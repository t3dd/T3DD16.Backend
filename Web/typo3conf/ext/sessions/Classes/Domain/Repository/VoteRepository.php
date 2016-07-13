<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\Sessions\Domain\Model\AbstractSession;
use TYPO3\Sso\Domain\Model\FrontendUser;

class VoteRepository extends Repository
{

    /**
     * @param FrontendUser $user
     * @param AbstractSession $session
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findOneByUserAndSession(FrontendUser $user, AbstractSession $session)
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd($query->equals('session', $session), $query->equals('user', $user)));
        $vote = $query->setLimit(1)->execute();
        return $vote->getFirst();
    }

}
