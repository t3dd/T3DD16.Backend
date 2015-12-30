<?php
namespace TYPO3\Sso\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\Sso\Exception\EmptyUserException;

class AuthenticationService extends FrontendUserAuthentication
{

    const CACHE_AUTHENTICATION_COOKIE_NAME = 'fe_cache_auth';

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    var $db;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initializeObject();
    }

    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->db = $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param \TYPO3\Sso\Domain\Model\FrontendUser $frontendUser
     *
     * @throws EmptyUserException
     * @return void
     */
    public function registerSession($frontendUser)
    {
        if (!$frontendUser) {
            throw new EmptyUserException('No user given that can be dropped into the session.',
                1329749957);
        }
        $row = $this->db->exec_SELECTgetSingleRow('*', 'fe_users', 'uid = ' . $frontendUser->getUid());

        $this->getFeUser()->createUserSession($row);
        $this->getFeUser()->setSessionCookie();
    }

    /**
     * @return void
     */
    public function unregisterSession()
    {
        $this->unsetCacheAuthenticationCookie();
        $this->getFeUser()->logoff();
    }

    /**
     * Unset the cache authentication cookie if it is set
     */
    public function unsetCacheAuthenticationCookie()
    {
        if ($_COOKIE[self::CACHE_AUTHENTICATION_COOKIE_NAME]) {
            setcookie(self::CACHE_AUTHENTICATION_COOKIE_NAME, '', $GLOBALS['EXEC_TIME'] - 86400, '/',
                GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
                (bool)GeneralUtility::getIndpEnv('TYPO3_SSL'), true);
        }
    }

    /**
     * @return FrontendUserAuthentication
     */
    protected function getFeUser()
    {
        return $GLOBALS['TSFE']->fe_user;
    }

}