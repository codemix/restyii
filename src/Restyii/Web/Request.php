<?php

namespace Restyii\Web;

/**
 * Extend the default Yii CHttpRequest to allow creating mock requests from string URLs
 * @package Restyii\Web
 */
class Request extends \CHttpRequest
{
    /**
     * @var string the name of the default input format.
     */
    public $defaultInputFormat = 'form';

    /**
     * @var string the name of the default output format.
     */
    public $defaultOutputFormat = 'html';

    /**
     * @var string the requested file name
     */
    protected $_fileName;

    /**
     * @var string the file extension for the requested file
     */
    protected $_fileExtension;

    /**
     * @var string the request content type
     */
    protected $_contentType;

    /**
     * @var array|bool the user input, or false if none is present
     */
    protected $_userInput;

    /**
     * @var \Restyii\MimeType\Base the format for the request
     */
    protected $_mimeType;

    /**
     * @var \Restyii\MimeType\Base[] the available formatters for the request
     */
    protected $_availableMimeTypes;

    /**
     * @var Response the response for the request
     */
    protected $_response;

    /**
     * @var \Restyii\Action\Base the action being requested
     */
    protected $_action;

    protected $_requestUri;
    protected $_hostInfo;
    protected $_requestType;

    /**
     * Sets the action that is handling the request
     * @param \Restyii\Action\Base $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * Gets the action that is handling the request.
     * Defaults to the current app controller action.
     * @return \Restyii\Action\Base the action
     */
    public function getAction()
    {
        if ($this->_action === null)
            return \Yii::app()->getController()->getAction();
        return $this->_action;
    }

    /**
     * @param \Restyii\MimeType\Base $format
     */
    public function setMimeType($format)
    {
        $this->_mimeType = $format;
    }

    /**
     * @return \Restyii\MimeType\Base
     */
    public function getMimeType()
    {
        if ($this->_mimeType === null) {
            $available = $this->getAvailableMimeTypes();
            foreach($available as $format)
                if ($format->canParse($this)) {
                    $this->_mimeType = $format;
                    return $this->_mimeType;
                }

            $this->_mimeType = $available[$this->defaultInputFormat];
        }
        return $this->_mimeType;
    }


    /**
     * Sets the supported formats
     * @param \Restyii\MimeType\Base[] $formats
     */
    public function setAvailableMimeTypes($formats)
    {
        foreach($formats as $name => $config) {
            if ($config instanceof \Restyii\MimeType\Base)
                continue;
            if (is_string($config))
                $config = array('class' => $config);
            $formats[$name] = \Yii::createComponent($config);
        }

        $this->_availableMimeTypes = $formats;
    }

    /**
     * Gets the supported request / response formats
     * @return \Restyii\MimeType\Base[] the supported request / response formats
     */
    public function getAvailableMimeTypes()
    {
        if ($this->_availableMimeTypes === null)
            $this->_availableMimeTypes = array(
                'json' => new \Restyii\MimeType\JSON(),
                'jsonp' => new \Restyii\MimeType\JSONP(),
                'form' => new \Restyii\MimeType\Form(),
                'csv' => new \Restyii\MimeType\CSV(),
                'tsv' => new \Restyii\MimeType\TSV(),
                'markdown' => new \Restyii\MimeType\Markdown(),
                'html' => new \Restyii\MimeType\HTML(),
                'xml' => new \Restyii\MimeType\XML(),
            );
        return $this->_availableMimeTypes;
    }

    /**
     * Sets the response for the request
     * @param \Restyii\Web\Response $response the response object
     */
    public function setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * Gets the response object for this request
     * @return \Restyii\Web\Response the response object
     */
    public function getResponse()
    {
        if ($this->_response === null)
            $this->_response = $this->createResponse();
        return $this->_response;
    }


    /**
     * Creates a response for the request.
     * @return Response the response object
     */
    protected function createResponse()
    {
        $response = new Response();
        $response->setRequest($this);
        return $response;
    }

    /**
     * Responds to this request
     *
     * @param int $code the http code for the response
     * @param array|null $data the data to send
     * @param array|null $headers the additional headers to send
     * @param bool $terminateApplication whether or not to terminate the application after responding
     */
    public function respond($code = 200, $data = null, $headers = null, $terminateApplication = true)
    {
        $response = $this->getResponse();
        $response->code = $code;
        $response->data = $data;
        if ($headers !== null)
            $response->headers = $headers;
        $response->send();
    }

    /**
     * @inheritDoc
     */
    public function getRequestUri()
    {
        if($this->_requestUri===null)
            return parent::getRequestUri();
        else
            return $this->_requestUri;
    }

    /**
     * Sets the request URI
     * @param mixed $url
     */
    public function setRequestUri($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['host'])) {
            if (!empty($parsed['scheme']))
                $hostInfo = $parsed['scheme'].'://';
            else
                $hostInfo = $this->getIsSecureConnection() ? 'https://' : 'http://';
            $hostInfo .= $parsed['host'];
            if (isset($parsed['port']))
                $hostInfo .= ':'.$parsed['port'];
            $this->setHostInfo($hostInfo);
        }
        $this->_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i','',$url);
    }


    /**
     * Sets the request URL
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->setRequestUri($url);
    }

    /**
     * Gets the requested filename, including extension, from the path info
     * @return string the file name for the request
     */
    public function getFileName()
    {
        if ($this->_fileName === null) {
            $parts = explode("/", $this->getPathInfo());
            $this->_fileName = array_pop($parts);
        }
        return $this->_fileName;
    }

    /**
     * Sets the requested filename, including extension
     * @param string $fileName the filename for the request
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }


    /**
     * Returns the file extension from the path info.
     * @return string the file extension with dot removed.
     */
    public function getFileExtension()
    {
        if ($this->_fileExtension === null) {
            $pathInfo = $this->getPathInfo();
            if (preg_match("/(\.([A-Za-z0-9_\-\$]*))$/", $pathInfo, $matches))
                $this->_fileExtension = $matches[2];
            else
                $this->_fileExtension = '';
        }
        return $this->_fileExtension;
    }

    /**
     * Sets the file extension for the request
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
    }

    /**
     * @inheritDoc
     */
    public function getRequestType()
    {
        if ($this->_requestType === null)
            return parent::getRequestType();
        else
            return $this->_requestType;
    }

    /**
     * Sets the request type
     * @param mixed $requestType
     */
    public function setRequestType($requestType)
    {
        $this->_requestType = $requestType;
    }

    /**
     * Sets the content type for the request
     * @param string $contentType the content type
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
    }

    /**
     * Gets the content type for the request
     * @return string the content type
     */
    public function getContentType()
    {
        if ($this->_contentType === null)
            $this->_contentType = !empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'application/x-www-form-urlencoded';
        return $this->_contentType;
    }

    /**
     * Gets the user input, if any, or returns false if there is none.
     * @return array|bool the user input, or false if none is present
     */
    public function getUserInput()
    {
        if ($this->_userInput === null) {
            $format = $this->getMimeType();
            $this->_userInput = $format->parse($this);
        }
        return $this->_userInput;
    }
}
