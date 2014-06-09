<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\Tests\EventStore;
use DateTimeImmutable;
use JMS\Serializer\Serializer;
use PDO;

class PDOEventStore implements EventStore
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct($pdo, $serializer)
    {
        $this->pdo = $pdo;
        $this->serializer = $serializer;
    }

    /**
     * @param DomainEvents $events
     * @return void
     */
    public function commit(DomainEvents $events)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO events (aggregate_id, `type`, created_at, `data`)
             VALUES (:aggregate_id, :type, :created_at, :data)'
        );

        foreach ($events as $event) {
            $stmt->execute([
                ':aggregate_id' => (string) $event->getAggregateId(),
                ':type'         => get_class($event),
                ':created_at'   => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                ':data'         => $this->serializer->serialize($event, 'json')
            ]);
        }
    }

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateHistory
     */
    public function getAggregateHistoryFor(IdentifiesAggregate $id)
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM events WHERE aggregate_id = :aggregate_id'
        );
        $stmt->execute([':aggregate_id' => (string) $id]);

        $events = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->serializer->deserialize(
                $row['data'],
                $row['type'],
                'json'
            );
        }

        $stmt->closeCursor();

        return new AggregateHistory($id, $events);
    }
}