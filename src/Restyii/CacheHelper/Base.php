<?php

namespace Restyii\CacheHelper;

use Restyii\Model\ActiveDataProvider;
use Restyii\Model\ActiveRecord;

class Base extends \CComponent
{
    /**
     * @var string the name of the application component to use for caching
     */
    public $cacheID = 'cache';

    /**
     * @var \Restyii\Action\Base the action this cache object is for
     */
    protected $_action;

    /**
     * @var string the key prefix to use
     */
    protected $_keyPrefix;

    /**
     * @var \CCacheDependency[] the cache dependencies
     */
    protected $_dependencies;

    /**
     * @var callable[] the methods that should be called to vary the cache key
     */
    protected $_variators;


    /**
     * @var int the number of seconds to cache for
     */
    protected $_ttl = 5;

    /**
     * @param \Restyii\Action\Base $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * @return \Restyii\Action\Base
     */
    public function getAction()
    {
        if ($this->_action === null)
            $this->_action = \Yii::app()->getController()->getAction();
        return $this->_action;
    }


    /**
     * @param \CCacheDependency[] $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->_dependencies = $dependencies;
    }

    /**
     * @return \CCacheDependency[]
     */
    public function getDependencies()
    {
        return $this->_dependencies;
    }

    /**
     * @param string $keyPrefix
     */
    public function setKeyPrefix($keyPrefix)
    {
        $this->_keyPrefix = $keyPrefix;
    }

    /**
     * @return string
     */
    public function getKeyPrefix()
    {
        return $this->_keyPrefix;
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->_ttl = $ttl;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->_ttl;
    }

    /**
     * @param \callable[] $variators
     */
    public function setVariators($variators)
    {
        $this->_variators = $variators;
    }

    /**
     * @return \callable[]
     */
    public function getVariators()
    {
        if ($this->_variators === null)
            $this->_variators = array();
        return $this->_variators;
    }

    /**
     * @return \CCacheDependency|\CChainedCacheDependency|null the combined cache dependency
     */
    public function getCombinedDependency()
    {
        $dependencies = $this->getDependencies();
        $dependencyCount = count($dependencies);
        if (!$dependencyCount)
            return null;
        if ($dependencyCount === 1)
            return $dependencies[0];
        else
            return new \CChainedCacheDependency($dependencies);
    }

    /**
     * Creates a cache key for the given item
     * @param mixed $input the item
     * @param array $qualifiers the extra qualifiers that differentiate the item
     *
     * @return string the cache key
     */
    public function createKey($input, $qualifiers = array())
    {
        if ($input instanceof ActiveRecord)
            return $this->createActiveRecordKey($input, $qualifiers);
        else if ($input instanceof ActiveDataProvider)
            return $this->createDataProviderKey($input, $qualifiers);
        else
            return $this->calculateKey($input, $qualifiers);
    }

    /**
     * Generates the cache key for an active record
     * @param ActiveRecord $input the data to generate the key for
     * @param array $qualifiers the qualifiers that differentiate the item
     *
     * @return string the cache key
     */
    protected function createActiveRecordKey(ActiveRecord $input, $qualifiers = array())
    {
        $prepend = $input->controllerID().':';
        $append = $input->getDbCriteria()->toArray();
        return $this->calculateKey($prepend, array($append, $qualifiers));
    }

    /**
     * Generates the cache key for an active data provider
     * @param ActiveDataProvider $input the data to generate the key for
     * @param array $qualifiers the qualifiers that differentiate the item
     *
     * @return string the cache key
     */
    protected function createDataProviderKey(ActiveDataProvider $input, $qualifiers = array())
    {
        $action = $this->getAction();
        $controller = $action->getController();
        $prepend = str_replace('/', '-', $controller->getUniqueId()).'-'.$action->getId().':';
        $append = $input->model->getDbCriteria()->toArray();
        return $this->calculateKey($prepend, array($append, $qualifiers));
    }

    /**
     * Calculates the key for a given action
     *
     * @param string $prepend the string to prepend to the key
     * @param mixed $variation the suffix to append to the key before it is hashed,
     *
     * @return string the generated key
     */
    protected function calculateKey($prepend = null, $variation = null)
    {
        $action = $this->getAction();
        $key = $this->getKeyPrefix();

        if (is_string($prepend) || is_int($prepend))
            $key .= $prepend;
        elseif ($prepend !== null)
            $key .= serialize($prepend);
        $variators = array();
        foreach($this->getVariators() as $variator) {
            $variators[] = $variator($action);
        }
        $hashParts = '#'.serialize($variators);

        if (is_string($variation) || is_int($variation))
            $hashParts .= $variation;
        elseif ($variation !== null)
            $hashParts .= serialize($variation);
        $key .= '#'.sha1(serialize($hashParts));
        return $key;
    }

    /**
     * Read an item from the cache
     * @param string $key the cache key to read
     *
     * @return mixed|bool the loaded item, or false if the item was not found
     */
    public function read($key)
    {
        return $this->getCache()->get($key);
    }

    /**
     * Write an item to the cache
     * @param string $key the cache key
     * @param string $value the item to cache
     *
     * @return bool true if the item was cached
     */
    public function write($key, $value)
    {
        $dependency = $this->getCombinedDependency();
        return $this->getCache()->set($key, $value, $this->getTtl(), $dependency);
    }

    /**
     * @return \CCache the cache
     */
    public function getCache()
    {
        return \Yii::app()->getComponent($this->cacheID);
    }
}
