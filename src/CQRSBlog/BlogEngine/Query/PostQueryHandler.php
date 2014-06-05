<?php

namespace CQRSBlog\BlogEngine\Query;

use CQRSBlog\BlogEngine\DomainModel\PostViewRepository;
use CQRSBlog\BlogEngine\Query\PostQuery;

class PostQueryHandler
{
    /**
     * @var PostViewRepository
     */
    private $postViewRepository;

    public function __construct($postViewRepository)
    {
        $this->postViewRepository = $postViewRepository;
    }

    public function handle(PostQuery $aPostQuery)
    {
        return $this->postViewRepository->get($aPostQuery->getId());
    }
}