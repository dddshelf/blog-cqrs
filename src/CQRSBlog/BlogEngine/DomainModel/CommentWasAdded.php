<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;

class CommentWasAdded implements DomainEvent
{
    /**
     * @var string
     */
    private $postId;

    /**
     * @var CommentId
     */
    private $commentId;

    /**
     * @var Comment
     */
    private $comment;

    public function __construct(PostId $postId, CommentId $commentId, $comment)
    {
        $this->postId       = $postId;
        $this->commentId    = $commentId;
        $this->comment      = $comment;
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
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return CommentId
     */
    public function getCommentId()
    {
        return $this->commentId;
    }
}
