<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IsEventSourced;
use Buttercup\Protects\RecordsEvents;
use Verraes\ClassFunctions\ClassFunctions;

class Post implements RecordsEvents, IsEventSourced
{
    const STATE_DRAFT = 10;
    const STATE_PUBLISHED = 20;

    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var DomainEvent[]
     */
    private $recordedEvents = [];

    /**
     * @var int
     */
    private $state;

    /**
     * @var Comment[]
     */
    private $comments;

    private function __construct($postId, $content, $title, $state, array $comments = [])
    {
        $this->postId = $postId;
        $this->content = $content;
        $this->title = $title;
        $this->state = $state;
    }

    /**
     * @param $aPostId
     * @return Post
     */
    private static function createEmptyPostWith($aPostId)
    {
        return new Post($aPostId, '', '', static::STATE_DRAFT);
    }

    /**
     * Get all the Domain Events that were recorded since the last time it was cleared, or since it was
     * restored from persistence. This does not include events that were recorded prior.
     *
     * @return DomainEvents
     */
    public function getRecordedEvents()
    {
        return new AggregateHistory(
            $this->postId,
            $this->recordedEvents
        );
    }

    /**
     * Clears the record of new Domain Events. This doesn't clear the history of the object.
     * @return void
     */
    public function clearRecordedEvents()
    {
        $this->recordedEvents = [];
    }

    public static function create($aPostId, $aTitle, $aContent)
    {
        $aNewPost = new Post($aPostId, $aTitle, $aContent, static::STATE_DRAFT);

        $aNewPost->recordThat(
            new PostWasCreated($aPostId, $aTitle, $aContent, static::STATE_DRAFT)
        );

        return $aNewPost;
    }

    public function publish()
    {
        $this->applyAndRecordThat(
            new PostWasPublished($this->postId)
        );
    }

    public function changeTitle($aNewTitle)
    {
        $this->applyAndRecordThat(
            new PostTitleWasChanged($this->postId, $aNewTitle)
        );
    }

    public function changeContent($aNewContent)
    {
        $this->applyAndRecordThat(
            new PostContentWasChanged($this->postId, $aNewContent)
        );
    }

    public function comment($aNewComment)
    {
        $this->applyAndRecordThat(
            new CommentWasAdded($this->postId, CommentId::generate(), $aNewComment)
        );
    }

    private function recordThat(DomainEvent $aDomainEvent)
    {
        $this->recordedEvents[] = $aDomainEvent;
    }

    private function applyAndRecordThat(DomainEvent $aDomainEvent)
    {
        $this->recordThat($aDomainEvent);

        $this->apply($aDomainEvent);
    }

    /**
     * Allow to reconstitute an aggregate from an aggregate events history and an initial state
     *
     * @param AggregateHistory $anAggregateHistory
     *
     * @return RecordsEvents
     */
    public static function reconstituteFrom(AggregateHistory $anAggregateHistory)
    {
        $aPost = static::createEmptyPostWith($anAggregateHistory->getAggregateId());

        foreach ($anAggregateHistory as $anEvent) {
            $aPost->apply($anEvent);
        }

        return $aPost;
    }

    private function apply($anEvent)
    {
        $method = 'apply' . ClassFunctions::short($anEvent);
        $this->$method($anEvent);
    }

    private function applyPostWasCreated(PostWasCreated $event)
    {
        $this->title = $event->getTitle();
        $this->content = $event->getContent();
    }

    private function applyPostWasPublished(PostWasPublished $event)
    {
        $this->state = static::STATE_PUBLISHED;
    }

    private function applyPostTitleWasChanged(PostTitleWasChanged $event)
    {
        $this->title = $event->getTitle();
    }

    private function applyPostContentWasChanged(PostContentWasChanged $event)
    {
        $this->content = $event->getContent();
    }

    private function applyCommentWasAdded(CommentWasAdded $event)
    {
        $this->comments[] = Comment::create($event->getCommentId(), $event->getComment());
    }
}