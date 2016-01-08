<?php
namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Session extends AbstractEntity implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $date = null;

    /**
     * @var \DateTime
     */
    protected $lightning = null;

    /**
     * @var \DateTime
     */
    protected $begin = null;

    /**
     * @var \DateTime
     */
    protected $end = null;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $speaker1;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $speaker2;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $speaker3;

    /**
     * @var \TYPO3\Sessions\Domain\Model\Room
     */
    protected $room;

    /**
     * @var boolean
     */
    protected $highlight;

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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getLightning()
    {
        return $this->lightning;
    }

    /**
     * @param \DateTime $lightning
     */
    public function setLightning($lightning)
    {
        $this->lightning = $lightning;
    }

    /**
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTime $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return FrontendUser
     */
    public function getSpeaker1()
    {
        return $this->speaker1;
    }

    /**
     * @param FrontendUser $speaker1
     */
    public function setSpeaker1($speaker1)
    {
        $this->speaker1 = $speaker1;
    }

    /**
     * @return FrontendUser
     */
    public function getSpeaker2()
    {
        return $this->speaker2;
    }

    /**
     * @param FrontendUser $speaker2
     */
    public function setSpeaker2($speaker2)
    {
        $this->speaker2 = $speaker2;
    }

    /**
     * @return FrontendUser
     */
    public function getSpeaker3()
    {
        return $this->speaker3;
    }

    /**
     * @param FrontendUser $speaker3
     */
    public function setSpeaker3($speaker3)
    {
        $this->speaker3 = $speaker3;
    }

    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param Room $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return boolean
     */
    public function isHighlight()
    {
        return $this->highlight;
    }

    /**
     * @param boolean $highlight
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return [
            '__identity' => $this->uid,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->getTimestamp($this->date),
            'lightning' => $this->getTimestamp($this->lightning),
            'start' => $this->getTimestamp($this->begin),
            'end' => $this->getTimestamp($this->end),
            'speaker1' => $this->speaker1,
            'speaker2' => $this->speaker2,
            'speaker3' => $this->speaker3,
            'room' => $this->room ? $this->room->getTitle() : '',
            'highlight' => $this->highlight,
            'links' => [
                'self' => $this->getLink(),
                'route' => $this->getRoute(),
            ]
        ];
    }

    /**
     * @param \DateTime $date
     * @return int
     */
    protected function getTimestamp(\DateTime $date = null)
    {
        return $date ? $date->getTimestamp() : 0;
    }

    /**
     * @return string
     */
    protected function getLink()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        return $uriBuilder->setCreateAbsoluteUri(true)->setTargetPageType(1450887489)->uriFor('index', ['session' => $this], 'Session', 'Sessions', 'sessions');
    }

    /**
     * @return string
     */
    protected function getRoute()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        return $uriBuilder->setCreateAbsoluteUri(true)->uriFor('index', ['session' => $this], 'Session', 'Sessions', 'sessions');
    }
}