<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

abstract class AbstractSessionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'highlight' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
        'crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

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

    /**
     * @param $start \DateTime
     * @param $end \DateTime
     * @return array|QueryResultInterface
     */
    public function findByStartAndEnd($start, $end)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->greaterThanOrEqual('begin', $start->format('Y-m-d H:i:s')),
                $query->lessThanOrEqual('end', $end->format('Y-m-d H:i:s'))
            )
        );
        return $query->execute(true);
    }

}
