<?php

namespace CQRSBlog\BlogEngine\DomainModel;

interface PostViewRepository
{
    /**
     * Finds a post view by its id
     *
     * @param string $id
     *
     * @return PostView
     */
    public function find($id);
}