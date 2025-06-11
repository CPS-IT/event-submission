<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

use InvalidArgumentException;

class UuidValidationException extends InvalidArgumentException
{
    public function __construct(string $message, public readonly string $uuid)
    {
        parent::__construct($message);
    }
}
