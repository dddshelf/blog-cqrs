<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb;

use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\Infrastructure\Projection\BaseProjection;
use CQRSBlog\BlogEngine\Query\PostsProjection as BasePostsProjection;
use MongoCollection;

final class PostsProjection extends BaseProjection implements BasePostsProjection
{
    /**
     * @var MongoCollection
     */
    private $postsCollection;

    public function __construct($aPostsCollection)
    {
        $this->postsCollection = $aPostsCollection;
    }

    public function handlePostWasCreated(PostWasCreated $event)
    {
        $this->postsCollection->insert([
            'postId'    => (string) $event->getAggregateId(),
            'title'     => $event->getTitle(),
            'content'   => $event->getContent(),
            'state'     => $event->getState()
        ]);
    }
}