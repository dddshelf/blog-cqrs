<?php

namespace CQRSBlog\Common\DomainModel;

use Buttercup\Protects\DomainEvents;

interface Projection
{
    public function project(DomainEvents $eventStream);
}