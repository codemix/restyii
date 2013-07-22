<?php


namespace Restyii\Client\Schema;

/**
 * # Attribute
 *
 * Represents an attribute of a resource
 *
 * @package Restyii\Client\Schema
 */
class Attribute extends AbstractEntity
{
    /**
     * @var string the attribute name
     */
    public $name;

    /**
     * @var string the attribute label
     */
    public $label;

    /**
     * @var string the attribute description
     */
    public $description;

    /**
     * @var string the attribute type
     */
    public $type;

    /**
     * @var string the attribute primitive type
     */
    public $primitive;

    /**
     * @var string the attribute format
     */
    public $format;

    /**
     * @var mixed the default value for the attribute
     */
    public $defaultValue;

    /**
     * @var array the input configuration
     */
    public $input = array('textField');

    /**
     * @var bool whether or not the attribute is writable
     */
    public $isWritable = true;

    /**
     * @var bool whether or not the attribute is required
     */
    public $isRequired = false;

    /**
     * @var bool whether or not the attribute is part of the primary key
     */
    public $isPrimaryKey = false;

    /**
     * @var array the validators for the attribute
     */
    public $validators = array();

}
