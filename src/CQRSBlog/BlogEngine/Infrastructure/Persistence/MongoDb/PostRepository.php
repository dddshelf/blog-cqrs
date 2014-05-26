<?php

namespace CQRSBlog\BlogEngine\Infrastructure\Persistence\MongoDb;

use BadMethodCallException;
use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\IsEventSourced;
use Buttercup\Protects\RecordsEvents;
use CQRSBlog\BlogEngine\DomainModel\PostRepository as BasePostRepository;
use MongoCollection;

final class PostRepository implements BasePostRepository
{
    /**
     * @var MongoCollection
     */
    private $postsCollection;

    /**
     * @param MongoCollection $postsCollection
     */
    public function __construct($postsCollection)
    {
        $this->postsCollection = $postsCollection;
    }

    /**
     * @param IdentifiesAggregate $aggregateId
     * @return IsEventSourced
     */
    public function get(IdentifiesAggregate $aggregateId)
    {
        return $this->postsCollection->findOne(['postId' => (string) $aggregateId]);
    }

    /**
     * @param RecordsEvents $aggregate
     * @throws BadMethodCallException
     * @return void
     */
    public function add(RecordsEvents $aggregate)
    {
        throw new BadMethodCallException('This repository cannot persist');
    }
}