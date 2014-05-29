<?php

namespace CQRSBlog\BlogEngine\Command\Handler;

use CQRSBlog\BlogEngine\Command\PublishPostCommand;
use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\DomainModel\PostRepository;

final class PublishPostHandler
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct($postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function handle(PublishPostCommand $aPublishPostCommand)
    {
        $aPost = $this->postRepository->get(PostId::fromString($aPublishPostCommand->getId()));

        $aPost->publish();

        $this->postRepository->add($aPost);
    }
}