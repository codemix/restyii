<?php

namespace Restyii\Event;

/**
 * # Pusher Event Stream,
 *
 *
 *
 * @package Restyii\Event
 */
class PusherEventStream extends \CApplicationComponent
{
    /**
     * @var string pusher api key
     */
    public $APIKey;

    /**
     * @var string pusher api secret key
     */
    public $appSecret;

    /**
     * @var string pusher api id
     */
    public $appId;

    /**
     * @var string pusher default channel
     */
    public $defaultChannel;

    /**
     * @var string prefix applied to events
     */
    public $eventPrefix;

    /**
     * @var Pusher pusher instance
     */
    protected $_pusher;


    /**
     * Initialises this application component
     */
    public function init()
    {
        parent::init();
        $this->_pusher = new \Pusher($this->APIKey, $this->appSecret, $this->appId);
    }

    /**
     * Publishes an event to the stream
     *
     * @param Event $event the event to publish
     *
     * @return bool|string true if the event was published, otherwise false
     */
    public function publish($event, $debug=false, $avoidJSONConversion=false)
    {
        if (!count($this->eventPrefix))
            $eventName = $event->name;
        else
            $eventName = $this->eventPrefix . "_" . $event['name'];

        $data = \CMap::mergeArray(
            array('event' => \CJSON::encode($event)),
            array('timestamp' => time())
        );

        return $this->_pusher->trigger($this->defaultChannel, $eventName, $data,null,$debug, $avoidJSONConversion);
    }
}
