<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Service\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ApiServiceInterface
{
    public function support(ServerRequestInterface $request): bool;
    public function process(ServerRequestInterface $request): iterable|null|ResponseInterface;
}
