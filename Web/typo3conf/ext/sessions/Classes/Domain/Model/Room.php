<?php
namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Room extends AbstractEntity implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return ['title' => $this->title];
    }


}