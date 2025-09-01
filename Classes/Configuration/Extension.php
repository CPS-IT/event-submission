<?php

declare(strict_types=1);

/*
 * This file is part of the event_submission project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Cpsit\EventSubmission\Configuration;

use Cpsit\EventSubmission\Form\Element\SubmissionApprovalStatusNode;
use Cpsit\EventSubmission\Form\Element\SubmissionPayloadDisplayNode;
use Cpsit\EventSubmission\Form\RegistrableInterface;

class Extension
{
    public const NAME = 'EventSubmission';
    public const VENDOR_NAME = 'Cpsit';
    public const EXTENSION_KEY = 'event_submission';

    public const ADDITIONAL_RENDER_TYPES = [
        SubmissionPayloadDisplayNode::class,
        SubmissionApprovalStatusNode::class
    ];
    public static function registerAdditionalRenderTypes():void
    {
        foreach (self::ADDITIONAL_RENDER_TYPES as $class) {
            if(!is_a($class, RegistrableInterface::class, true)) {
                continue;
            }

            $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$class::getNodeId()] = [
                    'nodeName' => $class::getNodeName(),
                    'priority' => $class::getPriority(),
                    'class' => $class,
                ];
        }
    }
}
