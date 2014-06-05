<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\Redis;

use CQRSBlog\BlogEngine\DomainModel\CommentWasAdded;
use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostContentWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostProjection as BasePostProjection;
use CQRSBlog\BlogEngine\DomainModel\PostTitleWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\DomainModel\PostWasPublished;
use CQRSBlog\BlogEngine\Infrastructure\Projection\BaseProjection;
use Predis\Client;

class PostProjection extends BaseProjection implements BasePostProjection
{
    /**
     * @var Client
     */
    private $predis;

    public function __construct($predis)
    {
        $this->predis = $predis;
    }

    /**
     * Projects a posts creation event
     *
     * @param PostWasCreated $event
     *
     * @return void
     */
    public function projectPostWasCreated(PostWasCreated $event)
    {
        $anAggregateId = $event->getAggregateId();

        $hash = $this->computePostHashFor($anAggregateId);

        $this->predis->hmset(
            $hash,
            [
                'title'     => $event->getTitle(),
                'content'   => $event->getContent(),
                'state'     => $event->getState()
            ]
        );

        $this->predis->rpush('posts', $hash);
    }

    /**
     * Projects when a post was published
     *
     * @param PostWasPublished $event
     *
     * @return void
     */
    public function projectPostWasPublished(PostWasPublished $event)
    {
        $this->predis->hset(
            $this->computePostHashFor($event->getAggregateId()),
            'state',
            Post::STATE_PUBLISHED
        );
    }

    /**
     * Projects when a post title was changed
     *
     * @param PostTitleWasChanged $event
     *
     * @return void
     */
    public function projectPostTitleWasChanged(PostTitleWasChanged $event)
    {
        $this->predis->hset(
            $this->computePostHashFor($event->getAggregateId()),
            'title',
            $event->getTitle()
        );
    }

    /**
     * Projects when a post content was changed
     *
     * @param PostContentWasChanged $event
     *
     * @return void
     */
    public function projectPostContentWasChanged(PostContentWasChanged $event)
    {
        $this->predis->hset(
            $this->computePostHashFor($event->getAggregateId()),
            'content',
            $event->getContent()
        );
    }

    /**
     * @param $anAggregateId
     * @return string
     */
    protected function computePostHashFor($anAggregateId)
    {
        return sprintf('posts:%s', $anAggregateId);
    }

    /**
     * Projects when a comment is added
     *
     * @param CommentWasAdded $event
     *
     * @return void
     */
    public function projectCommentWasAdded(CommentWasAdded $event)
    {
        $comments = unserialize($this->predis->hget(
            $this->computePostHashFor($event->getAggregateId()),
            'comments'
        ));

        $comments[] = [
            'commentId' => $event->getCommentId(),
            'comment'   => $event->getComment()
        ];

        $this->predis->hset(
            $this->computePostHashFor($event->getAggregateId()),
            'comments',
            serialize($comments)
        );
    }
}