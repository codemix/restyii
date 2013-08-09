<?php


namespace Restyii\Client;


use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Restyii\Client\Schema\Schema;

class Connection extends \CApplicationComponent
{
    /**
     * @var array the default headers to send
     */
    public $defaultHeaders = array(
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded',
    );

    /**
     * @var Schema the schema object
     */
    protected $_schema;

    /**
     * @var string the base URL for the Restyii service
     */
    protected $_baseUrl;

    /**
     * @var Client the guzzle client
     */
    protected $_guzzle;

    /**
     * Sets the schema for the connection
     * @param Schema|array $schema
     */
    public function setSchema($schema)
    {
        if ($schema instanceof Schema)
            $schema->setConnection($this);
        else
            $schema = $this->createSchema($schema);
        $this->_schema = $schema;
    }

    /**
     * Gets the schema for the connection
     * @return Schema
     */
    public function getSchema()
    {
        if ($this->_schema === null)
            $this->_schema = $this->createSchema();
        return $this->_schema;
    }

    /**
     * Creates a schema object for the connection
     *
     * @param array $config the config for the schema
     *
     * @return Schema the schema
     */
    protected function createSchema($config = array())
    {
        $schema = new Schema();
        $schema->setConnection($this);
        foreach($config as $key => $value)
            $schema->{$key} = $value;
        return $schema;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        if (is_object($this->_guzzle))
            $this->_guzzle->setBaseUrl($baseUrl);
        $this->_baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }


    /**
     * Sets the guzzle client for this connection
     * @param Client $guzzle the guzzle client
     */
    public function setGuzzle($guzzle)
    {
        $this->_guzzle = $guzzle;
    }

    /**
     * Gets the guzzle client for this connection
     * @return Client the guzzle client
     */
    public function getGuzzle()
    {
        if ($this->_guzzle === null)
            $this->_guzzle = new Client($this->getBaseUrl(), array('redirect.disable' => true));
        return $this->_guzzle;
    }

    /**
     * This event is raised before a request is sent. The Guzzle request object is available
     * from the `request` property of the event object. To cancel the event you can set `isValid`
     * to `false` on the event object. In this case you can supply a custom response value in
     * the `response` property of the event object.
     *
     * @param RequestEvent
     */
    public function onBeforeRequest($event)
    {
        $this->raiseEvent('onBeforeRequest',$event);
    }

    /**
     * Performs a request
     *
     * @param string $verb the http verb, e.g. 'GET' or 'POST'
     * @param string|array $url the url to request, can be a relative url or a Yii URL-route-like array, where the
     * first element is the url and subsequent elements are get parameters, e.g. array('user', 'id' => 123)
     * @param array|null $data the data to send
     * @param array|null $headers the headers to send
     *
     * @throws \Exception|\Guzzle\Http\Exception\HttpException if the request is invalid
     * @return mixed the decoded response
     */
    public function request($verb, $url, $data = null, $headers = null)
    {
        if ($headers !== null)
            $headers = \CMap::mergeArray($this->defaultHeaders, $headers);
        else
            $headers = $this->defaultHeaders;

        $request = $this->createRequest($verb, $url, $data, $headers);

        if($this->hasEventHandler('onBeforeRequest')) {
            $event = new RequestEvent;
            $event->request = $request;
            $event->sender = $this;
            $this->onBeforeRequest($event);
            if(!$event->isValid)
                return $event->response;
        }

        try {
            $result = $request->send();
            $decoded = json_decode($result->getBody(true), true);
        }
        catch (ClientErrorResponseException $e) {
            $result = $e->getResponse()->getBody(true);
            $decoded = json_decode($result, true);
            if ($decoded === null)
                throw $e;
        }
        catch (BadResponseException $e) {
            $result = $e->getResponse()->getBody(true);
            \Yii::log('Restyii Error: '.$result, 'error', 'restyii.client.connection');
            throw $e;
        }
        return $decoded;
    }

    /**
     * Create a guzzle request but don't send it immediately
     * @param string $verb the http verb, e.g. 'GET' or 'POST'
     * @param array|string $url the url to request
     * @param array|null $data the request data
     * @param array|null $headers the headers to send
     *
     * @return \Guzzle\Http\Message\RequestInterface
     */
    public function createRequest($verb, $url, $data = null, $headers = null)
    {
        $url = $this->resolveUrl($url);
        $guzzle = $this->getGuzzle();
        return $guzzle->createRequest($verb, $url, $headers, $data);
    }


    /**
     * Takes a url in either string or array format and return a string url.
     * @param array|string $url the url
     *
     * @return string the resolved url
     */
    public function resolveUrl($url)
    {
        if (is_array($url)) {
            $params = $url;
            $url = array_shift($params);
            if (count($params))
                $url .= '?'.http_build_query($params);
        }
        if ($url[0] != '/')
            $url = '/'.$url;

        return $url;
    }

    /**
     * Performs a HEAD request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function head($url, $data = null, $headers = null)
    {
        return $this->request('HEAD', $url, $data, $headers);
    }

    /**
     * Performs a OPTIONS request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function options($url, $data = null, $headers = null)
    {
        return $this->request('OPTIONS', $url, $data, $headers);
    }


    /**
     * Performs a GET request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function get($url, $data = null, $headers = null)
    {
        return $this->request('GET', $url, $data, $headers);
    }

    /**
     * Performs a POST request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function post($url, $data = null, $headers = null)
    {
        return $this->request('POST', $url, $data, $headers);
    }

    /**
     * Performs a PATCH request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function patch($url, $data = null, $headers = null)
    {
        return $this->request('PATCH', $url, $data, $headers);
    }

    /**
     * Performs a PUT request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function put($url, $data = null, $headers = null)
    {
        return $this->request('PUT', $url, $data, $headers);
    }

    /**
     * Performs a LINK request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function link($url, $data = null, $headers = null)
    {
        return $this->request('LINK', $url, $data, $headers);
    }

    /**
     * Performs an UNLINK request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function unlink($url, $data = null, $headers = null)
    {
        return $this->request('UNLINK', $url, $data, $headers);
    }


    /**
     * Performs a COPY request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function copy($url, $data = null, $headers = null)
    {
        return $this->request('COPY', $url, $data, $headers);
    }

    /**
     * Performs a MOVE request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function move($url, $data = null, $headers = null)
    {
        return $this->request('MOVE', $url, $data, $headers);
    }

    /**
     * Performs a DELETE request
     * @param array|string $url the url to request
     * @param array|null $data the request body
     * @param array|null $headers the request headers
     *
     * @return mixed the request response
     */
    public function delete($url, $data = null, $headers = null)
    {
        return $this->request('DELETE', $url, $data, $headers);
    }
}
