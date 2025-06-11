<?php

use Cpsit\EventSubmission\Configuration\Extension;
use TYPO3\CMS\Core\Utility\ArrayUtility;


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
