<?php

namespace Restyii\Client\Resource;

/**
 * # Resource Data Provider
 *
 * Similar to `\CActiveDataProvider`, this takes a search model and returns a list of further models
 *
 * @package Restyii\Client\Resource
 */
class DataProvider extends \CDataProvider
{
    /**
     * @var string the primary Resource Model class name. The {@link getData()} method
     * will return a list of objects of this class.
     */
    public $modelClass;
    /**
     * @var Model the Resource Model finder instance (eg <code>Post::model()</code>).
     * This property can be set by passing the finder instance as the first parameter
     * to the constructor. For example, <code>Post::model()->published()</code>.
     */
    public $model;

    /**
     * @var Criteria the criteria
     */
    protected $_criteria;

    /**
     * @var array|null the last response from the API
     */
    protected $_lastResponse;

    /**
     * Constructor.
     * @param mixed $modelClass the model class (e.g. 'Post') or the model finder instance
     * (e.g. <code>Post::model()</code>, <code>Post::model()->published()</code>).
     * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
     */
    public function __construct($modelClass,$config=array())
    {
        if(is_string($modelClass))
        {
            $this->modelClass=$modelClass;
            $this->model=$this->createModel($this->modelClass);
        }
        elseif($modelClass instanceof Model)
        {
            $this->modelClass=get_class($modelClass);
            $this->model=$modelClass;
        }
        $this->setId(\CHtml::modelName($this->model));
        foreach($config as $key=>$value)
            $this->$key=$value;
    }

    /**
     * Returns the pagination object.
     * @param string $className the pagination object class name. Parameter is available since version 1.1.13.
     * @return Pagination|bool the pagination object. If this is false, it means the pagination is disabled.
     */
    public function getPagination($className='Restyii\\Client\\Resource\\Pagination')
    {
       return parent::getPagination($className);
    }

    /**
     * Given resource model class name returns new model instance.
     *
     * @param string $className active record class name.
     * @return Model resource model instance.
     *
     */
    protected function createModel($className)
    {
        return Model::model($className);
    }

    /**
     * @param \Restyii\Client\Resource\Criteria $criteria
     */
    public function setCriteria($criteria)
    {
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);
        $this->_criteria = $criteria;
    }

    /**
     * @return \Restyii\Client\Resource\Criteria
     */
    public function getCriteria()
    {
        if ($this->_criteria === null)
            $this->_criteria = new Criteria();
        return $this->_criteria;
    }

    /**
     * Fetches the data from the persistent data storage.
     * @return array list of data items
     */
    protected function fetchData()
    {
        $criteria=clone $this->getCriteria();

        if(($pagination=$this->getPagination())!==false)
        {
            $pagination->setItemCount(999999);
            $pagination->applyLimit($criteria);
        }

        $baseCriteria=$this->model->getCriteria(false);

        if(($sort=$this->getSort())!==false)
        {
            // set model criteria so that CSort can use its table alias setting
            if($baseCriteria!==null)
            {
                $c=clone $baseCriteria;
                $c->mergeWith($criteria);
                $this->model->setCriteria($c);
            }
            else
                $this->model->setCriteria($criteria);
            $sort->applyOrder($criteria);
        }

        $this->model->setCriteria($baseCriteria!==null ? clone $baseCriteria : null);
        $data=$this->model->findAll($criteria);
        $response = $this->model->getLastResponse();
        $this->_lastResponse = $response;
        if (isset($response['total']))
            $pagination->setItemCount($response['total']);
        else
            $pagination->setItemCount(0);
        $this->model->setCriteria($baseCriteria);  // restore original criteria
        return $data;
    }

    /**
     * Fetches the data item keys from the persistent data storage.
     * @return array list of data item keys.
     */
    protected function fetchKeys()
    {
        $keys=array();
        foreach($this->getData() as $i=>$data /* @var Model $data */)
        {
            $key=$this->keyAttribute===null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
            $keys[$i]=is_array($key) ? implode(',',$key) : $key;
        }
        return $keys;
    }

    /**
     * Calculates the total number of data items.
     * @return integer the total number of data items.
     */
    protected function calculateTotalItemCount()
    {
        if ($this->_lastResponse === null)
            $this->getData();
        if (isset($this->_lastResponse['total']))
            return $this->_lastResponse['total'];
        else
            return 0;
    }

}
