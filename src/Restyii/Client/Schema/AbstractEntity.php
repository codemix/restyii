<?php

namespace Restyii\Client\Schema;

/**
 * # Entities
 *
 * Base class for schema entities
 *
 * @package Restyii\Client\Schema
 */
abstract class AbstractEntity extends \CComponent
{

    /**
     * Initialize the entity
     * @param array|null $config the entity configuration
     */
    public function __construct($config = null)
    {
        if ($config !== null)
            $this->configure($config);
    }

    /**
     * Configures the entity
     * @param array $config the entity config
     * @return $this the current object with the config applied
     */
    public function configure($config)
    {
        foreach($config as $key => $value)
            $this->{$key} = $value;
        return $this;
    }
}
