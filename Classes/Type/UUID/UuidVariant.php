<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

enum UuidVariant: string
{
    case NCS = 'ncs';
    case RFC4122 = 'rfc4122';
    case MICROSOFT = 'microsoft';
    case RESERVED = 'reserved';
}
