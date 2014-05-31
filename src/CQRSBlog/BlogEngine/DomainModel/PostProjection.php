<?php

namespace CQRSBlog\BlogEngine\DomainModel;

interface PostProjection extends Projection
{
    /**
     * Projects a posts creation event
     *
     * @param PostWasCreated $event
     *
     * @return void
     */
    public function handlePostWasCreated(PostWasCreated $event);

    /**
     * Projects when a post was published
     *
     * @param PostWasPublished $event
     *
     * @return void
     */
    public function handlePostWasPublished(PostWasPublished $event);

    /**
     * Projects when a post title was changed
     *
     * @param PostTitleWasChanged $event
     *
     * @return void
     */
    public function handlePostTitleWasChanged(PostTitleWasChanged $event);

    /**
     * Projects when a post content was changed
     *
     * @param PostContentWasChanged $event
     *
     * @return void
     */
    public function handlePostContentWasChanged(PostContentWasChanged $event);
}