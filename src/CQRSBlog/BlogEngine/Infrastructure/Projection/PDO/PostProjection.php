<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection\PDO;

use CQRSBlog\BlogEngine\DomainModel\CommentWasAdded;
use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostContentWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostProjection as BasePostProjection;
use CQRSBlog\BlogEngine\DomainModel\PostTitleWasChanged;
use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;
use CQRSBlog\BlogEngine\DomainModel\PostWasPublished;
use CQRSBlog\BlogEngine\Infrastructure\Projection\BaseProjection;
use PDO;

class PostProjection extends BaseProjection implements BasePostProjection
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
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
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (post_id, title, content, state)
             VALUES (:post_id, :title, :content, :state)'
        );

        $stmt->execute([
            ':post_id' => $event->getAggregateId(),
            ':title'   => $event->getTitle(),
            ':content' => $event->getContent(),
            ':state'   => $event->getState()
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
        $stmt = $this->pdo->prepare(
            'UPDATE posts SET state = :state WHERE post_id = :post_id'
        );

        $stmt->execute([
            ':state'    => Post::STATE_PUBLISHED,
            ':post_id'  => $event->getAggregateId()
        ]);
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
        $stmt = $this->pdo->prepare(
            'UPDATE posts SET title = :title WHERE post_id = :post_id'
        );

        $stmt->execute([
            ':title'    => $event->getTitle(),
            ':post_id'  => $event->getAggregateId()
        ]);
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
        $stmt = $this->pdo->prepare(
            'UPDATE posts SET content = :content WHERE post_id = :post_id'
        );

        $stmt->execute([
            ':content' => $event->getContent(),
            ':post_id' => $event->getAggregateId()
        ]);
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
        $stmt = $this->pdo->query('SELECT * FROM posts WHERE post_id = :post_id');
        $stmt->bindParam(':post_id', $event->getAggregateId());
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $stmt = $this->pdo->prepare(
            'INSERT INTO posts_with_comments (post_id, comment_id, title, content, state, comment)
             VALUES (:post_id, :comment_id, :title, :content, :state, :comment)'
        );

        $stmt->execute([
            ':post_id' => $event->getAggregateId(),
            ':comment_id' => $event->getCommentId(),
            ':title' => $post['TITLE'],
            ':content' => $post['CONTENT'],
            ':state' => $post['STATE'],
            ':comment' => $post['COMMENT']
        ]);
    }
}