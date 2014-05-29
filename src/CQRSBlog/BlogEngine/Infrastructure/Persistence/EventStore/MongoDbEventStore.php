<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb\PostProjection;
use MongoCollection;
use MongoDate;
use Symfony\Component\Serializer\Serializer;

final class MongoDbEventStore implements EventStore
{
    /**
     * @var MongoCollection
     */
    private $eventsCollection;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($aCollection, $aSerializer)
    {
        $this->eventsCollection = $aCollection;
        $this->serializer       = $aSerializer;
    }

    /**
     * @param DomainEvents $events
     * @return void
     */
    public function commit(DomainEvents $events)
    {
        foreach ($events as $event) {
            $this->eventsCollection->insert([
                'aggregate_id'  => (string) $event->getAggregateId(),
                'type'          => get_class($event),
                'event'         => $this->serializer->serialize($event, 'json'),
                'created_at'    => new MongoDate()
            ]);
        }
    }

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateHistory
     */
    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $cursor = $this->eventsCollection->find(['aggregate_id' => (string) $id]);
        $events = [];

        while ($cursor->hasNext()) {
            $doc = $cursor->getNext();
            $events[] = $this->serializer->deserialize($doc['event'], $doc['type'], 'json');
        }

        return new AggregateHistory($id, $events);
    }
}