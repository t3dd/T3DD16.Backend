<?php
namespace TYPO3\Sessions\Domain\Repository;

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

}