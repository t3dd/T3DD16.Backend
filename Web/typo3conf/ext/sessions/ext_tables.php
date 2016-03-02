<?php
defined('TYPO3_MODE') or die();

// Register backend modules, but not in frontend or within upgrade wizards
if (TYPO3_MODE === 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
    // Module Web->Sessions
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'TYPO3.Sessions',
        'web',
        'session',
        '',
        array(
            'SessionModule' => 'index,manage,generateFirstSchedule,createTimeTable',
            'ApiModule' =>  'toggle,info,listSessions,listRooms,updateSession,scheduleSession,unscheduleSession,swapSessions,analyze'
        ),
        array(
            'access' => 'user',
            'icon' => 'EXT:sessions/Resources/Public/Icons/module-session.svg',
            'labels' => 'LLL:EXT:sessions/Resources/Private/Language/locallang_mod_session.xlf',
        )
    );
}
