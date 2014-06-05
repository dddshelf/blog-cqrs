<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Projection;

use Buttercup\Protects\DomainEvents;
use CQRSBlog\Common\DomainModel\Projection;
use Verraes\ClassFunctions\ClassFunctions;

abstract class BaseProjection implements Projection
{
    public function project(DomainEvents $eventStream)
    {
        foreach ($eventStream as $event) {
            $projectMetohd = 'project' . ClassFunctions::short($event);
            $this->$projectMetohd($event);
        }
    }
}