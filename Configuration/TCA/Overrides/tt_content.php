<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'EventSubmission',
    'App',
    'LLL:EXT:event_submission/Resources/Private/Language/locallang.xlf:plugin.event_submission_app.title',
    'content-form',
    'forms',
    'LLL:EXT:event_submission/Resources/Private/Language/locallang.xlf:plugin.event_submission_app.description'
);

(function ($extKey = 'event_submission') {
    $ll = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:';
    $pluginSignature = 'eventsubmission_app';

    // Configure the bodytext field for the plugin
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = str_replace(
        '--palette--;;headers,',
        '--palette--;;headers, bodytext,',
        $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem']
    );

    // Configure bodytext field with JSON editor
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['columnsOverrides']['bodytext'] = [
        'label' => $ll . 'label.additionalFieldsConfiguration',
        'config' => [
            'type' => 'text',
            'renderType' => 'codeEditor',
            'format' => 'json',
            'rows' => 15,
            'cols' => 50,
            'enableRichtext' => false,
        ],
    ];
})();
