<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Controller;

use Cpsit\EventSubmission\Service\Api\EventApiService;
use Cpsit\EventSubmission\Service\Api\UserApiService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController
{
    public function __construct(
        private readonly UserApiService $userApiService,
        private readonly EventApiService $eventApiService,
    )
    {}

    public function process(ServerRequestInterface $request): iterable|null|ResponseInterface
    {
        return match (true) {
            $this->userApiService->support($request) => $this->userApiService->process($request),
            $this->eventApiService->support($request) => $this->eventApiService->process($request),
            default => null,
        };
    }
}
