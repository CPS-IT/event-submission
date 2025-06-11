<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Middleware;

use Cpsit\EventSubmission\Controller\ApiController;
use Cpsit\EventSubmission\Service\TypoScriptParser;
use Fr\IkiSitepackage\Middleware\BaseApiMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiMiddleware extends BaseApiMiddleware
{
    public function __construct(
        private readonly ApiController $apiController,
        private readonly TypoScriptParser $typoScriptParser
    )
    {}

    protected function validateBasePath(ServerRequestInterface $request): bool
    {
        $path = strtolower($request->getUri()->getPath());
        $method = strtoupper($request->getMethod());
        $lookup = [
            '/api/event' => ['GET', 'POST', 'PUT', 'DELETE'],
            '/api/service' => ['GET'],
            '/api/user' => ['POST'],
        ];

        return array_any($lookup, fn($methods, $pathPattern) => str_starts_with($path, $pathPattern) && in_array($method, $methods));
    }

    protected function processApiRequest(ServerRequestInterface $request): null|array|string|ResponseInterface
    {
        // boot up TypoScript, we want for api always english site
        $request = $this->typoScriptParser->boot(request: $request, languageUid: 1);
        return $this->apiController->process($request);
    }
}
