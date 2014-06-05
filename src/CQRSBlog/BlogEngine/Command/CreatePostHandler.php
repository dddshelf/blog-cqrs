<?php

namespace CQRSBlog\BlogEngine\Command;

use CQRSBlog\BlogEngine\Command\CreatePostCommand;
use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\DomainModel\PostRepository;

class CreatePostHandler
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(CreatePostCommand $aCreatePostCommand)
    {
        $aNewPost = Post::create(
            PostId::generate(),
            $aCreatePostCommand->getTitle(),
            $aCreatePostCommand->getContent()
        );

        $this->postRepository->add($aNewPost);
    }
}