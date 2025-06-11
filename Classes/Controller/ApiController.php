<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Controller;

use Cpsit\EventSubmission\Service\UserApiService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController
{
    public function __construct(
        private readonly UserApiService $userApiService
    )
    {}

    public function process(ServerRequestInterface $request): iterable|null|ResponseInterface
    {
        $path = $request->getUri()->getPath();
        return match (true) {
            $path === '/api/user/sendValidationRequest' => $this->userApiService->sendValidationRequest($request),
        };
    }
}
