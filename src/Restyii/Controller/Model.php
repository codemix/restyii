<?php

namespace Restyii\Controller;


class Model extends Base
{
    /**
     * @var string the name of the resource model that this controller is for
     */
    protected $_modelClass;

    /**
     * Sets the name of the model class to use for this controller.
     * @param string $modelClass
     */
    public function setModelClass($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    /**
     * Gets the name of the model class to use for this controller.
     * @return string
     */
    public function getModelClass()
    {
        if ($this->_modelClass === null)
            $this->_modelClass = substr(get_class($this),0, -10);
        return $this->_modelClass;
    }


    /**
     * @inheritDoc
     */
    public function actions()
    {
        return array(
            'create' => array(
                'class' => 'Restyii\Action\Collection\Create',
            ),
            'read' => array(
                'class' => 'Restyii\Action\Item\Read',
            ),
            'update' => array(
                'class' => 'Restyii\Action\Item\Update',
            ),
            'delete' => array(
                'class' => 'Restyii\Action\Item\Delete',
            ),
            'search' => array(
                'class' => 'Restyii\Action\Collection\Search',
            ),
            'head' => array(
                'class' => 'Restyii\Action\Generic\Head',
            ),
            'options' => array(
                'class' => 'Restyii\Action\Generic\Options',
            ),
            'replace' => array(
                'class' => 'Restyii\Action\Item\Replace',
            ),
            'copy' => array(
                'class' => 'Restyii\Action\Item\Copy',
            ),

            // the following actions operate on relations

            'createRelated' => array(
                'class' => 'Restyii\Action\Relation\Create',
            ),
            'readRelated' => array(
                'class' => 'Restyii\Action\Relation\Read',
            ),
            'searchRelated' => array(
                'class' => 'Restyii\Action\Relation\Search',
            ),
        );
    }


}
