<?php


namespace Restyii\Client\Schema;

/**
 * # Parameter
 *
 * Represents an parameter that an action accepts
 *
 * @package Restyii\Client\Schema
 */
class Parameter extends \CComponent
{
    /**
     * @var string the parameter name
     */
    public $name;

    /**
     * @var string the action label
     */
    public $label;

    /**
     * @var string the parameter description
     */
    public $description;

    /**
     * @var string the parameter type
     */
    public $type;

    /**
     * @var bool whether or not the parameter is required
     */
    public $isRequired = false;
}
