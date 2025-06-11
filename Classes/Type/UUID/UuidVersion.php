<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type\UUID;

enum UuidVersion: int
{
    case V1 = 1;
    case V2 = 2;
    case V3 = 3;
    case V4 = 4;
    case V5 = 5;
    case V6 = 6;
    case V7 = 7;
    case V8 = 8;
}
