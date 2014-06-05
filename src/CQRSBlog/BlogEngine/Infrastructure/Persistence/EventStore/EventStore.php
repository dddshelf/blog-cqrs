<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;

interface EventStore
{
    /**
     * @param DomainEvents $events
     * @param string $anSnapshot
     * @return void
     */
    public function commit(DomainEvents $events, $anSnapshot = null);

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateHistory
     */
    public function getAggregateHistoryFor(IdentifiesAggregate $id);
}