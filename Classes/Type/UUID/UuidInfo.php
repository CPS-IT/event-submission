<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

readonly class UuidInfo
{
    public function __construct(
        public UuidVersion $version,
        public UuidVariant $variant,
        public string $uuid,
        public bool $isNil = false
    ) {}
}
