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
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var bool
     */
    protected $auditorium = false;

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
        return [
            '__identity' => $this->uid,
            'title' => $this->title,
            'size' => $this->size,
            'location' => $this->location,
            'auditorium' => $this->auditorium,
        ];
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

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return bool
     */
    public function isAuditorium()
    {
        return $this->auditorium;
    }

    /**
     * @param bool $auditorium
     */
    public function setAuditorium($auditorium)
    {
        $this->auditorium = (bool)$auditorium;
    }
}
