<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection;

use Buttercup\Protects\DomainEvents;
use CQRSBlog\BlogEngine\Query\Projection;
use Verraes\ClassFunctions\ClassFunctions;

abstract class BaseProjection implements Projection
{
    public function project(DomainEvents $eventStream)
    {
        foreach ($eventStream as $event) {
            $handleMetohd = 'handle' . ClassFunctions::short($event);
            $this->$handleMetohd($event);
        }
    }
}