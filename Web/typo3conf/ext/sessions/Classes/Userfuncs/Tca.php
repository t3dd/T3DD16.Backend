<?php
namespace TYPO3\Sessions\Userfuncs;

use TYPO3\CMS\Backend\Utility\BackendUtility;

class Tca
{
    const DATE_EmptyValue = '0000-00-00 00:00:00';

    /**
     * @param array $parameters
     */
    public function getSessionTitle(array &$parameters)
    {
        $sessionRecord = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $title = $sessionRecord['title'];

        // TODO: Define backend title for session
        if (!empty($sessionRecord['begin']) && $sessionRecord['begin'] !== static::DATE_EmptyValue) {
            $beginDate = \DateTime::createFromFormat('Y-m-d H:i:s', $sessionRecord['begin']);
            $title = $beginDate->format('d.m. (H:i)') . ' - ' . $title;
        } else {
            $title = 'unassigned - ' . $title;
        }

        $parameters['title'] = $title;
    }

    /**
     * @param array $parameters
     */
    public function getVoteTitle(array &$parameters)
    {
        $sessionRecord = BackendUtility::getRecord('tx_sessions_domain_model_session', $parameters['row']['session']);
        $userRecord = BackendUtility::getRecord('fe_users', $parameters['row']['user']);

        $parameters['title'] = sprintf('"%s" by "%s"', $sessionRecord['title'], $userRecord['username']);
    }

}
