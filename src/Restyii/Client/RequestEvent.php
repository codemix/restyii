<?php
namespace Restyii\Client;

/**
 * RequestEvent
 *
 * This event represents a request event. To cancel a request, you can set the
 * `$isValid` to `false`. In this case you can set `$response` to a custom response
 * value that should be returned instead of the original request response.
 */
class RequestEvent extends \CModelEvent
{
    /**
     * @var \Guzzle\Http\Message\RequestInterface
     */
    public $request;

    /**
     * @var mixed the response to be used if $isValid was set to false
     */
    public $response;
}
