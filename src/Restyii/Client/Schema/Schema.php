<?php

namespace Restyii\Client\Schema;

use Guzzle\Http\Exception\HttpException;
use Restyii\Client\Connection;

class Schema extends \CAttributeCollection
{

    /**
     * @var int the caching duration for the schema
     */
    public $cachingDuration = 0;

    /**
     * @var string the id of the cache component to use
     */
    public $cacheId = 'cache';

    /**
     * @var string
     */
    public $cacheKeyPrefix = '';

    /**
     * @var bool whether or not schema names are case insensitive
     */
    public $caseSensitive = true;

    /**
     * @var Connection the connection this schema is for
     */
    protected $_connection;

    /**
     * @var array the names of resource schemas that we have attempted (successfully or unsuccessfully) to load from the api
     */
    protected $_lookedUpResourceNames = array();


    /**
     * Sets the connection the schema is for
     * @param Connection $connection the connection
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Gets the connection the schema is for
     * @throws \CException if there is no available connection
     * @return Connection the connection the schema is for
     */
    public function getConnection()
    {
        if ($this->_connection === null) {
            $app = \Yii::app();
            if (!$app->hasComponent('api'))
                throw new \CException(__CLASS__.' expects an \'api\' app component!');
            $this->_connection = $app->getComponent('api');
        }
        return $this->_connection;
    }

    /**
     * Overrides the parent to load resources from the api on demand.
     *
     * @inheritDoc
     */
    public function itemAt($key)
    {
        $item = parent::itemAt($key);
        if ($item === null && !in_array($key, $this->_lookedUpResourceNames)) {
            $resource = $this->lookupResource($key);
            if ($resource) {
                $this->add($key, $resource);
                return $resource;
            }
        }
        return $item;
    }


    /**
     * Looks up a resource and adds it to the list of looked up resources.
     * @param string $name the name of the resource to load
     *
     * @return bool|Resource the resource instance, or false if it could not be found
     */
    protected function lookupResource($name)
    {
        $resource = $this->fetchResource($name);
        $this->_lookedUpResourceNames[] = $name;
        return $resource;
    }


    /**
     * Loads a resource schema with a given name
     * @param string $name the resource name
     * @param bool $refresh whether or not to ignore the cache and fetch the latest schema
     *
     * @return Resource|bool the resource instance, or false if it couldn't be loaded
     */
    protected function fetchResource($name, $refresh = false)
    {
        if (!$refresh && ($resource = $this->fetchResourceFromCache($name)) !== false) {
            return $resource;
        }
        try {
            $config = $this->getConnection()->options($name);
        } catch (HttpException $e) {
            return false;
        }


        // detect http errors caught by the resource fetch call
        if(is_array($config) && array_key_exists('type', $config) && $config['type'] === 'CHttpException') {
            throw new \CHttpException($config['code'], 'Error fetching resource \''.$name.'\'. '.$config['type'].": ".$config['message'], 1);
        }

        $resource  = new Resource($config);
        $this->saveResourceToCache($resource);
        return $resource;
    }

    /**
     * Attempts to load a resource schema from the cache
     *
     * @param string $name the resource name
     *
     * @return Resource|bool the resource instance, or false if it could not be found in the cache
     */
    protected function fetchResourceFromCache($name)
    {
        $cache = \Yii::app()->getComponent($this->cacheId); /* @var \CCache $cache */
        return $cache->get($this->createCacheKey($name));
    }

    /**
     * Saves the given resource to the cache
     *
     * @param \Restyii\Client\Schema\Resource $resource the resource instance
     *
     * @return bool true if the resource was cached successfully, otherwise false
     */
    protected function saveResourceToCache(Resource $resource)
    {
        $cache = \Yii::app()->getComponent($this->cacheId); /* @var \CCache $cache */
        return $cache->set($this->createCacheKey($resource->name), $resource, $this->cachingDuration);

    }

    /**
     * Creates a cache key for the given resource name
     * @param string $resourceName the resource name
     *
     * @return string the cache key
     */
    protected function createCacheKey($resourceName)
    {
        return $this->cacheKeyPrefix.strtolower(str_replace('\\', '.', get_class($this))).':'.$resourceName;
    }

}
