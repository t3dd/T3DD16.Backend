<?php

/**
 * This user func just avoids having numerous global variables left that
 * need to be unset at the end of this file. So now RealURL configuration
 * is wrapped into an anonymous function call that only exposes the
 * $GLOBALS['TYPO3_CONF_VARS'] array.
 */
call_user_func(function () {
    global $TYPO3_CONF_VARS;

    $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = [
        'init' => [
            'enableCHashCache' => true,
            'appendMissingSlash' => 'ifNotFile,redirect',
            'adminJumpToBackend' => true,
            'enableUrlDecodeCache' => true,
            'enableUrlEncodeCache' => true,
            'emptyUrlReturnValue' => '/',
            'enableAllUnicodeLetters' => true,
            'enableDomainLookup' => true,
        ],
        'pagePath' => [
            'type' => 'user',
            'userFunc' => 'Tx\\Realurl\\UriGeneratorAndResolver->main',
            'spaceCharacter' => '-',
            'languageGetVar' => 'L',
            'expireDays' => 30,
            'expireAllLanguages' => true,
        ],
        'alternativeDomains' => [],
        'fileName' => [
            'defaultToHTMLsuffixOnPrev' => 0,
            'acceptHTMLsuffix' => 0,
            'index' => [
                '.json' => [
                    'keyValues' => [
                        'type' => 1450887489,
                    ]
                ],
            ],
        ],
        'postVarSets' => [],
        'fixedPostVars' => [
            'session' => [
                [
                    'GETvar' => 'tx_sessions_sessions[session]',
                    'lookUpTable' => [
                        'table' => 'tx_sessions_domain_model_session',
                        'enable404forInvalidAlias' => 1,
                        'id_field' => 'uid',
                        'alias_field' => 'title',
                        'addWhereClause' => ' AND NOT deleted',
                        'useUniqueCache' => 1,
                        'useUniqueCache_conf' => [
                            'strtolower' => 1,
                            'spaceCharacter' => '-',
                        ],
                        'languageGetVar' => 'L',
                        'languageExceptionUids' => '',
                        'languageField' => 'sys_language_uid',
                        'transOrigPointerField' => 'l10n_parent',
                        'autoUpdate' => 1,
                        'expireDays' => 180,
                    ],
                    'optional' => true
                ],
            ],
        ],
    ];

    $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT']['fixedPostVars'][11] = 'session';

});
