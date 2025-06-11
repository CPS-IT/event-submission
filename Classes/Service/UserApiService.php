<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service;

use Cpsit\EventSubmission\Configuration\Extension;
use Cpsit\EventSubmission\Type\UUID\UuidValidator;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Address;
use Throwable;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class UserApiService
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly SiteFinder $siteFinder,
        private readonly LanguageServiceFactory $languageServiceFactory
    ) {}

    /**
     * @param ServerRequestInterface $request
     * @return string[]
     * @throws Exception
     */
    public function sendValidationRequest(ServerRequestInterface $request): array
    {
        $payload = $this->parsePayloadFromRequest($request);
        $verificationUrl = $this->generateVerificationUrl($request, $payload);

        if (!empty($verificationUrl) && $this->emailService->sendMail(
                request: $request,
                template: 'EXT:event_submission/Resources/Private/Templates/SendValidationRequest.html',
                parameter: ['validationUrl' => $verificationUrl],
                recipient: new Address($payload['email']??''),
                subject: $this->translate('user.sendValidationRequest.mail.subject', Extension::EXTENSION_KEY, $request->getAttribute('language')),
            )) {
            return ['status' => 'ok'];
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

        $formPageUid = $this->extractFormPageUid($request);
        if ($formPageUid <= 0)
            throw new Exception('FormPageUid is not found');

        $site = $this->siteFinder->getSiteByPageId($formPageUid);

        return (string)$site->getRouter()->generateUri(
            $formPageUid,
            ['_language' => $request->getAttribute('language'), 'validationHash' => $payload['validationHash']], // URL parameters
            '', // fragment
            RouterInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return int
     */
    private function extractFormPageUid(ServerRequestInterface $request): int
    {
        /** @var FrontendTypoScript $typoScript */
        $typoScript = $request->getAttribute('frontend.typoscript');
        return (int)$typoScript?->getFlatSettings()['eventSubmission.appPid'] ?? 0;
    }

    /**
     * @param ServerRequestInterface $request
     * @return array|null
     */
    private function parsePayloadFromRequest(ServerRequestInterface $request): ?array
    {
        try {
            $result = json_decode($request->getBody()->getContents(), true);
            if (!is_array($result)) {
                $result = null;
            }
        } catch (Throwable) {
            $result = null;
        } finally {
            return $result;
        }
    }

    /**
     * @param string $key
     * @param string $extension
     * @param SiteLanguage $language
     * @return string
     */
    private function translate(string $key, string $extension, SiteLanguage $language): string
    {
        $languageService = $this->languageServiceFactory->createFromSiteLanguage($language);
        return $languageService->sL('LLL:EXT:' . $extension . '/Resources/Private/Language/locallang.xlf:' . $key);
    }
}
