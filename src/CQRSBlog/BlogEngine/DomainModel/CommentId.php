<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Rhumsaa\Uuid\Uuid;

class CommentId
{
    private $commentId;

    public function __construct($aCommentId)
    {
        $this->commentId = $aCommentId;
    }

    /**
     * Returns a string that can be parsed by fromString()
     * @return string
     */
    public function __toString()
    {
        return (string) $this->commentId;
    }

    public function equals(CommentId $other)
    {
        return $this->commentId === $other->commentId;
    }

    public static function generate()
    {
        return new CommentId(
            (string) Uuid::uuid1()
        );
    }
}