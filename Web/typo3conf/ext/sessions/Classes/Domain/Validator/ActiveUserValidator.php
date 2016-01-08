<?php
namespace TYPO3\Sessions\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ActiveUserValidator extends AbstractValidator
{

    /**
     * @var \TYPO3\Sso\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function isValid($value)
    {
        $user = $this->frontendUserRepository->findCurrentUser();
        if ($user === null) {
            $error = new \TYPO3\CMS\Extbase\Error\Error($this->translateErrorMessage('validator.activeUser',
                'sessions'), 1452072731);
            $this->result->addError($error);

            return false;
        }

        return true;
    }
}