<?php
namespace TYPO3\Sessions\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\Sessions\Domain\Model\Session;

class SessionOwnerValidator extends AbstractValidator
{

    /**
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @param Session $value
     * @return boolean
     */
    protected function isValid($value)
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        if ($user !== null && $user !== $value->getSpeaker1()) {
            $error = new \TYPO3\CMS\Extbase\Error\Error($this->translateErrorMessage('validator.sessionOwner',
                'sessions'), 1452072731);
            $this->result->addError($error);

            return false;
        }

        return true;
    }
}