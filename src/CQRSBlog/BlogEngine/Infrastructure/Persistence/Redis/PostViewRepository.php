<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\Redis;

use CQRSBlog\BlogEngine\DomainModel\PostView;
use CQRSBlog\BlogEngine\DomainModel\PostViewRepository as BasePostViewRepository;
use Predis\Client;

class PostViewRepository implements BasePostViewRepository
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
     * Finds a post view by its id
     *
     * @param string $id
     *
     * @return PostView
     */
    public function get($id)
    {
        $rawPostView = $this->predis->hgetall(sprintf('posts:%s', $id));

        return new PostView(
            $id,
            $rawPostView['title'],
            $rawPostView['content'],
            isset($rawPostView['comments']) ? unserialize($rawPostView['comments']) : []
        );
    }

    /**
     * Get all of the post views
     *
     * @return PostView[]
     */
    public function all()
    {
        $postIds = $this->predis->lrange('posts', 0, -1);

        if (empty($postIds)) {
            return [];
        }

        $posts = [];

        foreach ($postIds as $postId) {
            $posts[] = $this->get(explode(':', $postId)[1]);
        }

        return $posts;
    }
}