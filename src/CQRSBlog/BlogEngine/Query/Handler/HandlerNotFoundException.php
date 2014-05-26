<?php

namespace CQRSBlog\BlogEngine\Query\Handler;

use Exception;

final class HandlerNotFoundException extends Exception
{
    public function __construct($aQueryClass)
    {
        parent::__construct('Unable to find a registered handler for the query class "' . $aQueryClass . '"', 0, null);
    }
}