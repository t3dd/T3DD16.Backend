<?php
namespace TYPO3\Sso\Domain\Repository;

class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{

    /**
     * @return \TYPO3\Sso\Domain\Model\FrontendUser
     */
    public function findCurrentUser()
    {
        if (!is_array($GLOBALS['TSFE']->fe_user->user)) {
            return null;
        }

        return $this->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
    }

}