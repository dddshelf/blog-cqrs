<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;

final class PostWasCreated implements DomainEvent
{
    private $postId;
    private $title;
    private $content;
    private $state;

    public function __construct($postId, $title, $content, $state)
    {
        $this->postId = $postId;
        $this->title = $title;
        $this->content = $content;
        $this->state = $state;
    }

    /**
     * The Aggregate this event belongs to.
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }
}