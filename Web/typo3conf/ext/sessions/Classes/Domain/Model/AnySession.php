<?php
namespace TYPO3\Sessions\Domain\Model;


class AnySession extends AbstractSession
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            '__identity' => $this->uid,
            'title' => $this->title,
        ];
    }
}
