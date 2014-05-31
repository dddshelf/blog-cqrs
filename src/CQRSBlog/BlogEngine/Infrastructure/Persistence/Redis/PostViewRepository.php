<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\Redis;

use CQRSBlog\BlogEngine\DomainModel\PostView;
use CQRSBlog\BlogEngine\DomainModel\PostViewRepository as BasePostViewRepository;
use Predis\Client;

final class PostViewRepository implements BasePostViewRepository
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
    public function find($id)
    {
        $rawPostView = $this->predis->hgetall(sprintf('posts:%s', $id));

        return new PostView($id, $rawPostView['title'], $rawPostView['content']);
    }
}