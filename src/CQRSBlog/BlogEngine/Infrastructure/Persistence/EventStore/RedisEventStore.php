<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use Predis\Client;

class RedisEventStore implements EventStore
{
    /**
     * @var Client
     */
    private $predis;

    public function __construct($predis)
    {
        $this->predis = $predis;
    }

    public function commit(DomainEvents $events, $anSnapshot = null)
    {
        foreach ($events as $event) {
            $this->predis->rpush(
                $this->computeHashFor($event->getAggregateId()),
                serialize($event)
            );
        }
    }

    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $serializedEvents = $this->predis->lrange($this->computeHashFor($id), 0, -1);

        $eventStream = [];

        foreach ($serializedEvents as $serializedEvent) {
            $eventStream[] = unserialize($serializedEvent);
        }

        return new AggregateHistory($id, $eventStream);
    }

    private function computeHashFor(IdentifiesAggregate $anAggregateId)
    {
        return sprintf('events:%s', $anAggregateId);
    }
}