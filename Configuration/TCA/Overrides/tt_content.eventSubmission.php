<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(function ($extKey = 'event_submission', $table = 'tt_content') {
    $ll = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:';
    $contentElementType = 'eventsubmission_app';

    // Add the new content element to the CType select
    ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'label' => $ll . 'tt_content.CType.eventsubmission_app',
            'value' => $contentElementType,
            'icon' => 'content-form',
            'group' => 'forms',
            'description' => $ll . 'tt_content.CType.eventsubmission_app.description',
        ],
        'bullets',
        'before'
    );

    // Set the icon class for the content element
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentElementType] = 'content-form';

    // Define the showitem configuration for the content element
    $GLOBALS['TCA']['tt_content']['types'][$contentElementType] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
                bodytext,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended
        ',
        'columnsOverrides' => [
            'bodytext' => [
                'label' => $ll . 'label.additionalFieldsConfiguration',
                'config' => [
                    'type' => 'json',
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                ],
            ],
        ],
    ];

})();
