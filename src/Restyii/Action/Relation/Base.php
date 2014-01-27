<?php

namespace Restyii\Action\Relation;

use \Restyii\Model\ActiveRecord;
use \Restyii\Model\ModelInterface;

/**
 * Base class for actions that operate on relations.
 *
 * @package Restyii\Action\Relation
 */
abstract class Base extends \Restyii\Action\Item\Base
{

    /**
     * @var string the related resource model class
     */
    protected $_relatedModelClass;

    /**
     * @var ActiveRecord the owner model
     */
    protected $_ownerModel;

    /**
     * @param \Restyii\Model\ActiveRecord $ownerModel
     */
    public function setOwnerModel($ownerModel)
    {
        $this->_ownerModel = $ownerModel;
    }

    /**
     * @return \Restyii\Model\ActiveRecord
     */
    public function getOwnerModel()
    {
        if ($this->_ownerModel === null)
            $this->_ownerModel = $this->load();
        return $this->_ownerModel;
    }

    /**
     * @inheritDoc
     */
    public function params()
    {
        return \CMap::mergeArray(parent::params(), array(
            'relation' => array(
                'label' => \Yii::t('resource', 'Relation Name'),
                'description' => \Yii::t(
                    'resource',
                    'The name of the relation to the {className}',
                    array(
                        '{className}' => $this->staticModel()->classLabel()
                    )
                ),
                'required' => true,
                'type' => 'string',
            )
        ));
    }


    /**
     * Returns the appropriate related static model instance for this resource
     * @param string|null $className the class name
     *
     * @return ModelInterface|ActiveRecord the resource model
     */
    public function staticRelatedModel($className = null)
    {
        if ($className === null)
            $className = $this->getRelatedModelClass();
        return $className::model();
    }

    /**
     * @param string $relatedModelClass
     */
    public function setRelatedModelClass($relatedModelClass)
    {
        $this->_relatedModelClass = $relatedModelClass;
    }

    /**
     * Gets the related model class
     * @return string the related model class name
     */
    public function getRelatedModelClass()
    {
        if ($this->_relatedModelClass === null)
           $this->_relatedModelClass = $this->getRelation()->className;
        return $this->_relatedModelClass;
    }

    /**
     * Gets the active relation for the action.
     * @return \CActiveRelation|null the relation
     */
    public function getRelation()
    {
        $params = $this->getParams();
        $ownerModel = $this->staticModel();
        return $ownerModel->getActiveRelation($params['relation']);
    }

    /**
     * Instantiates a related resource model for this action.
     * @param string|null the scenario to use
     * @return ActiveRecord the instantiated model
     */
    public function instantiateRelatedModel($scenario = null)
    {
        $modelClass = $this->getRelatedModelClass();
        $model = new $modelClass($scenario === null? $this->getScenario() : $scenario);
        $this->applyRelationAttributes($model);
        return $model;
    }

    /**
     * Applies the appropriate relation attributes for HAS_MANY and HAS_ONE relations
     * @param ActiveRecord $model the related model to apply the attributes to
     */
    protected function applyRelationAttributes(ActiveRecord $model)
    {
        $owner = $this->getOwnerModel();
        $relation = $this->getRelation();
        if ($relation instanceof \CManyManyRelation) {
            return;
        }
        else if ($relation instanceof \CHasManyRelation) {
            $model->{$relation->foreignKey} = $owner->primaryKey;
        }
        else if ($relation instanceof \CHasOneRelation) {
            $model->{$relation->foreignKey} = $owner->primaryKey;
        }

    }


    /**
     * Loads the appropriate related resource for the current request.
     * @param mixed|null $pk the primary key to load, defaults to null meaning use the GET parameters.
     * @param ActiveRecord|null $finder the model to use when finding the resource
     * @return ActiveRecord the loaded resource model
     * @throws \CHttpException if the request doesn't contain the right parameters or the resource does not exist
     */
    public function loadRelated($pk = null, $finder = null)
    {
        if ($finder === null)
            $finder = $this->staticRelatedModel();
        if ($pk === null)
            $pk = $this->getRelatedPkFromContext($finder);
        if ($pk === null)
            throw new \CHttpException(401, 'Invalid Request, missing parameter(s).');
        $finder->getDbCriteria()->mergeWith($this->createRelationCriteria($this->getOwnerModel(), $finder));
        $model = $finder->findByPk($pk);
        if (!is_object($model))
            throw new \CHttpException(404, 'The specified resource cannot be found.');
        return $model;
    }

    /**
     * Performs a search
     *
     * @param ActiveRecord $finder
     * @param array $params
     *
     * @return \Restyii\Model\ActiveDataProvider
     */
    public function searchRelated(ActiveRecord $finder, $params = array())
    {
        $criteria = $this->getCriteria();
        $criteria->mergeWith($this->createEmbedCriteria($finder));
        $finder->getDbCriteria()->mergeWith($criteria);
        $dataProvider = $finder->search($params);
        if (($cache = $this->getCache()) !== false) {
            $cacheKey = $cache->createKey($dataProvider, $params);
            $fromCache = $cache->read($cacheKey);
            if ($fromCache)
                return $fromCache;
        }
        else
            return $dataProvider;

        $dataProvider->getData();
        $cache->write($cacheKey, $dataProvider);
        return $dataProvider;
    }

    /**
     * Gets the user input, if any, or returns false if there is none.
     * @return array|bool the user input, or false if none is present
     */
    protected function getUserInput()
    {
        $input = \Restyii\Action\Base::getUserInput();
        $model = $this->staticRelatedModel();
        $modelClass = get_class($model);
        if (isset($input[$modelClass]) && count($input[$modelClass]))
            $input = $input[$modelClass];

        return $input;
    }

    /**
     * @inheritDoc
     */
    public function getViewName()
    {
        if ($this->_viewName === null) {
            $controllerId = $this->staticRelatedModel()->controllerID();
            $id = $this->getId();
            if (substr($id, -7, 7) === 'Related')
                $this->_viewName = '//'.$controllerId.'/'.substr($id, 0, -7);
            else
                $this->_viewName = '//'.$controllerId.'/'.$id;
        }
        return $this->_viewName;
    }

    /**
     * Gets the related model primary key from the `$_GET` parameters.
     * Returns null if the required parameters do not exist.
     *
     * @param ActiveRecord $finder the model to get the pk values for
     * @param array $context the context to load from, defaults to $_GET
     *
     * @return mixed|null the pk, or null if the required params are not present
     */
    public function getRelatedPkFromContext(ActiveRecord $finder, $context = null)
    {
        if ($context === null)
            $context = $_GET;
        $pkName = $finder->getTableSchema()->primaryKey;
        if (is_array($pkName)) {
            $pk = array();
            foreach($pkName as $name) {
                $keyName = 'relation'.ucfirst($name);
                if (!isset($context[$keyName]))
                    return null;
                $pk[$name] = $context[$keyName];
            }
        }
        else {
            $keyName = 'relation'.ucfirst($pkName);
            if (!isset($context[$keyName]))
                return null;
            $pk = $context[$keyName];
        }
        return $pk;
    }

    /**
     * Creates the relation criteria
     *
     * @param ActiveRecord $owner the owner model
     * @param ActiveRecord $relatedModel the related model
     *
     * @return \CDbCriteria the criteria
     */
    public function createRelationCriteria(ActiveRecord $owner, ActiveRecord $relatedModel)
    {
        $criteria = new \CDbCriteria();
        $relation = $this->getRelation();
        $criteria->alias = empty($relation->alias) ? $relation->name : $relation->alias;
        if ($relation instanceof \CManyManyRelation) {
            $junctionTable = $relation->getJunctionTableName();
            $junctionKeys = $relation->getJunctionForeignKeys();
            $criteria->join = $relation->joinType.
                ' '.$junctionTable.
                ' ON '.$junctionTable.'.'.$junctionKeys[0].' = :relationOwnerModelId AND '.
                $junctionTable.'.'.$junctionKeys[1].' = '.$criteria->alias.'.'.$relatedModel->getTableSchema()->primaryKey.
                ' '.$relation->join
            ;
        }
        else if ($relation instanceof \CHasManyRelation) {
            $criteria->join = $relation->join;
            $criteria->condition = $criteria->alias.'.'.$relation->foreignKey.' = :relationOwnerModelId';
        }
        else
            $criteria->join = $relation->join;

        $criteria->select = $relation->select;
        if (empty($criteria->condition))
            $criteria->condition = $relation->condition;
        else if (!empty($relation->condition))
            $criteria->condition = '('.$criteria->condition.') AND ('.$relation->condition.')';

        $criteria->having = $relation->having;
        $criteria->with = $relation->with;
        $criteria->group = $relation->group;
        $criteria->order = $relation->order;
        $criteria->limit = $relation->limit;
        $criteria->params['relationOwnerModelId'] = $owner->primaryKey;
        return $criteria;
    }
}
