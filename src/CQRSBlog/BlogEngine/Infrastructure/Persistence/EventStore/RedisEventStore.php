<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use Predis\Client;

final class RedisEventStore implements EventStore
{
    /**
     * @var Client
     */
    private $predis;

    public function __construct($predis)
    {
        $this->predis = $predis;
    }

    public function commit(DomainEvents $events)
    {
        foreach ($events as $event) {
            $this->predis->rpush(
                $this->computeHashFor((string) $event->getAggregateId()),
                serialize($event)
            );
        }
    }

    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $anAggregateId = (string) $id;
        $serializedEvents = $this->predis->lrange($this->computeHashFor($anAggregateId), 0, -1);

        $eventStream = [];
        foreach ($serializedEvents as $serializedEvent) {
            $eventStream[] = unserialize($serializedEvent);
        }

        return new AggregateHistory($id, $eventStream);
    }

    private function computeHashFor($anAggregateId)
    {
        return sprintf('events:%s', $anAggregateId);
    }
}