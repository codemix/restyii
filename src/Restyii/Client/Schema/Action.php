<?php


namespace Restyii\Client\Schema;

/**
 * # Action
 *
 * Represents an action that can be performed on a resource
 *
 * @package Restyii\Client\Schema
 */
class Action extends AbstractEntity
{
    /**
     * @var string the action name
     */
    public $name;

    /**
     * @var string the action label
     */
    public $label;

    /**
     * @var string the action description
     */
    public $description;

    /**
     * @var string the action HTTP verb, e.g. 'GET' or 'PUT'
     */
    public $verb;

    /**
     * @var Header[] the action headers
     */
    protected $_headers;

    /**
     * @var Parameter[] the action parameters
     */
    protected $_params;

    /**
     * Sets the headers for the action
     * @param \Restyii\Client\Schema\Header[] $headers
     */
    public function setHeaders($headers)
    {
        foreach($headers as $name => $header) {
            if (!($header instanceof Header)) {
                $headers[$name] = new Header($header);
                $headers[$name]->name = $name;
            }
        }
        if (!($headers instanceof \CAttributeCollection)) {
            $c = new \CAttributeCollection();
            $c->caseSensitive = true;
            $c->copyFrom($headers);
            $headers = $c;
        }
        $this->_headers = $headers;
    }

    /**
     * Gets the headers for the action
     * @return \Restyii\Client\Schema\Header[]
     */
    public function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = new \CAttributeCollection();
            $this->_headers->caseSensitive = true;
        }
        return $this->_headers;
    }

    /**
     * Sets the parameters for the action
     * @param \Restyii\Client\Schema\Parameter[] $params
     */
    public function setParams($params)
    {
        foreach($params as $name => $param)
            if (!($param instanceof Parameter)) {
                $params[$name] = new Parameter($param);
                $params[$name]->name = $name;
            }
        if (!($params instanceof \CAttributeCollection)) {
            $c = new \CAttributeCollection();
            $c->caseSensitive = true;
            $c->copyFrom($params);
            $params = $c;
        }
        $this->_params = $params;
    }

    /**
     * Gets the parameters for the action
     * @return \Restyii\Client\Schema\Parameter[]
     */
    public function getParams()
    {
        if ($this->_params === null) {
            $this->_params = new \CAttributeCollection();
            $this->_params->caseSensitive = true;
        }
        return $this->_params;
    }

}
