<?php

namespace Restyii\Emitter;

/**
 * Base class for events that get sent on the message bus
 *
 * @package Restyii\Emitter
 */
class Event extends \CModelEvent
{
    /**
     * @var string the name of the event
     */
    public $name;

    /**
     * @return array a representation of the model that can be JSON encoded
     */
    public function toJSON()
    {
        return array(
            'name' => $this->name,
            'type' => get_class($this->sender),
            'id' => $this->sender->getPrimaryKey(),
            'params' => $this->params,
        );
    }
}
