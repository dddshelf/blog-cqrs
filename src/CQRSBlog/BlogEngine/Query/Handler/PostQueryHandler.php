<?php

namespace CQRSBlog\BlogEngine\Query\Handler;

use CQRSBlog\BlogEngine\DomainModel\PostViewRepository;
use CQRSBlog\BlogEngine\Query\PostQuery;

final class PostQueryHandler
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
        return $this->postViewRepository->find($aPostQuery->getId());
    }
}