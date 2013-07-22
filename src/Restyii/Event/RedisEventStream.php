<?php

namespace Restyii\Event;

/**
 * # Redis Event Stream
 *
 * Publishes messages to redis channels.
 * Depends on the YiiRedis extension
 *
 * @package Restyii\Event
 */
class RedisEventStream extends AbstractEventStream
{
    /**
     * @var string the prefix to prepend to redis channel names
     */
    public $channelPrefix = '';

    /**
     * @var string the suffix to append to redis channel names
     */
    public $channelSuffix = '';

    /**
     * @var \ARedisChannel[] the redis channels for the event stream, type => channel
     */
    protected $_channels = array();

    /**
     * Sets the redis channels for the event stream
     * @param \ARedisChannel[] $channels
     */
    public function setChannels($channels)
    {
        $this->_channels = $channels;
    }

    /**
     * Gets the redis channels for the event stream
     * @return \ARedisChannel[]
     */
    public function getChannels()
    {
        return $this->_channels;
    }

    /**
     * Sets the redis channel
     * @param string $type the resource type for the channel
     * @param \ARedisChannel $channel the channel itself
     */
    public function setChannel($type, $channel)
    {
        $this->_channels[$type] = $channel;
    }

    /**
     * Gets the redis channel for a given resource type.
     * @param string $type the resource type, e.g. 'User'
     *
     * @return \ARedisChannel the redis channel
     */
    public function getChannel($type)
    {
        if (!isset($this->_channels[$type]))
            $this->_channels[$type] = $this->createChannel($type);
        return $this->_channels[$type];
    }

    /**
     * Creates a channel for the given resource type
     *
     * @param string $type the resource type
     *
     * @return \ARedisChannel the redis channel
     */
    protected function createChannel($type)
    {
        $name = $this->channelPrefix.$type.$this->channelSuffix;
        return new \ARedisChannel($name);
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
        $json = json_encode($event->toJSON());
        return $this->getChannel(get_class($event->sender))->publish($json);
    }



}
