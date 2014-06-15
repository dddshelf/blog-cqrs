<?php

namespace CQRSBlog\Common\DomainModel;

class RawDomainEvents
{
    /**
     * @var array
     */
    private $events;

    public function __construct(array $events)
    {
        $this->events = $events;
    }
}