<?php
namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

abstract class AbstractSession extends AbstractEntity implements \JsonSerializable
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
        $this->speakers = new ObjectStorage();
        $this->topics = new ObjectStorage();
        $this->votes = new ObjectStorage();
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
    abstract public function jsonSerialize();

    /**
     * @param \DateTime $date
     * @return int
     */
    protected function getTimestamp(\DateTime $date = null)
    {
        return (!empty($date) ? $date->getTimestamp() : 0);
    }

    /**
     * @param \DateTime|null $date
     * @return string|null
     */
    protected function getIsoDateTime(\DateTime $date = null)
    {
        if (empty($date)) {
            return null;
        }

        /**
         * This is a work-around
         * @see https://review.typo3.org/#/c/49527/
         */

        $utcTimeZone = new \DateTimeZone('UTC');
        $currentTimeZone = new \DateTimeZone(date_default_timezone_get());

        $utcDateTime = clone $date;
        $utcDateTime->setTimezone($utcTimeZone);

        $localDateTime = new \DateTime($utcDateTime->format('Y-m-d\TH:i:s'), $currentTimeZone);
        return $localDateTime->format('c');
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
