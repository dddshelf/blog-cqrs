<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb\PostsProjection;
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

    /**
     * @var PostsProjection
     */
    private $postsProjection;

    public function __construct($aCollection, $aSerializer, $aPostsProjection)
    {
        $this->eventsCollection = $aCollection;
        $this->serializer       = $aSerializer;
        $this->postsProjection  = $aPostsProjection;
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

        $this->postsProjection->project($events);
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