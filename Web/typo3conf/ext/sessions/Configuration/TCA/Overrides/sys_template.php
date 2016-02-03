<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cms']['db_layout']['addTables']['tx_sessions_domain_model_session'][] = [
        'fList' => 'title,date,lightning,begin,end,room,highlight,speakers',
        'icon' => true,
    ];

});
