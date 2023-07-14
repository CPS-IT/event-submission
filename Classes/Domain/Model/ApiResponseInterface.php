<?php

declare(strict_types=1);

/*
 * This file is part of the event_submission project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Cpsit\EventSubmission\Domain\Model;

use Stringable;

/**
 *
 */
interface ApiResponseInterface extends Stringable
{
    public const EVENT_SUBMISSION_SUCCESS = 100;
    public const EVENT_SUBMISSION_ERROR = 400;

    public function __toString(): string;

}