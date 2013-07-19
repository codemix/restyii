<?php

namespace Restyii\Emitter;

/**
 * Interface for event streams
 * @package Restyii\Emitter
 */
interface EventStreamInterface
{
    /**
     * Publishes an event to the stream
     *
     * @param Event $event the event to publish
     *
     * @return boolean true if the event was published, otherwise false
     */
    public function publish(Event $event);
}
