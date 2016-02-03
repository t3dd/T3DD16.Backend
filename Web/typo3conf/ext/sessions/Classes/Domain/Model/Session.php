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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUser>
     */
    protected $speakers;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Topic>
     * @lazy
     */
    protected $topics;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Vote>
     * @lazy
     */
    protected $votes;

    /**
     * @var \TYPO3\Sessions\Domain\Model\Room
     */
    protected $room;

    /**
     * @var boolean
     */
    protected $highlight;

    public function __construct()
    {
        $this->speakers = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->topics = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->votes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

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
            'speakers'  =>  $this->speakers->toArray(),
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

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUser>
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUser> $speakers
     */
    public function setSpeakers($speakers)
    {
        $this->speakers = $speakers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $speaker
     */
    public function addSpeaker($speaker)
    {
        $this->speakers->attach($speaker);
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $speaker
     */
    public function removeSpeaker($speaker)
    {
        $this->speakers->detach($speaker);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Topic>
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Topic> $topics
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;
    }

    /**
     * @param \TYPO3\Sessions\Domain\Model\Topic $topic
     */
    public function addTopic($topic)
    {
        $this->topics->attach($topic);
    }

    /**
     * @param \TYPO3\Sessions\Domain\Model\Topic topic
     */
    public function removeTopic($topic)
    {
        $this->topics->detach($topic);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Vote>
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\Sessions\Domain\Model\Vote> $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @param \TYPO3\Sessions\Domain\Model\Vote $vote
     */
    public function addVote($vote)
    {
        $this->votes->attach($vote);
    }

    /**
     * @param \TYPO3\Sessions\Domain\Model\Vote vote
     */
    public function removeVote($vote)
    {
        $this->votes->detach($vote);
    }

}
