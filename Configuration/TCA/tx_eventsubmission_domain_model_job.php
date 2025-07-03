<?php
defined('TYPO3') or die();

use Cpsit\EventSubmission\Form\Element\SubmissionApprovalStatusNode;
use Cpsit\EventSubmission\Form\Element\SubmissionPayloadDisplayNode;
use Cpsit\EventSubmission\Type\SubmissionStatus;
$ll = 'LLL:EXT:event_submission/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_eventsubmission_domain_model_job',
        'label' => 'request_date_time',
        'label_alt' => 'email,event',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'uid, email, uuid, payload',
        'typeicon_column' => 'status',
        'typeicon_classes' => [
            'default' => 'event-submission-job-unknown',
            '1' => 'event-submission-job-new',
            '2' => 'event-submission-job-approved',
            '3' => 'event-submission-job-eventCreated',
            '4' => 'event-submission-job-updated',
            '5' => 'event-submission-job-withdrawn',
            '6' => 'event-submission-job-published',
            '7' => 'event-submission-job-error',
        ],
    ],
    'interface' => [],
    'types' => [
        '0' => [
            'showitem' => '
                approval_status,
                approved,
                status,
                event,
                email,
                payload,
            --div--;' . $ll . 'tx_eventsubmission_domain_model_job.tab.request,
                request_date_time,
                response_code,
                is_api_error,
            --div--;' . $ll . 'tx_eventsubmission_domain_model_job.tab.internal,
                job_triggered_date_time,
                is_done,
                uuid,
                internal_log_message,
                is_internal_error'
        ],
    ],
    'columns' => [
        'email' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.email',
            'config' => [
                'type' => 'input',
                'width' => 100,
                'eval' => 'trim,',
                'readOnly' => true,
            ],
        ],
        'uuid' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.uuid',
            'config' => [
                'type' => 'input',
                'width' => 200,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'request_date_time' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.request_date_time',
            'exclude' => true,
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'payload' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.payload',
            'config' => [
                'type' => 'user',
                'renderType' => SubmissionPayloadDisplayNode::getNodeName()
            ],
        ],
        'response_code' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.response_code',
            'exclude' => true,
            'config' => [
                'type' => 'input',
                'width' => 10,
                'eval' => 'trim, int',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
        'is_api_error' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.is_api_error',
            'exclude' => true,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                        'labelChecked' => 'Success',
                        'labelUnchecked' => 'Error',
                        'invertStateDisplay' => true,
                    ],
                ],
                'readOnly' => true,
            ],
        ],
        'job_triggered_date_time' => [
            'exclude' => true,
            'label' => $ll . 'tx_eventsubmission_domain_model_job.job_triggered_date_time',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'is_done' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.is_done',
            'exclude' => true,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                        'labelChecked' => 'Done',
                        'labelUnchecked' => 'not done',
                    ],
                ],
                'readOnly' => true,
            ],
        ],
        'internal_log_message' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.internal_log_message',
            'exclude' => true,
            'config' => [
                'type' => 'input',
                'width' => 200,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'is_internal_error' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.is_internal_error',
            'exclude' => true,
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                        'labelChecked' => 'Successful',
                        'labelUnchecked' => 'Error',
                        'invertStateDisplay' => true,
                    ],
                ],
                'readOnly' => true,
            ],
        ],
        'approved' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.approved',
            'description' => $ll . 'tx_eventsubmission_domain_model_job.approved_description',
            'displayCond' => [
                'AND' => [
                    'FIELD:is_done:!=:1',
                    'FIELD:status:!=:' . SubmissionStatus::WITHDRAWN
                ],
            ],
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxLabeledToggle',
                'items' => [
                    [
                        'label' => '',
                        'value' => '',
                        'labelChecked' => $ll . 'tx_eventsubmission_domain_model_job.approved.1',
                        'labelUnchecked' => $ll . 'tx_eventsubmission_domain_model_job.approved.0',
                    ],
                ],
            ],
        ],
        'approval_status' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.approval_status',
            'description' => $ll . 'tx_eventsubmission_domain_model_job.approval_status_description',
            'config' => [
                'type' => 'user',
                'renderType' => SubmissionApprovalStatusNode::getNodeName()
            ]
        ],
        'event' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.event',
            'description' => $ll . 'tx_eventsubmission_domain_model_job.event_description',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_news_domain_model_news',
                'maxitems' => 1,
                'minitems' => 1,
                'size' => 1,
                'foreign_table' => 'tx_news_domain_model_news',
                // we cannot use this option: if enabled form engine produces JS error
                //'hideDeleteIcon' => true,
                'hideSuggest' => true,
                'fieldWizard' => [
                    'tableList' => [
                        'disabled' => true
                    ],
                ],
                'fieldControl' => [
                    'elementBrowser' => [
                        'disabled' => true,
                    ],
                    'editPopup' => [
                        'disabled' => false,
                    ],
                    'addRecord' => [
                        'disabled' => true,
                    ],
                    'listModule' => [
                        'disabled' => true,
                    ],
                ],
            ],
            'displayCond' => 'FIELD:event:REQ:true',
        ],
        'status' => [
            'label' => $ll . 'tx_eventsubmission_domain_model_job.label.status',
            'description' => $ll .'tx_eventsubmission_domain_model_job.description.status',
            'config' => [
                'type' => 'select',
                'readOnly' => true,
                'default' => SubmissionStatus::UNKNOWN,
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => $ll . 'label.status.unknown',
                        'value' => SubmissionStatus::UNKNOWN,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-unknown.svg'
                    ],
                    [
                        'label' => $ll . 'label.status.new',
                        'value' => SubmissionStatus::NEW,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-new.svg'
                    ],
                    [
                        'label' => $ll . 'label.status.approved',
                        'value' => SubmissionStatus::APPROVED,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-approved.svg'
                    ],
                    [
                        'label' => $ll . 'label.status.eventCreated',
                        'value' => SubmissionStatus::EVENT_CREATED,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-eventCreated.svg'
                    ],
                    [
                        'label' => $ll . 'label.status.published',
                        'value' => SubmissionStatus::EVENT_PUBLISHED,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-published.svg'
                    ],
                    [
                        'label' => $ll . 'label.status.updated',
                        'value' => SubmissionStatus::UPDATED,
                        'icon' => "EXT:event_submission/Resources/Public/Icons/event-submission-job-updated.svg"
                    ],
                    [
                        'label' => $ll . 'label.status.withdrawn',
                        'value' => SubmissionStatus::WITHDRAWN,
                        'icon' => "EXT:event_submission/Resources/Public/Icons/event-submission-job-withdrawn.svg"
                    ],
                    [
                        'label' => $ll . 'label.status.error',
                        'value' => SubmissionStatus::ERROR,
                        'icon' => 'EXT:event_submission/Resources/Public/Icons/event-submission-job-error.svg'
                    ],
                ]
            ]
        ],
    ],
];
