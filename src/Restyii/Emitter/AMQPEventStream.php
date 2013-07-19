<?php

namespace Restyii\Emitter;

/**
 * Base class for event streams
 * @package Restyii\Emitter
 */
class AMQPEventStream extends \CApplicationComponent implements EventStreamInterface
{
    /**
     * @var string the id of the AMQP application component
     */
    public $componentId = 'mq';

    /**
     * @var string the prefix to prepend to routing keys
     */
    public $routingKeyPrefix = '';

    /**
     * @var string the suffix to append to routing keys
     */
    public $routingKeySuffix = '';

    /**
     * @var \IApplicationComponent the AMQP client
     */
    protected $_amqpClient;

    /**
     * @param \IApplicationComponent $amqpClient
     */
    public function setAmqpClient($amqpClient)
    {
        $this->_amqpClient = $amqpClient;
    }

    /**
     * Gets the AMQP client for the event stream.
     *
     * @throws \CException if the required app component does not exist
     * @return \IApplicationComponent the AMQP app component
     */
    public function getAmqpClient()
    {
        if ($this->_amqpClient === null) {
            $app = \Yii::app();
            if (!$app->hasComponent($this->componentId))
                throw new \CException(__CLASS__." expects a '".$this->componentId."' application component!");
            $this->_amqpClient = $app->getComponent($this->componentId);
        }
        return $this->_amqpClient;
    }



    /**
     * Publishes an event to the stream
     *
     * @param Event $event the event to publish
     *
     * @return boolean true if the event was published, otherwise false
     */
    public function publish(Event $event)
    {
        $this->getAmqpClient()->sendJSONMessage(json_encode($event->toJSON()), $this->createRoutingKey($event));
    }


    /**
     * Creates a routing key for the given event
     * @param Event $event the event
     *
     * @return string the routing key
     */
    public function createRoutingKey(Event $event)
    {
        return $this->routingKeyPrefix.get_class($event->sender).$this->routingKeySuffix;
    }


}
