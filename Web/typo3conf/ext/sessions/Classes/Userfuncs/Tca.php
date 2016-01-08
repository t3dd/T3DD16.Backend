<?php
namespace TYPO3\Sessions\Userfuncs;

use TYPO3\CMS\Backend\Utility\BackendUtility;

class Tca
{

    /**
     * @param array $parameters
     * @param $parentObject
     */
    public function getSessionTitle(&$parameters, $parentObject)
    {
        $sessionRecord = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $title = $sessionRecord['title'];

        // TODO: Define backend title for session
        if ($sessionRecord['date']) {
            $title = (new \DateTime())->setTimestamp($sessionRecord['date'])->format('d-m-y') . ' - ' . $title;
        }

        $parameters['title'] = $title;
    }

}