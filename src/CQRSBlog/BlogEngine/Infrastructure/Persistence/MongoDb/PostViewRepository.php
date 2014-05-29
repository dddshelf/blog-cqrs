<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\MongoDb;

use CQRSBlog\BlogEngine\DomainModel\PostView;
use CQRSBlog\BlogEngine\DomainModel\PostViewRepository as BasePostViewRepository;
use MongoCollection;

final class PostViewRepository implements BasePostViewRepository
{
    /**
     * @var MongoCollection
     */
    private $postsCollection;

    /**
     * @param MongoCollection $postsCollection
     */
    public function __construct($postsCollection)
    {
        $this->postsCollection = $postsCollection;
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
        $doc = $this->postsCollection->findOne(['postId' => $id]);

        return new PostView($doc['postId'], $doc['title'], $doc['content']);
    }
}