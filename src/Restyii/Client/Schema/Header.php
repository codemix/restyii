<?php


namespace Restyii\Client\Schema;

/**
 * # Header
 *
 * Represents an header that an action accepts
 *
 * @package Restyii\Client\Schema
 */
class Header extends \CComponent
{
    /**
     * @var string the header name
     */
    public $name;

    /**
     * @var string the action label
     */
    public $label;

    /**
     * @var string the header description
     */
    public $description;

    /**
     * @var bool whether or not the header is required
     */
    public $isRequired = false;


}
