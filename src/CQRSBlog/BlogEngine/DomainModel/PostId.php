<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\IdentifiesAggregate;
use Rhumsaa\Uuid\Uuid;

final class PostId implements IdentifiesAggregate
{
    private $postId;

    public function __construct($aPostId)
    {
        $this->postId = $aPostId;
    }

    /**
     * Creates an identifier object from a string representation
     * @param string $aPostId
     * @return IdentifiesAggregate
     */
    public static function fromString($aPostId)
    {
        return new PostId($aPostId);
    }

    /**
     * Returns a string that can be parsed by fromString()
     * @return string
     */
    public function __toString()
    {
        return (string) $this->postId;
    }

    /**
     * Compares the object to another IdentifiesAggregate object. Returns true if both have the same type and value.
     * @param $other
     * @return boolean
     */
    public function equals(IdentifiesAggregate $other)
    {
        return
            $other instanceof PostId
            && $this->postId === $other->postId
        ;
    }

    public static function generate()
    {
        return new PostId(
            (string) Uuid::uuid1()
        );
    }
}