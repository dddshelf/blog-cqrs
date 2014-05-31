<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\DomainEvents;

interface Projection
{
    public function project(DomainEvents $eventStream);
}