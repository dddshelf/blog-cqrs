<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\Tests\EventStore;
use JMS\Serializer\Serializer;
use MongoCollection;
use MongoDate;

class MongoDbEventStore implements EventStore
{
    /**
     * @var MongoCollection
     */
    private $eventsCollection;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($eventsCollection, $serializer)
    {
        $this->eventsCollection = $eventsCollection;
        $this->serializer = $serializer;
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
                'created_at'    => new MongoDate(),
                'data'          => $this->serializer->serialize($event, 'json')
            ]);
        }
    }

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateHistory
     */
    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $rawEvents = $this->eventsCollection->find(['aggregate_id' => (string) $id]);

        $events = [];

        foreach ($rawEvents as $rawEvent) {
            $events[] = $this->serializer->deserialize(
                $rawEvent['data'],
                $rawEvent['type'],
                'json'
            );
        }

        return new AggregateHistory($id, $events);
    }
}