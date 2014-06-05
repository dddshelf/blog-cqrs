<?php

namespace CQRSBlog\BlogEngine\Command;

class CommentCommand
{
    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $postId;

    public function __construct($postId, $comment)
    {
        $this->comment = $comment;
        $this->postId = $postId;
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
    public function getComment()
    {
        return $this->comment;
    }
}