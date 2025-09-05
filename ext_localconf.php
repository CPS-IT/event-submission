<?php

use Cpsit\EventSubmission\Configuration\Extension;
use Cpsit\EventSubmission\Controller\EventSubmissionController;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

// Register plugin
ExtensionUtility::registerPlugin(
    'EventSubmission',
    'App',
    'LLL:EXT:event_submission/Resources/Private/Language/locallang.xlf:plugin.event_submission_app.title',
    'content-form',
    'forms',
    'LLL:EXT:event_submission/Resources/Private/Language/locallang.xlf:plugin.event_submission_app.description'
);

// Configure plugin
ExtensionUtility::configurePlugin(
    'EventSubmission',
    'App',
    [
        EventSubmissionController::class => 'app',
    ]
);

// cacheHash handling
ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TYPO3_CONF_VARS'],
    [
        'FE' => [
            'cacheHash' => [
                'excludedParameters' => [
                    'validationHash' => 'validationHash',
                    'editToken' => 'editToken',
                ],
            ],
        ],
    ]
);

Extension::registerAdditionalRenderTypes();
