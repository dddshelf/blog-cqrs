<?php

namespace CQRSBlog\BlogEngine\Query;

use CQRSBlog\BlogEngine\Query\Handler\HandlerNotFoundException;
use Verraes\ClassFunctions\ClassFunctions;

final class QueryBus
{
    private $queryHandlers = [];

    public function handle($aQuery)
    {
        $anUnderscoredQueryClass = ClassFunctions::underscore($aQuery);

        if (!isset($this->queryHandlers[$anUnderscoredQueryClass])) {
            throw new HandlerNotFoundException(get_class($aQuery));
        }

        $aQueryHandler = $this->queryHandlers[$anUnderscoredQueryClass];
        return $aQueryHandler->handle($aQuery);
    }

    public function register($aQueryHandler)
    {
        $anUnderscoredQueryHandlerClass = ClassFunctions::underscore($aQueryHandler);
        $aQueryClass = str_replace(
            [
                '.handler',
                '_handler'
            ],
            [
                '',
                ''
            ],
            $anUnderscoredQueryHandlerClass
        );

        $this->queryHandlers[$aQueryClass] = $aQueryHandler;
    }
}