<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SessionRepository extends Repository
{
	/**
	 * @return array()
	 */
	public function findAllFlat(){

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