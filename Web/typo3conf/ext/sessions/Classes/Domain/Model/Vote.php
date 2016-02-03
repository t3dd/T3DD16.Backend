<?php

namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Vote extends AbstractEntity
{

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $user;

    /**
     * @var \TYPO3\Sessions\Domain\Model\Session
     */
    protected $session;

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }


}
