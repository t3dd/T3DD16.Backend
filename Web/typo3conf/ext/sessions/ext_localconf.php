<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sessions',
    'schedule',
    ['Session' => 'listScheduled'],
    ['Session' => '']
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sessions',
    'sessions',
    ['Session' => 'index'],
    ['Session' => '']
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TYPO3.Sessions',
    'votes',
    ['Vote' => 'index'],
    ['Vote' => 'index']
);