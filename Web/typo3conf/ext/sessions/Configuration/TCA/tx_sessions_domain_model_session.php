<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$_EXTKEY = 'sessions';
$LLL = 'LLL:EXT:sessions/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $LLL . 'tx_sessions_domain_model_session',
        'type'  =>  'type',
        'label' => 'title',
        'label_userFunc' => 'TYPO3\\Sessions\\Userfuncs\\Tca->getSessionTitle',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,

        'versioningWS' => 2,
        'versioning_followPages' => true,

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY begin ASC, title ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'searchFields' => 'title',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Session.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, title',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesBase;palettesBase,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesTopics;palettesTopics,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.access,
                sys_language_uid;;;;1-1-1,
                l10n_parent,
                l10n_diffsource,
                hidden,
            '
        ],
        'TYPO3\Sessions\Domain\Model\AnySession' => [
            'showitem' => '
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesBase;palettesBase,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesTopics;palettesTopics,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.access,
                sys_language_uid;;;;1-1-1,
                l10n_parent,
                l10n_diffsource,
                hidden,
            '
        ],
        'TYPO3\Sessions\Domain\Model\ProposedSession' => [
            'showitem' => '
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesBase;palettesBase,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesTopics;palettesTopics,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.access,
                sys_language_uid;;;;1-1-1,
                l10n_parent,
                l10n_diffsource,
                hidden,
            '
        ],
        'TYPO3\Sessions\Domain\Model\AcceptedSession' => [
            'showitem' => '
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesBase;palettesBase,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesTopics;palettesTopics,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.access,
                sys_language_uid;;;;1-1-1,
                l10n_parent,
                l10n_diffsource,
                hidden,
            '
        ],
        'TYPO3\Sessions\Domain\Model\ScheduledSession' => [
            'showitem' => '
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesBase;palettesBase,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesDate;palettesDate,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesTopics;palettesTopics,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.access,
                sys_language_uid;;;;1-1-1,
                l10n_parent,
                l10n_diffsource,
                hidden,
            '
        ],
    ],
    'palettes' => [
        '1' => [
            'showitem' => ''
        ],
        'palettesBase' => [
            'showitem' => 'type, highlight, --linebreak--, title, --linebreak--, description,'
        ],
        'palettesDate' => [
            'showitem' => 'room, --linebreak--, begin, end,'
        ],
        'palettesSpeaker' => [
            'showitem' => 'speakers'
        ],
        'palettesTopics'  => [
            'showitem'  =>  'topics'
        ]
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
                'default'   =>  0
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_sessions_domain_model_session',
                'foreign_table_where' => 'AND tx_sessions_domain_model_session.pid=###CURRENT_PID### AND tx_sessions_domain_model_session.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'description' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 15,
            ],
        ],
        'highlight' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.highlight',
            'config' => [
                'type' => 'check',
            ],
        ],
        'room' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.room',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'tx_sessions_domain_model_room',
                'foreign_table_where' => 'ORDER BY tx_sessions_domain_model_room.title',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'begin' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.begin',
            'config' => [
                'type' => 'input',
                'dbType' => 'datetime',
                'default' => '0000-00-00 00:00:00',
                'size' => 20,
                'max' => 20,
                'eval' => 'datetime',
            ],
        ],
        'end' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.end',
            'config' => [
                'type' => 'input',
                'dbType' => 'datetime',
                'default' => '0000-00-00 00:00:00',
                'size' => 20,
                'max' => 20,
                'eval' => 'datetime',
            ],
        ],
        'speakers' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.speakers',
            'config' => [
                'type' => 'select',
                'multiple' => false,
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.username ASC',
                'MM' => 'tx_sessions_session_record_mm',
                'MM_match_fields' => array(
                    'tablenames' => 'fe_users'
                ),
                'size' => 10,
                'autoSizeMax' => 50,
                'maxitems' => 9999,
            ],
        ],
        'topics'    =>  [
            'exclude'   =>  1,
            'label'     =>  $LLL . 'tx_sessions_domain_model_session.topics',
            'config'    =>  [
                'type' => 'select',
                'foreign_table' => 'tx_sessions_domain_model_topic',
                'foreign_table_where' => 'ORDER BY tx_sessions_domain_model_topic.title ASC',
                'MM' => 'tx_sessions_session_record_mm',
                'MM_match_fields' => array(
                    'tablenames' => 'tx_sessions_domain_model_topic'
                ),
                'size' => 10,
                'autoSizeMax' => 50,
                'maxitems' => 9999,
            ]
        ],
        'votes'    =>  [
            'exclude'   =>  1,
            'label'     =>  $LLL . 'tx_sessions_domain_model_session.votes',
            'config'    =>  [
                'type' => 'inline',
                'foreign_table' => 'tx_sessions_domain_model_vote',
                'foreign_field' => 'session',
                'foreign_table_where' => 'ORDER BY tx_sessions_domain_model_vote.crdate DESC',
            ]
        ],
        'type' => [
            'label' => 'Domain Object',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['AnySession', 'TYPO3\Sessions\Domain\Model\AnySession'],
                    ['ProposedSession', 'TYPO3\Sessions\Domain\Model\ProposedSession'],
                    ['AcceptedSession', 'TYPO3\Sessions\Domain\Model\AcceptedSession'],
                    ['ScheduledSession', 'TYPO3\Sessions\Domain\Model\ScheduledSession'],
                    ['DeclinedSession', 'TYPO3\Sessions\Domain\Model\DeclinedSession'],
                ],
                'default' => 'TYPO3\Sessions\Domain\Model\AnySession'
            ]
        ],
    ],
];
