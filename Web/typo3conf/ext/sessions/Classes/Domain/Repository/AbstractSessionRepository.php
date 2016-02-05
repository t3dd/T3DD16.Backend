<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

abstract class AbstractSessionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @param array|int[] $uids array holding multiple unique identifiers
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUids(array $uids)
    {
        $query = $this->createQuery();
        return $query->matching($query->in('uid', $uids))->execute();
    }

    /**
     * @return array()
     */
    public function findAllFlat() {

        $query = $this->createQuery();

        return $query->execute(true);
    }

    /**
     * @return QueryResultInterface
     */
    public function getAcceptedSessionsOrderByVoteCount(){

        $query = $this->createQuery();

        $query->setOrderings(array('votes' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));

        return $query->execute();
    }
}
