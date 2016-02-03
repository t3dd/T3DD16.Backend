<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class SessionRepository extends Repository
{

    /**
     * @param array $uids array holding multiple unique identifiers
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUids($uids)
    {
        $query = $this->createQuery();
        return $query->matching($query->in('uid', $uids))->execute();
    }
}
