<?php

namespace CQRSBlog\BlogEngine\Command;

final class UpdatePostCommand
{
    private $postId;
    private $title;
    private $content;

    public function __construct($postId, $content, $title)
    {
        $this->postId = $postId;
        $this->content = $content;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}