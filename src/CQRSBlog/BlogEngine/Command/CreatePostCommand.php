<?php

namespace CQRSBlog\BlogEngine\Command;

class CreatePostCommand
{
    private $title;
    private $content;

    public function __construct($content, $title)
    {
        $this->content = $content;
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}