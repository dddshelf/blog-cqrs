<?php

namespace CQRSBlog\BlogEngine\Query;

use CQRSBlog\BlogEngine\DomainModel\PostViewRepository;

class AllPostsQueryHandler
{
    /**
     * @var PostViewRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(AllPostsQuery $anAllPostsQuery)
    {
        return $this->postRepository->all();
    }
}