<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $languageFilePrefix = 'LLL:EXT:sso/Resources/Private/Language/locallang_db.xlf:';

    foreach (['login', 'authenticate', 'logout', 'me'] as $item) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'TYPO3.Sso',
            $item,
            $languageFilePrefix . 'plugins.' . $item . '.title'
        );
    }

});