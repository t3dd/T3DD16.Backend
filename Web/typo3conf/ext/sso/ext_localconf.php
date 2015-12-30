<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sso',
    'login',
    ['Authentication' => 'login'],
    ['Authentication' => 'login']
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sso',
    'authenticate',
    ['Authentication' => 'authenticate'],
    ['Authentication' => 'authenticate']
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sso',
    'logout',
    ['Authentication' => 'logout'],
    ['Authentication' => 'logout']
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sso',
    'me',
    ['Authentication' => 'me'],
    ['Authentication' => 'me']
);