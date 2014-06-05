<?php

namespace CQRSBlog\BlogEngine\DomainModel;

interface PostViewRepository
{
    /**
     * Get a post view by its id
     *
     * @param string $id
     *
     * @return PostView
     */
    public function get($id);

    /**
     * Get all of the post views
     *
     * @return PostView[]
     */
    public function all();
}