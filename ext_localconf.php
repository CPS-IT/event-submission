<?php

use Cpsit\EventSubmission\Configuration\Extension;
use Cpsit\EventSubmission\Controller\AppController;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    Extension::VENDOR_NAME . '.' . Extension::NAME,
    'App',
    [
        AppController::class => 'app',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
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
