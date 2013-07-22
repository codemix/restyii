<?php
namespace Restyii\Client;

use \Yii as Yii;

/**
 * This is the application component for the Restyii client.
 *
 * @author Charles Pick <charles@codemix.com>
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license MIT
 * @package Restyii\Client
 */
class ApiComponent extends \CApplicationComponent
{
    /**
     * @var string the URL to the API's service description
     */
    public $serviceDescriptionUrl;

    /**
     * @var string|null ID of the cache component. Default is 'cache'.
     */
    public $cacheID = 'cache';

    /**
     * @var int how many seconds to cache the service description. Set to 0 to disable caching. Default is 1200.
     */
    public $cacheDuration = 1200;

    /**
     * @var string the suffix that identifies collection classes. If a responseClass ends on this suffix
     * a DataProvider will be created.
     */
    public $collectionSuffix = 'Collection';

    /**
     * @var \Guzzle\Service\Description\ServiceDescription the service description for the api
     */
    protected $_description;

    /**
     * @var \Guzzle\Service\Client
     */
    protected $_client;

    public function init()
    {
        if($this->serviceDescriptionUrl===null) {
            throw new CException('No URL for the service description configured');
        }
    }

    /**
     * @param \Guzzle\Service\Description\ServiceDescription $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return \Guzzle\Service\Description\ServiceDescription
     */
    public function getDescription()
    {
        if ($this->_description === null) {
            $description = $this->loadDescription();
            $this->_description = \Guzzle\Service\Description\ServiceDescription::factory($description);
        }
        return $this->_description;
    }

    /**
     * @param \Guzzle\Service\Client $client
     */
    public function setClient($client)
    {
        $this->_client = $client;
    }

    /**
     * @return \Guzzle\Service\Client
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = new \Guzzle\Service\Client;
            $this->_client->setDescription($this->getDescription());
        }
        return $this->_client;
    }

    /**
     * Gets the named command from the api service and executes it
     * PHP Magic Method, do not call directly.
     *
     * @param string $name the name of the command
     * @param array $arguments first element can be an array of parameters or a Model to pass to the API command,
     * the second element an array of configuration for the DataProvider if the result is a collection
     *
     * @return mixed the resulting data
     */
    public function __call($name, $arguments)
    {
        $parameters = array_shift($arguments);
        if($parameters instanceof Model) {
            $parameters = (array) $parameters;
        }
        if (empty($parameters)) {
            $parameters = array();
        }

        $command        = $this->getClient()->getCommand($name,$parameters);
        $responseClass  = $command->getOperation()->getResponseClass();

        $l = strlen($this->collectionSuffix);
        if($responseClass!==$this->collectionSuffix && substr($responseClass,-$l)===$this->collectionSuffix) {
            $config = array_shift($arguments);
            if(!$config) {
                $config = array();
            }
            return new DataProvider($this,$command, $config);
        } else {
            $response = $command->execute();

            if($response instanceof \Guzzle\Service\Resource\Model) {
                return Model::fromResponse($this, $responseClass, $response);
            } else {
                return $response;
            }
        }
    }

    /**
     * Creates a new API model
     *
     * @param string $name of model
     * @return Restyii\Model the new model object
     */
    public function createModel($name)
    {
        return new Model($this, $name);
    }

    /**
     * Loads the raw service description from cache or requests it from serviceDescriptionUrl
     * @return array service description
     */
    protected function loadDescription()
    {
        $cache      = $this->cacheDuration ? Yii::app()->getComponent($this->cacheID) : null;
        $cacheKey   = '__serviceDescription_'.md5($this->serviceDescriptionUrl);

        if($cache===null || ($data = $cache->get($cacheKey))===false) {
            $data = $this->requestDescription();
        }

        if($cache!==null) {
            $cache->set($cacheKey, $data);
        }
        return $data;
    }

    /**
     * Request service description from remote URL
     * @return array service description
     */
    protected function requestDescription()
    {
        $client = new \Guzzle\Http\Client();
        return $client->get($this->serviceDescriptionUrl)->send()->json();
    }
}
