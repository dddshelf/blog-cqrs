<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore;

use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\IsEventSourced;
use Buttercup\Protects\RecordsEvents;
use CQRSBlog\BlogEngine\DomainModel\Post;
use CQRSBlog\BlogEngine\DomainModel\PostRepository as BasePostRepository;

final class PostRepository implements BasePostRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function __construct($eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param IdentifiesAggregate $aggregateId
     * @return IsEventSourced
     */
    public function get(IdentifiesAggregate $aggregateId)
    {
        $eventStream = $this->eventStore->getAggregateHistoryFor($aggregateId);

        return Post::reconstituteFrom($eventStream);
    }

    /**
     * @param RecordsEvents $aggregate
     * @return void
     */
    public function add(RecordsEvents $aggregate)
    {
        $this->eventStore->commit($aggregate->getRecordedEvents());

        $aggregate->clearRecordedEvents();
    }
}