<?php

namespace CQRSBlog\BlogEngine\Query;

use Buttercup\Protects\DomainEvents;

interface Projection
{
    public function project(DomainEvents $eventStream);
}