<?php

namespace Restyii\Event;


use CComponent;
use CEvent;
use CModelEvent;
use Restyii\Model\ActiveRecord;

/**
 * # Event Behavior
 *
 * Allows resources to broadcast their change events via message queues
 *
 * @package Restyii\Event
 */
class EmitterBehavior extends \CActiveRecordBehavior
{
    /**
     * @var string the name of the event stream application component id
     */
    public $eventStreamID = 'eventStream';

    /**
     * @var EventStreamInterface the emitter channel
     */
    protected $_eventStream;

    /**
     * Sets the event stream for the emitter
     * @param \Restyii\Event\AbstractEventStream $channel
     */
    public function setEventStream($channel)
    {
        $this->_eventStream = $channel;
    }

    /**
     * Gets the event stream for the emitter
     *
     * @throws \CException if no stream is available
     * @return \Restyii\Event\EventStreamInterface the event stream
     */
    public function getEventStream()
    {
        if ($this->_eventStream === null) {
            $app = \Yii::app();
            if (!$app->hasComponent($this->eventStreamID))
                throw new \CException(__CLASS__." expects an '".$this->eventStreamID."' app component!");
            $this->_eventStream = $app->getComponent($this->eventStreamID);
        }

        return $this->_eventStream;
    }

    /**
     * Emits an event to the stream after the model is saved
     *
     * @param CModelEvent $event the event being raised
     */
    public function afterSave($event)
    {
        $diff = $event->sender->diff();
        $this->emit($event->sender->scenario, $diff);
    }

    /**
     * Emits an event to the stream after the model is deleted
     *
     * @param CEvent $event the event being raised
     */
    public function afterDelete($event)
    {
        $this->emit("delete");
    }

    /**
     * Emit a given event
     * @param string $name the name of the event to emit
     * @param array $data the data for the event
     * @return boolean true if the event was successfully emitted
     */
    public function emit($name, $data = array())
    {
        $event = $this->createEvent($name, $data);
        return $this->getEventStream()->publish($event);
    }

    /**
     * Creates an event for the owner
     *
     * @param string $name the name of the event
     * @param array $data the event parameters
     *
     * @return Event the event
     */
    public function createEvent($name, $data = array())
    {
        $owner = $this->getOwner(); /* @var ActiveRecord $owner */
        $event = new Event();
        $event->sender = $owner;
        $event->name = $name;
        $event->params = $data;
        return $event;
    }

}
