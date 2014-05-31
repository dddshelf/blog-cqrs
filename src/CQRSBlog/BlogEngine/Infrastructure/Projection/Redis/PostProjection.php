<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\Redis;

use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostContentWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostProjection as BasePostProjection;
use CQRSBlog\BlogEngine\DomainModel\PostTitleWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\DomainModel\PostWasPublished;
use CQRSBlog\BlogEngine\Infrastructure\Projection\BaseProjection;
use Predis\Client;

final class PostProjection extends BaseProjection implements BasePostProjection
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
    public function handlePostWasCreated(PostWasCreated $event)
    {
        $anAggregateId = $event->getAggregateId();

        $this->predis->hmset(
            $this->computePostHashFor($anAggregateId),
            [
                'title'     => $event->getTitle(),
                'content'   => $event->getContent(),
                'state'     => $event->getState()
            ]
        );
    }

    /**
     * Projects when a post was published
     *
     * @param PostWasPublished $event
     *
     * @return void
     */
    public function handlePostWasPublished(PostWasPublished $event)
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
    public function handlePostTitleWasChanged(PostTitleWasChanged $event)
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
    public function handlePostContentWasChanged(PostContentWasChanged $event)
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
}