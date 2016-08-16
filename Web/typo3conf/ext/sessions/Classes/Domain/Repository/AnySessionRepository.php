<?php
namespace TYPO3\Sessions\Domain\Repository;

class AnySessionRepository extends AbstractSessionRepository
{

    /**
     * @param object $modifiedObject
     * @param bool $forceDirectPersist
     */
    public function update($modifiedObject, $forceDirectPersist = false)
    {
        parent::update($modifiedObject);
        if ($forceDirectPersist) {
            $this->persistenceManager->persistAll();
        }
    }
}
