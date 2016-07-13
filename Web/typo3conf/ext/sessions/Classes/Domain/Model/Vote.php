<?php

namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\Sso\Domain\Model\FrontendUser;

class Vote extends AbstractEntity implements \JsonSerializable
{

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $user;

    /**
     * @var \TYPO3\Sessions\Domain\Model\AbstractSession
     */
    protected $session;

    /**
     * @param FrontendUser $user
     * @param AbstractSession $session
     */
    public function __construct(FrontendUser $user, AbstractSession $session)
    {
        $this->user = $user;
        $this->session = $session;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return AbstractSession
     */
    public function getSession()
    {
        return $this->session;
    }
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'session' => $this->session->getUid(),
        ];
    }
}
