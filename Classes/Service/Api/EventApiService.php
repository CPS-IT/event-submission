<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service\Api;

use Cpsit\EventSubmission\Configuration\Extension;
use Cpsit\EventSubmission\Domain\Repository\JobRepository;
use Cpsit\EventSubmission\Service\EmailService;
use Cpsit\EventSubmission\Service\UrlGenerator;
use Cpsit\EventSubmission\Type\SubmissionStatus;
use Cpsit\EventSubmission\Type\UUID\UuidValidator;
use Cpsit\EventSubmission\Validator\EventValidator;
use Fr\IkiEventApproval\Domain\Model\Job;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Symfony\Component\Mime\Address;

class EventApiService extends BaseApiService
{
    public function __construct(
        private readonly EventValidator $eventValidator,
        private readonly JobRepository $jobRepository,
        private readonly EmailService $emailService,
        private readonly UrlGenerator $urlGenerator
    )
    {}

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function support(ServerRequestInterface $request): bool
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        return
            // create event
            ($method === 'POST' && $path === '/api/event') ||
            // fetch event
            ($method === 'GET' && $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path) > 0) ||
            //edit event
            ($method === 'PUT' && $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path) > 0) ||
            // DELETE
            ($method === 'DELETE' && $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path) > 0) ||
            // cancel event
            ($method === 'PUT' && $this->extractId('/^\/api\/event\/(.+)\/withdraw/', $path) > 0);
    }

    /**
     * @param ServerRequestInterface $request
     * @return iterable|ResponseInterface|null
     * @throws Exception
     */
    public function process(ServerRequestInterface $request): iterable|null|ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        return match (true) {
            ($method === 'POST' && $path === '/api/event') => $this->createEvent($request, $this->parsePayloadFromRequest($request)),
            ($method === 'GET' && ($id = $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path)) > 0) => $this->fetchEvent($id),
            ($method === 'DELETE' && ($id = $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path)) > 0) => $this->deleteEvent($id),
            ($method === 'PUT' && ($id = $this->extractId('/^\/api\/event\/([^\/]+)\/?$/', $path)) > 0) => $this->editEvent($id, $this->parsePayloadFromRequest($request)),
            ($method === 'PUT' && ($id = $this->extractId('/^\/api\/event\/(.+)\/withdraw/', $path)) > 0) => $this->withdrawEvent($id),
        };
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @return int|string|null
     */
    private function extractId(string $pattern, string $subject): int|string|null
    {
        $matches = [];
        if (preg_match($pattern, $subject, $matches) === 1) {
            $id = ($matches[1] ?? null);
            if (is_numeric($id))
                return (int)$id;
            return (string)$id;
        }

        return null;
    }

    /**
     * @param int|string $id
     * @return array
     * @throws Exception
     */
    private function fetchEvent(int|string $id): array
    {
        /** @var Job $job */
        $job = $this->jobRepository->findBy(['uuid' => $id])->current();

        if ($id <= 0) {
            return throw new Exception('Event ID invalid');
        }

        return [
            'code' => 700,
            'data'=> $job->getPayloadDecoded()
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $data
     * @return array
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    private function createEvent(ServerRequestInterface $request, array $data): array
    {
        $this->validateEventData($data);
        $job = Job::makeFromData($data);
        $this->jobRepository->add($job);
        $this->jobRepository->flush();

        if (!empty($job->getUid()) && !empty($job->getUuid())) {

            // send mail with edit link
            $this->emailService->sendMail(
                request: $request,
                template: 'EXT:event_submission/Resources/Private/Templates/EventPostConfirmationEmail.html',
                parameter: ['editUrl' => $this->urlGenerator->generateUrl($request, ['editToken' => $job->getUuid()])],
                recipient: new Address($job->getEmail()),
                subject: $this->urlGenerator->translate('user.eventPostConfirmation.mail.subject', Extension::EXTENSION_KEY, $request->getAttribute('language')),
            );

            return [
                'code' => 100,
                'data' => [
                    'id' => $job->getUid(),
                    'editToken' => $job->getUuid()
                ]
            ];
        }

        return ['code' => 400, 'message' => 'Job creation failed'];
    }

    /**
     * @param int|string $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function editEvent(int|string $id, array $data): array
    {
        if ($id <= 0 || UuidValidator::validate($id, true)) {
            return throw new Exception('Event ID invalid');
        }

        /** @var Job $job */
        $job = $this->jobRepository->findBy(['uuid' => $id])->current();
        if ($job) {
            $updateData = $job->getPayloadDecoded() + $data;
            $this->validateEventData($updateData);
            $job->setPayload(json_encode($updateData));
            $this->jobRepository->update($job);
            $this->jobRepository->flush();
            return ['code' => 200,'data' => $updateData + ['id' => $job->getUuid()]];
        }

        return ['code' => 400, 'message' => 'Event ID invalid'];
    }

    /**
     * @param int|string $id
     * @return array
     * @throws Exception
     */
    private function withdrawEvent(int|string $id): array
    {
        if ($id <= 0 || UuidValidator::validate($id, true)) {
            return throw new Exception('Event ID invalid');
        }

        /** @var Job $job */
        $job = $this->jobRepository->findBy(['uuid' => $id])->current();
        if ($job) {
            $job->setStatus(SubmissionStatus::withdrawn->value);
            $this->jobRepository->update($job);
            $this->jobRepository->flush();

            return ['code' => 200,'data' => ['id' => $job->getUuid()]];
        }

        return ['code' => 400, 'message' => 'Event ID invalid'];
    }

    /**
     * @param int|string $id
     * @return array
     * @throws Exception
     */
    private function deleteEvent(int|string $id): array
    {
        if ($id <= 0 || UuidValidator::validate($id, true)) {
            return throw new Exception('Event ID invalid');
        }

        $job = $this->jobRepository->findBy(['uuid' => $id])->current();
        if ($job) {
            $this->jobRepository->remove($job);
            $this->jobRepository->flush();

            return ['code' => 900, 'data' => ['id' => $id]];
        }

        return ['code' => 400, 'message' => 'Event ID invalid'];
    }

    private function validateEventData(array $data): void
    {
        $validatorResult = $this->eventValidator->validate($data);
        if (!empty($validatorResult)) {
            throw new Exception('Invalid data: ' . implode(',', $validatorResult));
        }
    }
}
