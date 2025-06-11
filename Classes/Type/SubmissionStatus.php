<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Type;

use TYPO3\CMS\Core\Type\Enumeration;

/*
 * This file is part of the event_submission project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */
enum SubmissionStatus: int
{

    case unknown        = 0;  // unknown
    case new            = 1;  // submitted by frontend user
    case approved       = 2;  // approved by editor, ready for processing, event not yet created
    case eventCreated   = 3;  // event created after approval
    case updated        = 4;  // updated by frontend user, new approval required
    case withdrawn      = 5;  // proposal was withdrawn by the submitter
    case published      = 6;  // event has been published
    case error          = 7;  // something went wrong, might need intervention by editor
    case rejected       = 8;  // something went wrong, might need intervention by editor

    public const UNKNOWN = self::unknown->value;
    public const NEW = self::new->value;
    public const APPROVED = self::approved->value;
    public const EVENT_CREATED = self::eventCreated->value;
    public const EVENT_PUBLISHED = self::published->value;
    public const UPDATED = self::updated->value;
    public const WITHDRAWN = self::withdrawn->value;
    public const ERROR = self::error->value;
    public const REJECTED = self::rejected->value;


    public static function status(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
