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
     * @var integer
     */
    protected $size;

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

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }


}
