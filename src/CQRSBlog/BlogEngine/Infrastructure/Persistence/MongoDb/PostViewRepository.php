<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\MongoDb;

use CQRSBlog\BlogEngine\DomainModel\PostView;
use CQRSBlog\BlogEngine\DomainModel\PostViewRepository as BasePostViewRepository;
use MongoCollection;

class PostViewRepository implements BasePostViewRepository
{
    /**
     * @var MongoCollection
     */
    private $postsComments;

    public function __construct($postsComments)
    {
        $this->postsComments = $postsComments;
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
        $rawPost = $this->postsComments->findOne(['post_id' => $id]);

        return $this->newPostView($rawPost);
    }

    /**
     * Get all of the post views
     *
     * @return PostView[]
     */
    public function all()
    {
        $rawPosts = $this->postsComments->find();

        $posts = [];

        foreach ($rawPosts as $rawPost) {
            $posts[] = $this->newPostView($rawPost);
        }

        return $posts;
    }

    private function newPostView($rawPost)
    {
        return new PostView(
            $rawPost['post_id'],
            $rawPost['title'],
            $rawPost['content'],
            isset($rawPost['comments']) ? $rawPost['comments'] : []
        );
    }
}