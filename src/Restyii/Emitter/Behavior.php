<?php

namespace Restyii\Emitter;


use CComponent;
use CEvent;
use CModelEvent;
use Restyii\Model\ActiveRecord;

/**
 * # Emitter Behavior
 *
 * Allows resources to broadcast their change events via message queues
 *
 * @package Restyii\Emitter
 */
class Behavior extends \CActiveRecordBehavior
{
    /**
     * @var EventStreamInterface the emitter channel
     */
    protected $_eventStream;

    /**
     * @var array the old attributes for the model
     */
    protected $_oldAttributes = array();

    /**
     * Sets the old attributes for the model
     *
     * @param array $oldAttributes the old attributes, name => value
     */
    public function setOldAttributes($oldAttributes)
    {
        $this->_oldAttributes = $oldAttributes;
    }

    /**
     * Gets the old attributes for the model
     *
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }


    /**
     * Sets the event stream for the emitter
     * @param \Restyii\Emitter\AbstractEventStream $channel
     */
    public function setEventStream($channel)
    {
        $this->_eventStream = $channel;
    }

    /**
     * Gets the event stream for the emitter
     *
     * @throws \CException if no stream is available
     * @return \Restyii\Emitter\EventStreamInterface the event stream
     */
    public function getEventStream()
    {
        if ($this->_eventStream === null) {
            $app = \Yii::app();
            if (!$app->hasComponent('eventStream'))
                throw new \CException(__CLASS__." expects an 'eventStream' app component!");
            $this->_eventStream = $app->getComponent('eventStream');
        }

        return $this->_eventStream;
    }

    /**
     * Stores the old attributes so that we can later
     * determine which attributes have changed.
     *
     * @param CEvent $event the event being raised
     */
    public function afterFind($event)
    {
        $this->_oldAttributes = $event->sender->getAttributes();
    }

    /**
     * Emits an event to the stream after the model is saved
     *
     * @param CModelEvent $event the event being raised
     */
    public function afterSave($event)
    {
        $diff = $this->diff();

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
     * Returns the difference between the old attributes and the new attributes
     * of the owner model.
     * @return array a collection of attributes, name => array(oldValue, newValue)
     */
    public function diff()
    {
        $owner = $this->getOwner(); /* @var ActiveRecord $owner */
        $oldAttributes = $this->getOldAttributes();
        $diff = array();
        foreach($owner->getAttributes() as $attribute => $value)
            if (!array_key_exists($attribute, $oldAttributes))
                $diff[$attribute] = array(null, $value);
            else if ($oldAttributes[$attribute] != $value)
                $diff[$attribute] = array($oldAttributes[$attribute], $value);
        return $diff;
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
