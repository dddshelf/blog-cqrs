<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use DateTimeImmutable;
use JMS\Serializer\Serializer;
use Predis\Client;

class RedisEventStore implements EventStore
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($predis, $serializer)
    {
        $this->predis = $predis;
        $this->serializer = $serializer;
    }

    public function commit(DomainEvents $events, $anSnapshot = null)
    {
        foreach ($events as $event) {
            $eventType = get_class($event);
            $data = $this->serializer->serialize($event, 'json');

            $this->predis->rpush(
                $this->computeHashFor($event->getAggregateId()),
                $this->serializer->serialize([
                    'type' => $eventType,
                    'created_on' => (new DateTimeImmutable())->format('YmdHis'),
                    'data' => $data
                ], 'json')
            );
        }
    }

    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $serializedEvents = $this->predis->lrange($this->computeHashFor($id), 0, -1);

        $eventStream = [];

        foreach ($serializedEvents as $serializedEvent) {
            $eventData = $this->serializer->deserialize($serializedEvent, 'array', 'json');
            $eventStream[] = $this->serializer->deserialize($eventData['data'], $eventData['type'], 'json');
        }

        return new AggregateHistory($id, $eventStream);
    }

    private function computeHashFor(IdentifiesAggregate $anAggregateId)
    {
        return sprintf('events:%s', $anAggregateId);
    }
}