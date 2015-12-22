<?php
defined('TYPO3_MODE') or die();

$domains = ['t3dd16.typo3.org'];

foreach ($domains as $domain) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('t3dd16',
        'Configuration/TypoScript/' . $domain . '/', $domain);
}

unset($domains, $domain);
