<?php

namespace CQRSBlog\BlogEngine\Query;

final class PostQuery
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