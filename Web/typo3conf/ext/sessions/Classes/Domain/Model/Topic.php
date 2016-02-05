<?php
namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;


class Topic extends AbstractEntity implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var \TYPO3\Sessions\Domain\Model\Topic
     */
    protected $parent;

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
     * @return Topic
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Topic $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            '__identity'  =>  $this->uid,
            'title'     =>  $this->title,
            'parent'    =>  $this->parent
        ];
    }

}
