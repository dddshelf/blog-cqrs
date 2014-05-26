<?php

namespace CQRSBlog\BlogEngine\Command\Handler;

use Exception;

final class HandlerNotFoundException extends Exception
{
    public function __construct($aCommandClass)
    {
        parent::__construct('Unable to find a registered handler for the command class "' . $aCommandClass . '"', 0, null);
    }
}