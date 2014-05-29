<?php

namespace CQRSBlog\BlogEngine\Command\Handler;

use CQRSBlog\BlogEngine\Command\UpdatePostCommand;
use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\DomainModel\PostRepository;

final class UpdatePostHandler
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(UpdatePostCommand $anUpdatePostCommand)
    {
        $aPost = $this->postRepository->get(
            PostId::fromString($anUpdatePostCommand->getPostId())
        );

        $aPost->changeTitle($anUpdatePostCommand->getTitle());
        $aPost->changeContent($anUpdatePostCommand->getContent());

        $this->postRepository->add($aPost);
    }
}