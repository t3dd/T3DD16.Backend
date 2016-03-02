<?php


namespace TYPO3\Sessions\Domain\Validator;


use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\Sessions\Utility\PlanningUtility;

class SpeakerCollisionValidator extends AbstractValidator
{

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param \TYPO3\Sessions\Domain\Model\AcceptedSession $value
     * @return boolean
     */
    protected function isValid($value)
    {
        /** @var PlanningUtility $planningUtitlity */
        $planningUtitlity = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PlanningUtility::class);
        $colls = $planningUtitlity->getCollidingSessions($value);
        if(is_array($colls)) {
            /** @var \TYPO3\Sessions\Domain\Model\AnySession $col */
            foreach($colls as $col) {
                $this->addError(
                    $this->translateErrorMessage('validator.speakerCollision', 'sessions', [$col->getTitle()])
                , 1455569466);
            }
            return false;
        }
        return true;
    }
}