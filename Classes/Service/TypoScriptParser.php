<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Throwable;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TypoScriptParser
{
    private ?Context $context = null;
    private ?SiteFinder $siteFinder = null;

    public function __construct() {}

    /**
     * Boot the request with site, language, frontend.user, frontend.controller & frontend.typoscript attributes
     *
     * @param ServerRequestInterface $request
     * @param int|null $pageUid
     * @param int|null $languageUid
     * @return ServerRequestInterface
     */
    public function boot(ServerRequestInterface $request, ?int $pageUid = null, ?int $languageUid = null): ServerRequestInterface
    {
        $this->context ??= GeneralUtility::makeInstance(Context::class);
        $this->siteFinder ??= GeneralUtility::makeInstance(SiteFinder::class);

        $site = $this->resolveSite($request);
        $request = $request->withAttribute('site', $site);

        $language = $this->resolveLanguage($request, $site, $languageUid);
        $request = $request->withAttribute('language', $language);

        $frontendUser = $this->generateFrontendUser($request);
        $request = $request->withAttribute('frontend.user', $frontendUser);

        $frontendController = $this->generateFrontendController($request, $site, $frontendUser, $language, $pageUid);
        $request = $request->withAttribute('frontend.controller', $frontendController);

        return $this->initTypoScript($request, $frontendController);
    }

    /**
     * @param ServerRequestInterface $request
     * @param SiteInterface $site
     * @param FrontendUserAuthentication $frontendUser
     * @param SiteLanguage $language
     * @param int|null $pageUid
     * @return TypoScriptFrontendController|null
     */
    private function generateFrontendController(ServerRequestInterface $request, SiteInterface $site, FrontendUserAuthentication $frontendUser, SiteLanguage $language, ?int $pageUid = null): ?TypoScriptFrontendController
    {
        if ($request->getAttribute('frontend.controller') !== null) {
            return $request->getAttribute('frontend.controller');
        } else {
            /** @var TypoScriptFrontendController $controller */
            $controller = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $this->context,
                $site,
                $language,
                $this->generatePageArgument($site, $pageUid),
                $frontendUser
            );
        }

        return $controller;
    }

    /**
     * @param ServerRequestInterface $request
     * @param TypoScriptFrontendController $controller
     * @return ServerRequestInterface
     */
    private function initTypoScript(ServerRequestInterface $request, TypoScriptFrontendController $controller): ServerRequestInterface
    {
        return $request;
    }

    /**
     * @param SiteInterface $site
     * @param int|null $pageUid
     * @return PageArguments
     */
    private function generatePageArgument(SiteInterface $site, ?int $pageUid = null): PageArguments
    {
        return new PageArguments($pageUid ?? $site->getRootPageId(), '0', []);
    }

    /**
     * @param ServerRequestInterface $request
     * @return FrontendUserAuthentication
     */
    private function generateFrontendUser(ServerRequestInterface $request): FrontendUserAuthentication
    {
        if ($request->getAttribute('frontend.user') !== null) {
            return $request->getAttribute('frontend.user');
        }

        $frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        // Authenticate now
        try {
            $frontendUser->start($request);
        } catch (Throwable) {}
        // no matter if we have an active user we try to fetch matching groups which can
        // be set without an user (simulation for instance!)
        $frontendUser->fetchGroupData($request);

        // Register the frontend user as aspect and within the request
        $this->context->setAspect('frontend.user', $frontendUser->createUserAspect());
        return $frontendUser;
    }

    /**
     * @param ServerRequestInterface $request
     * @param SiteInterface $site
     * @param int|null $languageUid
     * @return SiteLanguage
     */
    private function resolveLanguage(ServerRequestInterface $request, SiteInterface $site, ?int $languageUid = null): SiteLanguage
    {
        if ($request->getAttribute('language') !== null) {
            return $request->getAttribute('language');
        }

        if ($languageUid === null) {
            return $site->getDefaultLanguage();
        }

        foreach ($site->getLanguages() as $language) {
            if ($language->getLanguageId() === $languageUid) {
                return $language;
            }
        }

        return $site->getDefaultLanguage();
    }

    /**
     * @param ServerRequestInterface $request
     * @return SiteInterface|null
     */
    private function resolveSite(ServerRequestInterface $request): ?SiteInterface
    {
        if ($request->getAttribute('site') !== null) {
            return $request->getAttribute('site');
        }

        $host = $request->getUri()->getHost();
        try {
            return array_find($this->siteFinder->getAllSites(), fn($site) => $site->getBase()->getHost() === $host);
        } catch (Throwable) {
            return null;
        }
    }
}
