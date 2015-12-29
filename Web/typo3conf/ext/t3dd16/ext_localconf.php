<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

# Always include pageTsConfig. This is necessary to have it also for records with pid 0 (sys_file_metadata)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT:source="FILE:EXT:t3dd16/Configuration/PageTs/Include.t3s">');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('<INCLUDE_TYPOSCRIPT:source="FILE:EXT:t3dd16/Configuration/UserTs/Options.t3s">');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][] = [
    \TYPO3\T3DD16\ContentObject\JsonContentObject::CONTENT_OBJECT_NAME,
    \TYPO3\T3DD16\ContentObject\JsonContentObject::class
];

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(\TYPO3\CMS\Core\Resource\ResourceStorage::class, \TYPO3\CMS\Core\Resource\ResourceStorageInterface::SIGNAL_PreGeneratePublicUrl, \TYPO3\T3DD16\SignalSlot\ResourceStorage::class, 'getCdnPublicUrl');