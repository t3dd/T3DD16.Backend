<?php
namespace TYPO3\Sessions\Domain\Repository;

class ScheduledSessionRepository extends AbstractSessionRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'begin' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
        'highlight' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
    ];

}
