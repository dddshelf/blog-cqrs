<?php

namespace CQRSBlog\BlogEngine\Command;

use CQRSBlog\BlogEngine\DomainModel\Post;
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
        /** @var Post $aNewPost */
        $aNewPost = $this->postRepository->get(
            PostId::fromString($aCommentCommand->getPostId())
        );

        $aNewPost->comment($aCommentCommand->getComment());

        $this->postRepository->add($aNewPost);
    }
}