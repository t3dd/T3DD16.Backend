<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$_EXTKEY = 'sessions';
$LLL = 'LLL:EXT:sessions/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $LLL . 'tx_sessions_domain_model_vote',
        'label' => 'session',
        'label_alt' =>  'user',
        'label_userFunc' => 'TYPO3\\Sessions\\Userfuncs\\Tca->getVoteTitle',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,

        'versioningWS' => 2,
        'versioning_followPages' => true,

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate ASC',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Room.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, session, user',
    ],
    'types' => [
        '1' => [
            'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, session, user'
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
                'default' => 0
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
                'foreign_table' => 'tx_sessions_domain_model_vote',
                'foreign_table_where' => 'AND tx_sessions_domain_model_vote.pid=###CURRENT_PID### AND tx_sessions_domain_model_vote.sys_language_uid IN (-1,0)',
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
        'session' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_session',
            'l10n_mode' => 'mergeIfNotBlank',
            'config' => [
                'type' => 'select',
                'size' => 1,
                'minitems'  =>  1,
                'maxitems'  =>  1,
                'foreign_table' => 'tx_sessions_domain_model_session',
                'foreign_table_where'   =>  'ORDER BY tx_sessions_domain_model_session.title'
            ],
        ],
        'user' => [
            'exclude' => 1,
            'label' => $LLL . 'tx_sessions_domain_model_vote.user',
            'l10n_mode' => 'mergeIfNotBlank',
            'config' => [
                'type' => 'select',
                'size' => 1,
                'minitems'  =>  1,
                'maxitems'  =>  1,
                'foreign_table' => 'fe_users',
                'foreign_table_where' =>    'ORDER BY fe_users.username',
            ],
        ]
    ],
];
