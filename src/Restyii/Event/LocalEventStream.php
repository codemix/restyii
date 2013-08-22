<?php

namespace Restyii\Event;

/**
 * # Local Event Stream
 *
 * Publishes messages to callback functions
 *
 * @package Restyii\Event
 */
class LocalEventStream extends AbstractEventStream
{
    /**
     * @var callable[] the callbacks to push events to
     */
    public $callbacks = array();

    /**
     * Publishes an event to the stream
     *
     * @param Event $event the event to publish
     *
     * @return boolean true if the event was published, otherwise false
     */
    public function publish(Event $event)
    {
        foreach($this->callbacks as $callback)
            $callback($event);

        return true;
    }

}
