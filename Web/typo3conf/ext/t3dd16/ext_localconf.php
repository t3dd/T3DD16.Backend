<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

# Always include pageTsConfig. This is necessary to have it also for records with pid 0 (sys_file_metadata)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT:source="FILE:EXT:t3dd16/Configuration/PageTs/Include.t3s">');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('<INCLUDE_TYPOSCRIPT:source="FILE:EXT:t3dd16/Configuration/UserTs/Options.t3s">');
