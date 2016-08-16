<?php
namespace TYPO3\Sessions\Domain\Model;


class ScheduledSession extends AcceptedSession
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
            'start' => $this->getIsoDateTime($this->begin),
            'end' => $this->getIsoDateTime($this->end),
            'speakers'  =>  $this->speakers->toArray(),
            'room' => $this->room,
            'highlight' => $this->highlight,
            'links' => [
                'self' => $this->getLink(),
                'route' => $this->getRoute(),
            ]
        ];
    }
}
