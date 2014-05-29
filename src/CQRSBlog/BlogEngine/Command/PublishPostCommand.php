<?php

namespace CQRSBlog\BlogEngine\Command;

final class PublishPostCommand
{
    /**
     * @var string
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}