<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use CQRSBlog\Common\DomainModel\RawDomainEvents;
use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use JMS\Serializer\Serializer;
use ReflectionObject;
use ReflectionProperty;
use Rhumsaa\Uuid\Uuid;
use Verraes\ClassFunctions\ClassFunctions;

class EventStore
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $eventStoreHost;

    public function __construct($eventStoreHost, $client, $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->eventStoreHost = $eventStoreHost;
    }

    public function commit(DomainEvents $events)
    {
        $requestBody = $this->serializer->serialize(
            $this->prepareEventsForSerialization($events),
            'xml'
        );

        $request = $this->client->createRequest(
            'POST',
            $this->urlFor($events->getAggregateId()),
            [
                'headers' => [
                    'Content-Type' => 'application/vnd.eventstore.events+xml'
                ],
                'body' => Stream::factory(
                    $requestBody
                )
            ]
        );

        $this->client->send($request);
    }

    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $content = json_decode(
            file_get_contents(
                $this->urlForWithBodyEmbeded($id)
            ),
            true
        );

        $events = [];

        foreach ($content['entries'] as $entry) {
            $events[] = $this->serializer->deserialize(
                $entry['data'],
                $this->getEventType($entry['eventType']),
                'json'
            );
        }

        return new AggregateHistory($id, $events);
    }

    private function prepareEventsForSerialization(DomainEvents $events)
    {
        $rawEvents = [];

        foreach ($events as $event) {
            $rawEvents[] = [
                'EventId'       => (string) Uuid::uuid1(),
                'EventType'     => ClassFunctions::underscore($event),
                'Data'          => $this->extractEventData($event)
            ];
        }

        return new RawDomainEvents($rawEvents);
    }

    private function urlFor(IdentifiesAggregate $id)
    {
        return sprintf('http://%s/streams/%s', $this->eventStoreHost, $id);
    }

    private function urlForWithBodyEmbeded(IdentifiesAggregate $id)
    {
        return $this->urlFor($id) . '?embed=body';
    }

    private function getEventType($entry)
    {
        return str_replace(
            ' ',
            '\\',
            ucwords(
                str_replace(
                    '.',
                    ' ',
                    str_replace(
                        ' ',
                        '',
                        ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $entry
                            )
                        )
                    )
                )
            )
        );
    }

    private function extractEventData($event)
    {
        $reflectedEvent = new ReflectionObject($event);

        $rawEvent = [];

        /** @var ReflectionProperty $eventProperty */
        foreach ($reflectedEvent->getProperties() as $eventProperty) {
            $eventProperty->setAccessible(true);
            $eventName = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $eventProperty->getName()));

            $rawEvent[$eventName] = $eventProperty->getValue($event);
        }

        return $rawEvent;
    }
}