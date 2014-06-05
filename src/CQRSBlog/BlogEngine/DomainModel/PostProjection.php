<?php

namespace CQRSBlog\BlogEngine\DomainModel;

use CQRSBlog\Common\DomainModel\Projection;

interface PostProjection extends Projection
{
    /**
     * Projects a posts creation event
     *
     * @param PostWasCreated $event
     *
     * @return void
     */
    public function projectPostWasCreated(PostWasCreated $event);

    /**
     * Projects when a post was published
     *
     * @param PostWasPublished $event
     *
     * @return void
     */
    public function projectPostWasPublished(PostWasPublished $event);

    /**
     * Projects when a post title was changed
     *
     * @param PostTitleWasChanged $event
     *
     * @return void
     */
    public function projectPostTitleWasChanged(PostTitleWasChanged $event);

    /**
     * Projects when a post content was changed
     *
     * @param PostContentWasChanged $event
     *
     * @return void
     */
    public function projectPostContentWasChanged(PostContentWasChanged $event);

    /**
     * Projects when a comment is added
     *
     * @param CommentWasAdded $event
     *
     * @return void
     */
    public function projectCommentWasAdded(CommentWasAdded $event);
}