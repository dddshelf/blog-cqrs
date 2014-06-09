<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb;

use CQRSBlog\BlogEngine\DomainModel\CommentWasAdded;
use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostContentWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostProjection as BasePostProjection;
use CQRSBlog\BlogEngine\DomainModel\PostTitleWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\DomainModel\PostWasPublished;
use CQRSBlog\BlogEngine\Infrastructure\Projection\BaseProjection;
use MongoCollection;

class PostProjection extends BaseProjection implements BasePostProjection
{
    /**
     * @var MongoCollection
     */
    private $postsCollection;

    public function __construct($postsCollection)
    {
        $this->postsCollection = $postsCollection;
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
        $this->postsCollection->insert([
            'post_id' => (string) $event->getAggregateId(),
            'title'   => $event->getTitle(),
            'content' => $event->getContent(),
            'state'   => $event->getState()
        ]);
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
        $post = $this->postsCollection->findOne(['post_id' => (string) $event->getAggregateId()]);

        $post['state'] = Post::STATE_PUBLISHED;

        $this->postsCollection->save($post);
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
        $post = $this->postsCollection->findOne(['post_id' => (string) $event->getAggregateId()]);

        $post['title'] = $event->getTitle();

        $this->postsCollection->save($post);
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
        $post = $this->postsCollection->findOne(['post_id' => (string) $event->getAggregateId()]);

        $post['content'] = $event->getContent();

        $this->postsCollection->save($post);
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
        $post = $this->postsCollection->findOne(['post_id' => (string) $event->getAggregateId()]);

        if (!isset($post['comments'])) {
            $post['comments'] = [];
        }

        $post['comments'] = [
            'comment_id' => (string) $event->getCommentId(),
            'comment'    => $event->getComment()
        ];

        $this->postsCollection->save($post);
    }
}