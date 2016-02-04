<?php
namespace TYPO3\Sessions\Domain\Repository;

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
}
