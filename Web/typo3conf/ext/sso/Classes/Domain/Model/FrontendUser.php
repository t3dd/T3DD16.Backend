<?php
namespace TYPO3\Sso\Domain\Model;

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser implements \JsonSerializable
{

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email
        ];
    }

}