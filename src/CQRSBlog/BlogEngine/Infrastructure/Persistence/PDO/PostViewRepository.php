<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\PDO;

use CQRSBlog\BlogEngine\DomainModel\PostView;
use CQRSBlog\BlogEngine\DomainModel\PostViewRepository as BasePostViewRepository;
use PDO;

class PostViewRepository implements BasePostViewRepository
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
     * Get a post view by its id
     *
     * @param string $id
     *
     * @return PostView
     */
    public function get($id)
    {
        $stmt = $this->pdo->query('SELECT * FROM posts_with_comments WHERE post_id = :post_id');
        $stmt->execute([
            ':post_id' => $id
        ]);

        $title = $content = null;
        $comments = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (null === $title) {
                $title = $row['TITLE'];
                $content = $row['CONTENT'];
            }

            $comments[] = [
                'comment_id' => $row['COMMENT_ID'],
                'comment'    => $row['COMMENT']
            ];
        }

        return new PostView(
            $id,
            $title,
            $content,
            $comments
        );
    }

    /**
     * Get all of the post views
     *
     * @return PostView[]
     */
    public function all()
    {
        $stmt = $this->pdo->query('SELECT * FROM posts');
        $stmt->execute();

        $posts = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = new PostView(
                $row['POST_ID'],
                $row['TITLE'],
                $row['CONTENT'],
                []
            );
        }

        return $posts;
    }
}