<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    $tempColumns['profile_image'] = [
        'exclude' => 1,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => 'LLL:EXT:sso/Resources/Private/Language/locallang_db.xlf:fe_users.profile_image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('profile_image', [
            'appearance' => [
                'createNewRelationLinkTitle' => 'LLL:EXT:sso/Resources/Private/Language/locallang_db.xlf:fe_users.profile_image.add',
            ],
            'foreign_types' => [
                \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                    'showitem' => '
                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                    --palette--;;filePalette'
                ],
            ],
        ], $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'profile_image;;;;1-1-1', '', 'after:image');
});