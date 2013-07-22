<?php
namespace Restyii\Client;

/**
 * This is the data provider component for the Restyii client.
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @license MIT
 * @package Restyii\Client
 */
class DataProvider extends \CDataProvider
{
    /**
     * @var string|null name of primary key attribute. Default is 'id'.
     */
    public $keyAttribute = 'id';

    /**
     * @var ApiComponent
     */
    protected $_api;

    /**
     * @var \Guzzle\Service\Command\CommandInterface
     */
    protected $_command;

    /**
     * @var string name of item type
     */
    protected $_type;

    /**
     * @var int total number of results
     */
    protected $_total = 0;

    /**
     * @param ApiComponent the API that this model belongs to
     * @param \Guzzle\Service\Command\CommandInterface $command the API command
     * @param array $config optional configuration for this data provider
     */
    public function __construct($api, \Guzzle\Service\Command\CommandInterface $command, $config=array())
    {
        $this->_api = $api;
        $this->_command = $command;
        $this->_type = substr($command->getOperation()->getResponseClass(),0,-strlen($api->collectionSuffix));
        foreach($config as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Fetch data from the API
     *
     * @return array list of data items
     */
    protected function fetchData()
    {
        if(($pagination=$this->getPagination())!==false)
        {
            if(!isset($this->_command['page'])) {
                $this->_command['page'] = $pagination->currentPage;
            }
            if(!isset($this->_command['pagesize'])) {
                $this->_command['pagesize'] = $pagination->pageSize;
            }
        }

        $response       = $this->_command->execute()->toArray();
        if(!isset($response['_embedded'], $response['total'])) {
            throw new \CException('Invalid API response: Not HAL compliant.');
        }

        $items          = array_shift($response['_embedded']);
        $this->_total   = $response['total'];

        $data =  array();
        foreach($items as $item) {
            $model = new Model($this->_api, $this->_type);
            $model->setAttributes($item);
            $data[] = $model;
        }
        return $data;
    }

    /**
     * Fetches the data item keys from the persistent data storage.
     *
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        $keys = array();
        foreach($this->getData() as $i => $item) {
            $keys[$i] = $item->{$this->keyAttribute};
        }
        return $keys;
    }

    /**
     * @return integer the total number of data items
     */
    public function calculateTotalItemCount()
    {
        // Trigger fetchData()
        $this->getData();
        return $this->_total;
    }
}
