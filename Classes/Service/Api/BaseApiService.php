<?php

namespace Cpsit\EventSubmission\Service\Api;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class BaseApiService implements ApiServiceInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return array|null
     */
    protected function parsePayloadFromRequest(ServerRequestInterface $request): ?array
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
}
