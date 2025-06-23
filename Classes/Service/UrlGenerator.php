<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use Exception;

class UrlGenerator
{
    public function __construct(
        private readonly SiteFinder $siteFinder,
        private readonly LanguageServiceFactory $languageServiceFactory
    )
    {}

    /**
     * @param ServerRequestInterface $request
     * @param array $parameters
     * @param string $fragment
     * @param bool $forceEnglish
     * @return string
     * @throws SiteNotFoundException
     */
    public function generateUrl(ServerRequestInterface $request, array $parameters, string $fragment = '', bool $forceEnglish = false): string
    {
        $formPageUid = $this->extractFormPageUid($request);
        if ($formPageUid <= 0)
            throw new Exception('FormPageUid is not found');

        $site = $this->siteFinder->getSiteByPageId($formPageUid);
        if ($forceEnglish)
            $lang = $site->getLanguageById(1);
        else
            $lang = $request->getAttribute('language') ?? $site->getDefaultLanguage();

        return (string)$site->getRouter()->generateUri(
            $formPageUid,
            ['_language' => $lang] + $parameters, // URL parameters
            $fragment,
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
        return (int)(($typoScript?->getFlatSettings()['eventSubmission.appPid'] ?? null) ?? 507);
    }

    /**
     * @param string $key
     * @param string $extension
     * @param SiteLanguage $language
     * @return string
     */
    public function translate(string $key, string $extension, SiteLanguage $language): string
    {
        $languageService = $this->languageServiceFactory->createFromSiteLanguage($language);
        return $languageService->sL('LLL:EXT:' . $extension . '/Resources/Private/Language/locallang.xlf:' . $key);
    }
}
