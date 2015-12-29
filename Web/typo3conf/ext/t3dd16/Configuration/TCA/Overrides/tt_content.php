<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $languageFilePrefix = 'LLL:EXT:t3dd16/Resources/Private/Language/Backend.xlf:';

    foreach (['card', 'price', 'fullwidthimage', 'sponsors'] as $item) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $languageFilePrefix . 'tt_content.' . $item . '.title',
                $item,
                'content-textpic'
            ],
            'textmedia',
            'after'
        );
        $GLOBALS['TCA']['tt_content']['types'][$item] = $GLOBALS['TCA']['tt_content']['types']['textmedia'];
        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$item] = 'mimetypes-x-content-text-media';
    }

    $GLOBALS['TCA']['tt_content']['palettes']['header'] = $GLOBALS['TCA']['tt_content']['palettes']['headers'];

});