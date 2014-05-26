<?php

namespace CQRSBlog\BlogEngine\Query;

use CQRSBlog\BlogEngine\DomainModel\PostWasCreated;

interface PostsProjection extends Projection
{
    public function handlePostWasCreated(PostWasCreated $event);
}