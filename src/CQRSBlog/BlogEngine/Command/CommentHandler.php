<?php

namespace CQRSBlog\BlogEngine\Command;

use CQRSBlog\BlogEngine\Command\CommentCommand;
use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\DomainModel\PostRepository;

class CommentHandler
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(CommentCommand $aCommentCommand)
    {
        $aNewPost = $this->postRepository->get(
            PostId::fromString($aCommentCommand->getPostId())
        );

        $aNewPost->comment($aCommentCommand->getComment());

        $this->postRepository->add($aNewPost);
    }
}