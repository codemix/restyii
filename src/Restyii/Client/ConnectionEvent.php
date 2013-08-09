<?php
namespace Restyii\Client;

/**
 * ConnectionEvent
 */
class ConnectionEvent extends \CEvent
{
    /**
     * @var \Guzzle\Http\Message\RequestInterface
     */
    public $request;
}
