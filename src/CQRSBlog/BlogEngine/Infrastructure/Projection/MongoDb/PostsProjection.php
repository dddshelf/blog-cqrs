<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb;

use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostContentWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostTitleWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\DomainModel\PostWasPublished;
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

    public function handlePostWasPublished(PostWasPublished $event)
    {
        $aPost = $this->findPostByIts((string) $event->getAggregateId());
        $aPost['state'] = Post::STATE_PUBLISHED;

        $this->postsCollection->save($aPost);
    }

    public function handlePostTitleWasChanged(PostTitleWasChanged $event)
    {
        $aPost = $this->findPostByIts((string) $event->getAggregateId());
        $aPost['title'] = $event->getTitle();

        $this->postsCollection->save($aPost);
    }

    public function handlePostContentWasChanged(PostContentWasChanged $event)
    {
        $aPost = $this->findPostByIts((string) $event->getAggregateId());
        $aPost['content'] = $event->getContent();

        $this->postsCollection->save($aPost);
    }

    private function findPostByIts($postId)
    {
        return $this->postsCollection->findOne(['postId' => $postId]);
    }
}