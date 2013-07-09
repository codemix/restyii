<?php
namespace Restyii\Web;

use \Restyii\Model\ModelInterface;

/**
 * Represents a response to a request
 */
class Response extends \CComponent
{

    /**
     * @var int the http response code
     */
    public $code;

    /**
     * @var mixed the http response data
     */
    public $data;

    /**
     * @var array the http response headers
     */
    public $headers = array();


    /**
     * @var bool whether or not to pretty print the output, false by default as it is usually slower.
     */
    public $prettyPrint = false;

    /**
     * @var \Restyii\MimeType\Base the format for the response.
     */
    protected $_format;

    /**
     * @var Request the request that spawned this response
     */
    protected $_request;

    /**
     * @var string the name of the view to use when rendering HTML responses
     */
    protected $_viewName;



    /**
     * Sets the request that spawned this response
     * @param \Restyii\Web\Request $request the request object
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    /**
     * Gets the request that spawned this response
     * @return \Restyii\Web\Request the request object
     */
    public function getRequest()
    {
        if ($this->_request === null)
            return \Yii::app()->getRequest();
        return $this->_request;
    }

    /**
     * @param string $viewName
     */
    public function setViewName($viewName)
    {
        $this->_viewName = $viewName;
    }

    /**
     * @return string
     */
    public function getViewName()
    {
        if ($this->_viewName === null)
            $this->_viewName = $this->getRequest()->getAction()->getViewName();
        return $this->_viewName;
    }

    /**
     * Sets the format for the response
     * @param \Restyii\MimeType\Base $format the response format
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }

    /**
     * Gets the format for the response
     * @return \Restyii\MimeType\Base the response format
     */
    public function getFormat()
    {
        if ($this->_format === null) {
            $request = $this->getRequest();
            foreach($request->getAvailableMimeTypes() as $formatter) {
                if ($formatter->canFormat($this)) {
                    $this->_format = $formatter;
                    break;
                }
            }
            if ($this->_format === null)
                $this->_format = $this->getDefaultFormat();

        }
        return $this->_format;
    }

    /**
     * @return \Restyii\MimeType\Base the default format
     */
    public function getDefaultFormat()
    {
        $request = $this->getRequest();
        $formats = $request->getAvailableMimeTypes();
        return $formats[$request->defaultOutputFormat];
    }

    /**
     * Sends the response
     * @param bool $terminateApplication whether or not to terminate the application after responding
     */
    public function send($terminateApplication = true)
    {
        $formatter = $this->getFormat();
        if ($this->data === null)
            $formatted = null;
        else
            $formatted = $formatter->format($this);
        $headers = $this->headers;
        $sentStatus = false;
        foreach($headers as $name => $value) {
            if (strcasecmp($name, 'content-type') === 0) {
                header($name.': '.$value, true, $this->code);
                $sentStatus = true;
            }
            else if (is_array($value)) {
                foreach($value as $item)
                    header($name.': '.$item, false);
            }
            else
                header($name.': '.$value);
        }
        if (!$sentStatus) {
            $contentType = isset($formatter->mimeTypes[0]) ? $formatter->mimeTypes[0] : 'text/plain';
            header('Content-type: '.$contentType, true, $this->code);
        }
        if ($formatted !== null)
            echo $formatted;
        if ($terminateApplication)
            \Yii::app()->end();
    }




}
