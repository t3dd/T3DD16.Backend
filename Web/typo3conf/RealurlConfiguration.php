<?php

/**
 * This user func just avoids having numerous global variables left that
 * need to be unset at the end of this file. So now RealURL configuration
 * is wrapped into an anonymous function call that only exposes the
 * $GLOBALS['TYPO3_CONF_VARS'] array.
 */
call_user_func(function () {
    global $TYPO3_CONF_VARS;

    $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
        'init' => array(
            'enableCHashCache' => true,
            'appendMissingSlash' => 'ifNotFile,redirect',
            'adminJumpToBackend' => true,
            'enableUrlDecodeCache' => true,
            'enableUrlEncodeCache' => true,
            'emptyUrlReturnValue' => '/',
            'enableAllUnicodeLetters' => true,
            'enableDomainLookup' => true,
        ),
        'pagePath' => array(
            'type' => 'user',
            'userFunc' => 'Tx\\Realurl\\UriGeneratorAndResolver->main',
            'spaceCharacter' => '-',
            'languageGetVar' => 'L',
            'expireDays' => 30,
            'expireAllLanguages' => true,
        ),
        'alternativeDomains' => array(),
        'fileName' => array(
            'defaultToHTMLsuffixOnPrev' => 0,
            'acceptHTMLsuffix' => 1,
            'index' => array(),
        ),
        'postVarSets' => array(),
        'fixedPostVars' => array(),
    );

});
