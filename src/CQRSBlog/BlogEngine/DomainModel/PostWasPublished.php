<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;

final class PostWasPublished implements DomainEvent
{
    /**
     * @var IdentifiesAggregate
     */
    private $postId;

    public function __construct($aPostId)
    {
        $this->postId = $aPostId;
    }

    /**
     * The Aggregate this event belongs to.
     *
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->postId;
    }
}