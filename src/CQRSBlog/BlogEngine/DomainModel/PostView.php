<?php

namespace CQRSBlog\BlogEngine\DomainModel;

class PostView
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $comments;

    public function __construct($id, $title, $content, array $comments = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->comments = $comments;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }
}