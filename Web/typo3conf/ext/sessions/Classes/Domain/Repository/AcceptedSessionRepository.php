<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class AcceptedSessionRepository extends AbstractSessionRepository
{
	/**
	 * @return QueryResultInterface
	 */
	public function getAllOrderByVoteCount(){

		$query = $this->createQuery();

		$query->setOrderings(array('votes' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING));

		return $query->execute();
	}
}
