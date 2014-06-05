<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;

class PostContentWasChanged implements DomainEvent
{
    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var string
     */
    private $content;

    public function __construct(PostId $postId, $content)
    {
        $this->content = $content;
        $this->postId = $postId;
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

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}