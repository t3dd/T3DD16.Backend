<?php
namespace TYPO3\Sessions\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class ProposedSession extends AnySession
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            '__identity' => $this->uid,
            'title' => $this->title,
            'description' => $this->description,
            'start' => null,
            'end' => null,
            'speakers'  =>  $this->speakers->toArray(),
            'room' => $this->room,
            'highlight' => $this->highlight,
            'votes' => $this->votes->count(),
            'links' => [
                'self' => $this->getLink(),
                'route' => $this->getRoute(),
            ]
        ];
    }
}
