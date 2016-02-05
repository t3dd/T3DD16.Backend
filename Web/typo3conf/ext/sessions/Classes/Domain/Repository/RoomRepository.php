<?php
namespace TYPO3\Sessions\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class RoomRepository extends Repository
{
	/**
	 * @return array()
	 */
	public function findAllFlat(){

		$query = $this->createQuery();

		return $query->execute(true);
	}

	/**
	 * @param integer $limit
	 * @return QueryResultInterface
	 */
	public function findAllLimited($limit){

		$query = $this->createQuery();

		return $query->setLimit($limit)->execute();
	}

}