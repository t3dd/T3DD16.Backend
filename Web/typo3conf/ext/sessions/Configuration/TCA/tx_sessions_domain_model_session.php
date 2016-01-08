<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$_EXTKEY = 'sessions';
$LLL = 'LLL:EXT:sessions/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $LLL . 'tx_sessions_domain_model_session',
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
        'default_sortby' => 'ORDER BY date ASC, begin ASC, title ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
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
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesDate;palettesDate,
                --palette--;' . $LLL . 'tx_sessions_domain_model_session.palette.palettesSpeaker;palettesSpeaker,
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
            'showitem' => 'highlight, --linebreak--, title, --linebreak--, description,'
        ],
        'palettesDate' => [
            'showitem' => 'room, --linebreak--, date, --linebreak--, lightning, begin, end,'
        ],
        'palettesSpeaker' => [
            'showitem' => 'speaker1, speaker2, speaker3,'
        ],
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
        'date' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.date',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 20,
                'eval' => 'date'
            ],
        ],
        'lightning' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.lightning',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 20,
                'eval' => 'time',
            ],
        ],
        'begin' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.begin',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 20,
                'eval' => 'time',
            ],
        ],
        'end' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.end',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 20,
                'eval' => 'time',
            ],
        ],
        'speaker1' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.speaker1',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.name',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'speaker2' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.speaker2',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.name',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'speaker3' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session.speaker3',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.name',
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
    ],
];