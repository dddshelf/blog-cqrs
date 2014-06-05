<?php

namespace CQRSBlog\Common\ServiceBus;

use Verraes\ClassFunctions\ClassFunctions;

class CommandBus
{
    private $commandHandlers = [];

    public function handle($aCommand)
    {
        $anUnderscoredCommandClass = ClassFunctions::underscore($aCommand);

        if (!isset($this->commandHandlers[$anUnderscoredCommandClass])) {
            throw new HandlerNotFoundException(get_class($aCommand));
        }

        $aCommandHandler = $this->commandHandlers[$anUnderscoredCommandClass];
        $aCommandHandler->handle($aCommand);
    }

    public function register($aCommandHandler)
    {
        $aCommandClass = str_replace(
            [
                '.handler',
                '_handler'
            ],
            [
                '',
                '_command'
            ],
            ClassFunctions::underscore($aCommandHandler)
        );

        $this->commandHandlers[$aCommandClass] = $aCommandHandler;
    }
}