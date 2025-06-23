<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service\Api;

use Cpsit\EventSubmission\Configuration\Extension;
use Cpsit\EventSubmission\Service\EmailService;
use Cpsit\EventSubmission\Service\UrlGenerator;
use Cpsit\EventSubmission\Type\UUID\UuidValidator;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;

class UserApiService extends BaseApiService
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly UrlGenerator $urlGenerator
    ) {}

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function support(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === 'POST' && str_starts_with($request->getUri()->getPath(), '/api/user');
    }

    /**
     * @param ServerRequestInterface $request
     * @return string[]
     * @throws Exception
     */
    public function process(ServerRequestInterface $request): array
    {
        return $this->sendValidationRequest($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string[]
     * @throws Exception
     */
    private function sendValidationRequest(ServerRequestInterface $request): array
    {
        $payload = $this->parsePayloadFromRequest($request);
        $verificationUrl = $this->generateVerificationUrl($request, $payload);

        if (!empty($verificationUrl) && $this->emailService->sendMail(
                request: $request,
                template: 'EXT:event_submission/Resources/Private/Templates/SendValidationRequest.html',
                parameter: ['validationUrl' => $verificationUrl],
                recipient: new Address($payload['email']??''),
                subject: $this->urlGenerator->translate('user.sendValidationRequest.mail.subject', Extension::EXTENSION_KEY, $request->getAttribute('language')),
            )) {
            return [
                'data' => [],
                'code' => 300 // expected answer if all works
            ];
        }

         throw new Exception('could not send validation email');
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $payload
     * @return string
     * @throws SiteNotFoundException
     * @throws Exception
     */
    private function generateVerificationUrl(ServerRequestInterface $request, array $payload): string
    {
        $hash = $payload['validationHash'];
        if (empty($hash)) {
            throw new Exception('ValidationHash is empty');
        }
        if (!UuidValidator::validate($hash, true)) {
            throw new Exception('ValidationHash is not valid UUID');
        }

        return $this->urlGenerator->generateUrl($request, ['validationHash' => $hash]);
    }
}
