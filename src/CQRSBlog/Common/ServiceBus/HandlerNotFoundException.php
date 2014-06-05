<?php

namespace CQRSBlog\Common\ServiceBus;

use Exception;

class HandlerNotFoundException extends Exception
{
    public function __construct($aQueryClass)
    {
        parent::__construct(sprintf('Unable to find a registered handler for "%s"', $aQueryClass), 0, null);
    }
}