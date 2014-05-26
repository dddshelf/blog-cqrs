<?php

namespace CQRSBlog\BlogEngine\Query\Handler;

use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\DomainModel\PostRepository;
use CQRSBlog\BlogEngine\Query\Post;
use CQRSBlog\BlogEngine\Query\PostQuery;

final class PostQueryHandler
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(PostQuery $aPostQuery)
    {
        $aPostId = PostId::fromString($aPostQuery->id);
        $data = $this->postRepository->get($aPostId);

        $aPost = new Post();
        $aPost->title = $data['title'];
        $aPost->content = $data['content'];

        return $aPost;
    }
}