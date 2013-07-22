<?php
namespace Restyii\Client;

/**
 * This is the model component for the Restyii client.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license MIT
 * @package Restyii\Client
 */
class Model extends \CModel
{
    /**
     * @var array model attributes
     */
    protected $_attributes = array();

    /**
     * @var ApiComponent the API that this model belongs to
     */
    protected $_api;

    /**
     * @var bool wether this object is new
     */
    protected $_new = true;

    /**
     * @var string the name of this model type
     */
    protected $_type;

    /**
     * @var list of attributes indexed by model type
     */
    protected static $_attributeNames = array();

    /**
     * @param ApiComponent the API that this model belongs to
     * @param string $type name of the model type
     */
    public function __construct($api,$type)
    {
        if(!isset(self::$_attributeNames[$type])) {
            $description = $api->getDescription();
            if(!$description->hasModel($type)) {
                throw new CException("Invalid model type '$type'");
            }
            $parameters = $description->getModel($type)->toArray();
            self::$_attributeNames[$type] = array_keys($parameters['properties']);
        }

        $this->_api = $api;
        $this->_type = $type;
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that model attributes can be accessed like properties.
     * @param string $name property name
     * @return mixed property value
     */
    public function __get($name)
    {
        if(array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name,$value)
    {
        if(in_array($name, $this->attributeNames()))
        {
            $this->$name = $value;
        } else {
            parent::__set($name,$value);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking
     * if the named attribute is null or not.
     * @param string $name the property name or the event name
     * @return boolean whether the property value is null
     */
    public function __isset($name)
    {
        if(isset($this->_attributes[$name]))
            return true;
        return parent::__isset($name);
    }

    /**
     * Sets a component property to be null.
     * This method overrides the parent implementation by clearing
     * the specified attribute value.
     * @param string $name the property name or the event name
     */
    public function __unset($name)
    {
        if(array_key_exists($name, $this->_attributes)) {
            unset($this->_attributes[$name]);
        } else {
            parent::__unset($name);
        }
    }

    /**
     * TODO
     * @return array attribute names that are considered safe
     */
    public function getSafeAttributeNames()
    {
        return $this->attributeNames();
    }

    /**
     * @return array list of attribute names
     */
    public function attributeNames()
    {
        return self::$_attributeNames[$this->_type];
    }

    /**
     * @return string the type of this model
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * @return boolean whether the record is new
     */
    public function getIsNewRecord()
    {
        return $this->_new;
    }

    /**
     * @param boolean $value whether the record is new
     */
    public function setIsNewRecord($value)
    {
        $this->_new=$value;
    }

    /**
     * Create a new model instance from a Guzzle response model
     *
     * @param ApiComponent $api
     * @param string $type of the model
     * @param \Guzzle\Service\Resource\Model $response
     * @return Model the model instance
     */
    public static function fromResponse($api, $type, \Guzzle\Service\Resource\Model $response)
    {
        $model = new self($api, $type);
        $model->setAttributes($response->toArray());
        return $model;
    }
}
