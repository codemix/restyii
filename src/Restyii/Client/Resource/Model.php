<?php


namespace Restyii\Client\Resource;


use Restyii\Client\Schema\Attribute;
use Restyii\Client\Schema\Link;

class Model extends \CModel
{
    /**
     * @var bool whether or not this is a new record
     */
    protected $_new = false;

    /**
     * @var array the model attributes, name => value
     */
    protected $_attributes = array();

    /**
     * @var Link[] the links to related records
     */
    protected $_links = array();

    /**
     * @var array the related objects, name => relation
     */
    protected $_linked = array();

    /**
     * @var Model[] the static model instances
     */
    protected static $_models = array();

    /**
     * @var mixed the old primary key
     */
    protected $_pk;

    /**
     * @var Criteria the query criteria to use
     */
    protected $_criteria;

    /**
     * @var array|null the last response from the api
     */
    protected $_lastResponse;

    /**
     * Returns the name to use for this particular resource instance.
     * The result of this function will be used as default anchor text when
     * creating links to resource instances.
     * @return string the name for this resource
     */
    public function instanceLabel()
    {
        return $this->getLink('self')->title;
    }

    /**
     * Returns the label for this resource type.
     *
     * @param bool $plural whether or not to pluralize the label, e.g. 'Users' instead of 'User'
     *
     * @return string the resource label
     */
    public function classLabel($plural = false)
    {
        if ($plural)
            return $this->getResourceSchema()->collectionLabel;
        else
            return $this->getResourceSchema()->label;
    }


    /**
     * Constructor.
     * @param string $scenario scenario name. See {@link CModel::scenario} for more details about this parameter.
     * Note: in order to setup initial model parameters use {@link init()} or {@link afterConstruct()}.
     * Do NOT override the constructor unless it is absolutely necessary!
     */
    public function __construct($scenario='insert')
    {
        if($scenario===null) // internally used by populateRecord() and model()
            return;

        $this->setScenario($scenario);
        $this->setIsNewRecord(true);
        $this->_attributes=$this->getResourceSchema()->getAttributeDefaults();

        $this->init();

        $this->attachBehaviors($this->behaviors());
        $this->afterConstruct();
    }

    /**
     * Initializes this model.
     * This method is invoked when an Resource Model instance is newly created and has
     * its {@link scenario} set.
     * You may override this method to provide code that is needed to initialize the model (e.g. setting
     * initial property values.)
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $rules = array();
        $required = array();
        $types = array();

        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /** @var Attribute $attribute */) {
            if ($attribute->isRequired) {
                $required[] = $name;
            }
            if ($attribute->isWritable) {
                if (!isset($types[$attribute->primitive]))
                    $types[$attribute->primitive] = array();
                $types[$attribute->primitive][] = $name;
            }
        }

        if (count($required))
            $rules[] = array($required, 'required');
        foreach($types as $type => $attributes) {
            $rules[] = array($attributes, 'type', 'type' => $type);
        }
        return $rules;
    }


    /**
     * @param Criteria $criteria
     */
    public function setCriteria($criteria)
    {
        if (!($criteria instanceof Criteria))
            $criteria = $this->createCriteria($criteria);
        $this->_criteria = $criteria;
    }

    /**
     * Gets the criteria for the model
     *
     * @param bool $createIfNull true if the criteria should be created
     *
     * @return Criteria
     */
    public function getCriteria($createIfNull = true)
    {
        if ($createIfNull && $this->_criteria === null)
            $this->_criteria = $this->createCriteria();
        return $this->_criteria;
    }

    /**
     * Creates a criteria object
     * @param array $config the criteria config
     *
     * @return Criteria the criteria instance
     */
    protected function createCriteria($config = array())
    {
        return new Criteria($config);
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that Resource Model attributes can be accessed like properties.
     * @param string $name property name
     * @return mixed property value
     * @see getAttribute
     */
    public function __get($name)
    {
        if(isset($this->_attributes[$name]))
            return $this->_attributes[$name];
        elseif($this->getResourceSchema()->hasAttribute($name))
            return null;
        elseif(isset($this->_linked[$name]))
            return $this->_linked[$name];
        elseif(isset($this->_links[$name]))
            return $this->getLinked($name);
        else
            return parent::__get($name);
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that Resource Model attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     * @return void
     */
    public function __set($name,$value)
    {
        if($this->setAttribute($name,$value)===false)
        {
            if(isset($this->_links[$name]))
                $this->_linked[$name]=$value;
            else
                parent::__set($name,$value);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking
     * if the named attribute is null or not.
     * @param string $name the property name or the event name
     * @return boolean whether the property value is null
     */
    public function __isset($name)
    {
        if(isset($this->_attributes[$name]))
            return true;
        elseif($this->getResourceSchema()->hasAttribute($name))
            return false;
        elseif(isset($this->_linked[$name]))
            return true;
        elseif(isset($this->_links[$name]))
            return $this->getLinked($name)!==null;
        else
            return parent::__isset($name);
    }

    /**
     * Sets a component property to be null.
     * This method overrides the parent implementation by clearing
     * the specified attribute value.
     * @param string $name the property name or the event name
     *
     * @return void
     */
    public function __unset($name)
    {
        if($this->getResourceSchema()->hasAttribute($name))
            unset($this->_attributes[$name]);
        elseif(isset($this->_links[$name]))
            unset($this->_linked[$name]);
        else
            parent::__unset($name);
    }

    /**
     * Calls the named method which is not a class method.
     * Do not call this method. This is a PHP magic method that we override
     * to implement the named scope feature.
     * @param string $name the method name
     * @param array $parameters method parameters
     * @return mixed the method return value
     */
    public function __call($name,$parameters)
    {
        if(isset($this->_links[$name]))
        {
            if(empty($parameters))
                return $this->getLinked($name,false);
            else
                return $this->getLinked($name,false,$parameters[0]);
        }

        $scopes=$this->scopes();
        if(isset($scopes[$name]))
        {
            $this->getCriteria()->mergeWith($scopes[$name]);
            return $this;
        }

        return parent::__call($name,$parameters);
    }

    /**
     * Returns the related resources(s).
     * This method will return the related record(s) of the current record.
     * If the relation is HAS_ONE or BELONGS_TO, it will return a single object
     * or null if the object does not exist.
     * If the relation is HAS_MANY or MANY_MANY, it will return an array of objects
     * or an empty array.
     * @param string $name the relation name (see {@link relations})
     * @param boolean $refresh whether to reload the related objects from database. Defaults to false.
     * If the current record is not a new record and it does not have the related objects loaded they
     * will be retrieved from the database even if this is set to false.
     * If the current record is a new record and this value is false, the related objects will not be
     * retrieved from the database.
     * @param mixed $params array or CDbCriteria object with additional parameters that customize the query conditions as specified in the relation declaration.
     * If this is supplied the related record(s) will be retrieved from the database regardless of the value or {@link $refresh}.
     * The related record(s) retrieved when this is supplied will only be returned by this method and will not be loaded into the current record's relation.
     * The value of the relation prior to running this method will still be available for the current record if this is supplied.
     * @return mixed the related object(s).
     * @throws \CDbException if the relation is not specified in {@link relations}.
     */
    public function getLinked($name,$refresh=false,$params=array())
    {
        if(!$refresh && $params===array() && (isset($this->_linked[$name]) || array_key_exists($name,$this->_linked)))
            return $this->_linked[$name];

        if(!isset($this->_links[$name]))
            throw new \CDbException(\Yii::t('yii','{class} does not have relation "{name}".',
                array('{class}'=>get_class($this), '{name}'=>$name)));

        \Yii::trace('lazy loading '.get_class($this).'.'.$name,'restyii.resource.model');

        if ($exists = isset($this->_linked[$name]))
            $save = $this->_linked[$name];
        else
            $save = null;

        unset($this->_linked[$name]);

        $this->fetchLinked($name, $params);

        if($params!==array())
        {
            $results=$this->_linked[$name];
            if($exists)
                $this->_linked[$name]=$save;
            else
                unset($this->_linked[$name]);
            return $results;
        }
        else
            return $this->_linked[$name];
    }


    /**
     * Fetch the data for the given link name
     *
     * @param string $name the name of the link
     * @param array $params the extra parameters
     */
    protected function fetchLinked($name, $params = array())
    {
        $link = $this->_links[$name];
        if ($params !== array()) {
            array_unshift($params, $link->href);
            $url = $params;
        }
        else
            $url = $link->href;

        $resource = $this->getApiConnection()->get($url);
        $this->addLinkedResource($link, $resource);
    }


    /**
     * Sets (all) the linked resources
     * @param array $resource the resource data
     */
    public function setLinked($resource)
    {
        foreach($resource['_embedded'] as $name => $data)
            if ($data !== null)
                $this->addLinkedResource($this->getLink($name), $data);

    }

    /**
     * Adds a linked resource instance or collection of instances.
     * You should not call this method directly.
     * @param Link $link the link instance
     * @param array $resource the resource data
     */
    protected function addLinkedResource(Link $link, $resource)
    {
        if (is_array($link->profile)) {
            // this is a list of resources
            foreach($link->profile as $className) {
                $model = self::model($className);
                $name = $model->resourceName();
                if (isset($resource['_embedded'][$name])) {
                    $this->_linked[$link->name] = $model->populateRecords($resource);
                }
            }
        }
        else {
            // this is a single resource
            $model = self::model($link->profile);
            $this->_linked[$link->name] = $model->populateRecord($resource);
        }
    }

    /**
     * Returns a value indicating whether the named related object(s) has been loaded.
     * @param string $name the relation name
     * @return boolean a value indicating whether the named related object(s) has been loaded.
     */
    public function hasLinkedResource($name)
    {
        return isset($this->_linked[$name]) || array_key_exists($name,$this->_linked);
    }

    /**
     * Sets the links for the resource model
     * @param Link[] $links
     */
    public function setLinks($links)
    {
        foreach($links as $name => $link) {
            if (!($link instanceof Link))
                $links[$name] = new Link($link);
            $links[$name]->name = $name;
        }

        $this->_links = $links;
    }

    /**
     * Gets the links for the resource model
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * Gets the link with the given name
     * @param string $name the link name
     *
     * @return Link|null the link instance, or null
     */
    public function getLink($name)
    {
        $links = $this->getLinks();
        return isset($links[$name]) ? $links[$name] : null;
    }

    /**
     * Returns the default named scope that should be implicitly applied to all queries for this model.
     * Note, default scope only applies to SELECT queries. It is ignored for INSERT, UPDATE and DELETE queries.
     * The default implementation simply returns an empty array. You may override this method
     * if the model needs to be queried with some default criteria (e.g. only active records should be returned).
     * @return array the query criteria. This will be used as the parameter to the constructor
     * of {@link CDbCriteria}.
     */
    public function defaultScope()
    {
        return array();
    }

    /**
     * Resets all scopes and criterias applied.
     *
     * @param boolean $resetDefault including default scope. This parameter available since 1.1.12
     * @return Model
     */
    public function resetScope($resetDefault=true)
    {
        if($resetDefault)
            $this->_criteria=new Criteria();
        else
            $this->_criteria=null;

        return $this;
    }

    /**
     * Returns the static model of the specified resource model class.
     * It is provided for invoking class-level methods (something similar to static class methods.)
     *
     * EVERY derived resource model class must override this method as follows,
     * <pre>
     * public static function model($className=__CLASS__)
     * {
     *     return parent::model($className);
     * }
     * </pre>
     *
     * @param string $className resource record class name.
     * @return Model active record model instance.
     */
    public static function model($className=__CLASS__)
    {
        if(isset(self::$_models[$className]))
            return self::$_models[$className];
        else
        {
            $model=self::$_models[$className]=new $className(null); /* @var Model $model */
            $model->attachBehaviors($model->behaviors());
            return $model;
        }
    }

    /**
     * Returns the primary key of the associated resource
     * This method is meant to be overridden in case when the resource is not defined with a primary key
     * (for some non Restyii server). If the resource is already defined with a primary key,
     * you do not need to override this method. The default implementation simply returns null,
     * meaning using the primary key defined in the resource.
     * @return mixed the primary key of the associated resource.
     * If the key is a single attribute, it should return the attribute name;
     * If the key is a composite one consisting of several attributes, it should
     * return the array of the key attribute names.
     */
    public function primaryKey()
    {
    }

    /**
     * Gets the connection to the api.
     * Child classes should override this if they need to specify a different connection
     *
     * @return \Restyii\Client\Connection
     */
    public function getApiConnection()
    {
        return \Yii::app()->getComponent('api');
    }

    /**
     * Gets the schema for this resource model
     * @return \Restyii\Client\Schema\Resource the resource schema
     */
    public function getResourceSchema()
    {
        return $this->getApiConnection()->getSchema()->itemAt($this->resourceName());
    }

    /**
     * Declares the name of the resource.
     * Defaults to lowerCamelCase version of the class name.
     * @return string the resource name
     */
    public function resourceName()
    {
        return lcfirst(get_class($this));
    }

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributeNames()
    {
        return $this->getResourceSchema()->getAttributes()->keys;
    }

    /**
     * Returns the declaration of named scopes.
     * A named scope represents a query criteria that can be chained together with
     * other named scopes and applied to a query. This method should be overridden
     * by child classes to declare named scopes for the particular Resource Model classes.
     * For example, the following code declares two named scopes: 'recently' and
     * 'published'.
     * <pre>
     * return array(
     *     'published'=>array(
     *           'filter'=>array('status' => true),
     *     ),
     *     'recently'=>array(
     *           'order'=>'create_time.DESC',
     *           'limit'=>5,
     *     ),
     * );
     * </pre>
     * If the above scopes are declared in a 'Post' model, we can perform the following
     * queries:
     * <pre>
     * $posts=Post::model()->published()->findAll();
     * $posts=Post::model()->published()->recently()->findAll();
     * $posts=Post::model()->published()->embed('comments')->findAll();
     * </pre>
     * Note that the last query is a relational query.
     *
     * @return array the scope definition. The array keys are scope names; the array
     * values are the corresponding scope definitions. Each scope definition is represented
     * as an array whose keys must be properties of {@link Criteria}.
     */
    public function scopes()
    {
        return array();
    }
    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        $labels = array();
        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /* @var \Restyii\Client\Schema\Attribute $attribute */)
            $labels[$name] = $attribute->label;
        return $labels;
    }

    /**
     * The attribute descriptions
     * @return array attribute => description
     */
    public function attributeDescriptions()
    {
        $descriptions = array();
        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /* @var \Restyii\Client\Schema\Attribute $attribute */)
            $descriptions[$name] = $attribute->description;
        return $descriptions;
    }

    /**
     * The attribute formats
     * @return array attribute => format
     */
    public function attributeFormat()
    {
        $descriptions = array();
        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /* @var \Restyii\Client\Schema\Attribute $attribute */)
            $descriptions[$name] = $attribute->format;
        return $descriptions;
    }

    /**
     * The attribute primitive types
     * @return array attribute => primitive type
     */
    public function attributePrimitive()
    {
        $descriptions = array();
        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /* @var \Restyii\Client\Schema\Attribute $attribute */)
            $descriptions[$name] = $attribute->primitive;
        return $descriptions;
    }

    /**
     * The attribute types
     * @return array attribute => type
     */
    public function attributeTypes()
    {
        $descriptions = array();
        foreach($this->getResourceSchema()->getAttributes() as $name => $attribute /* @var \Restyii\Client\Schema\Attribute $attribute */)
            $descriptions[$name] = $attribute->type;
        return $descriptions;
    }

    /**
     * Returns the named relation declared for this Resource Model class.
     * @param string $name the relation name
     * @return Link the named relation declared for this Resource Model class. Null if the relation does not exist.
     */
    public function getActiveRelation($name)
    {
        return isset($this->_links[$name]) ? $this->_links[$name] : null;
    }

    /**
     * Checks whether this resource has the named attribute
     * @param string $name attribute name
     * @return boolean whether this resource has the named attribute.
     */
    public function hasAttribute($name)
    {
        return $this->getResourceSchema()->hasAttribute($name);
    }

    /**
     * Returns the named attribute value.
     * If this is a new record and the attribute is not set before,
     * the default column value will be returned.
     * If this record is the result of a query and the attribute is not loaded,
     * null will be returned.
     * You may also use $this->AttributeName to obtain the attribute value.
     * @param string $name the attribute name
     * @return mixed the attribute value. Null if the attribute is not set or does not exist.
     * @see hasAttribute
     */
    public function getAttribute($name)
    {
        if(property_exists($this,$name))
            return $this->$name;
        elseif(isset($this->_attributes[$name]))
            return $this->_attributes[$name];
        else
            return null;
    }

    /**
     * Sets the named attribute value.
     * You may also use $this->AttributeName to set the attribute value.
     * @param string $name the attribute name
     * @param mixed $value the attribute value.
     * @return boolean whether the attribute exists and the assignment is conducted successfully
     * @see hasAttribute
     */
    public function setAttribute($name,$value)
    {
        if(property_exists($this,$name))
            $this->$name=$value;
        elseif($this->getResourceSchema()->hasAttribute($name))
            $this->_attributes[$name]=$value;
        else
            return false;
        return true;
    }


    /**
     * Returns all column attribute values.
     * Note, related objects are not returned.
     * @param mixed $names names of attributes whose value needs to be returned.
     * If this is true (default), then all attribute values will be returned, including
     * those that are not loaded from API (null will be returned for those attributes).
     * If this is null, all attributes except those that are not loaded from DB will be returned.
     * @return array attribute values indexed by attribute names.
     */
    public function getAttributes($names=true)
    {
        $attributes=$this->_attributes;
        foreach($this->getResourceSchema()->getAttributes()->getKeys() as $name)
        {
            if(property_exists($this,$name))
                $attributes[$name]=$this->$name;
            elseif($names===true && !isset($attributes[$name]))
                $attributes[$name]=null;
        }
        if(is_array($names))
        {
            $filtered=array();
            foreach($names as $name)
            {
                if(property_exists($this,$name))
                    $filtered[$name]=$this->$name;
                else
                    $filtered[$name]=isset($attributes[$name])?$attributes[$name]:null;
            }
            return $filtered;
        }
        else
            return $attributes;
    }

    /**
     * Saves the current record.
     *
     * The record is inserted as a row into the database table if its {@link isNewRecord}
     * property is true (usually the case when the record is created using the 'new'
     * operator). Otherwise, it will be used to update the corresponding row in the table
     * (usually the case if the record is obtained using one of those 'find' methods.)
     *
     * Validation will be performed before saving the record. If the validation fails,
     * the record will not be saved. You can call {@link getErrors()} to retrieve the
     * validation errors.
     *
     * If the record is saved via insertion, its {@link isNewRecord} property will be
     * set false, and its {@link scenario} property will be set to be 'update'.
     * And if its primary key is auto-incremental and is not set before insertion,
     * the primary key will be populated with the automatically generated key value.
     *
     * @param boolean $runValidation whether to perform validation before saving the record.
     * If the validation fails, the record will not be saved to database.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the saving succeeds
     */
    public function save($runValidation=true,$attributes=null)
    {
        if(!$runValidation || $this->validate($attributes))
            return $this->getIsNewRecord() ? $this->insert($attributes) : $this->update($attributes);
        else
            return false;
    }

    /**
     * Returns if the current record is new.
     * @return boolean whether the record is new and should be inserted when calling {@link save}.
     * This property is automatically set in constructor and {@link populateRecord}.
     * Defaults to false, but it will be set to true if the instance is created using
     * the new operator.
     */
    public function getIsNewRecord()
    {
        return $this->_new;
    }

    /**
     * Sets if the record is new.
     * @param boolean $value whether the record is new and should be inserted when calling {@link save}.
     * @see getIsNewRecord
     */
    public function setIsNewRecord($value)
    {
        $this->_new=$value;
    }

    /**
     * This event is raised before the record is saved.
     * By setting {@link CModelEvent::isValid} to be false, the normal {@link save()} process will be stopped.
     * @param \CModelEvent $event the event parameter
     */
    public function onBeforeSave($event)
    {
        $this->raiseEvent('onBeforeSave',$event);
    }

    /**
     * This event is raised after the record is saved.
     * @param \CEvent $event the event parameter
     */
    public function onAfterSave($event)
    {
        $this->raiseEvent('onAfterSave',$event);
    }

    /**
     * This event is raised before the record is deleted.
     * By setting {@link CModelEvent::isValid} to be false, the normal {@link delete()} process will be stopped.
     * @param \CModelEvent $event the event parameter
     */
    public function onBeforeDelete($event)
    {
        $this->raiseEvent('onBeforeDelete',$event);
    }

    /**
     * This event is raised after the record is deleted.
     * @param \CEvent $event the event parameter
     */
    public function onAfterDelete($event)
    {
        $this->raiseEvent('onAfterDelete',$event);
    }

    /**
     * This event is raised before an Resource Model finder performs a find call.
     * This can be either a call to Models find methods or a find call
     * when model is loaded in relational context via lazy or eager loading.
     * If you want to access or modify the query criteria used for the
     * find call, you can use {@link getDbCriteria()} to customize it based on your needs.
     * When modifying criteria in beforeFind you have to make sure you are using the right
     * table alias which is different on normal find and relational call.
     * You can use {@link getTableAlias()} to get the alias used for the upcoming find call.
     * Please note that modification of criteria is fully supported as of version 1.1.13.
     * Earlier versions had some problems with relational context and applying changes correctly.
     * @param \CModelEvent $event the event parameter
     * @see beforeFind
     */
    public function onBeforeFind($event)
    {
        $this->raiseEvent('onBeforeFind',$event);
    }

    /**
     * This event is raised after the record is instantiated by a find method.
     * @param \CEvent $event the event parameter
     */
    public function onAfterFind($event)
    {
        $this->raiseEvent('onAfterFind',$event);
    }

    /**
     * This event is raised before an Resource Model finder performs a count call.
     * If you want to access or modify the query criteria used for the
     * count call, you can use {@link getDbCriteria()} to customize it based on your needs.
     * When modifying criteria in beforeCount you have to make sure you are using the right
     * table alias which is different on normal count and relational call.
     * You can use {@link getTableAlias()} to get the alias used for the upcoming count call.
     * @param \CModelEvent $event the event parameter
     * @see beforeCount
     */
    public function onBeforeCount($event)
    {
        $this->raiseEvent('onBeforeCount',$event);
    }

    /**
     * This method is invoked before saving a record (after validation, if any).
     * The default implementation raises the {@link onBeforeSave} event.
     * You may override this method to do any preparation work for record saving.
     * Use {@link isNewRecord} to determine whether the saving is
     * for inserting or updating record.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the saving should be executed. Defaults to true.
     */
    protected function beforeSave()
    {
        if($this->hasEventHandler('onBeforeSave'))
        {
            $event=new \CModelEvent($this);
            $this->onBeforeSave($event);
            return $event->isValid;
        }
        else
            return true;
    }

    /**
     * This method is invoked after saving a record successfully.
     * The default implementation raises the {@link onAfterSave} event.
     * You may override this method to do postprocessing after record saving.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterSave()
    {
        if($this->hasEventHandler('onAfterSave'))
            $this->onAfterSave(new \CEvent($this));
    }

    /**
     * This method is invoked before deleting a record.
     * The default implementation raises the {@link onBeforeDelete} event.
     * You may override this method to do any preparation work for record deletion.
     * Make sure you call the parent implementation so that the event is raised properly.
     * @return boolean whether the record should be deleted. Defaults to true.
     */
    protected function beforeDelete()
    {
        if($this->hasEventHandler('onBeforeDelete'))
        {
            $event=new \CModelEvent($this);
            $this->onBeforeDelete($event);
            return $event->isValid;
        }
        else
            return true;
    }

    /**
     * This method is invoked after deleting a record.
     * The default implementation raises the {@link onAfterDelete} event.
     * You may override this method to do postprocessing after the record is deleted.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterDelete()
    {
        if($this->hasEventHandler('onAfterDelete'))
            $this->onAfterDelete(new \CEvent($this));
    }

    /**
     * This method is invoked before an Resource Model finder executes a find call.
     * The find calls include {@link find}, {@link findAll}, {@link findByPk},
     * {@link findAllByPk}, {@link findByAttributes}, {@link findAllByAttributes},
     * {@link findBySql} and {@link findAllBySql}.
     * The default implementation raises the {@link onBeforeFind} event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is raised properly.
     * For details on modifying query criteria see {@link onBeforeFind} event.
     */
    protected function beforeFind()
    {
        if($this->hasEventHandler('onBeforeFind'))
        {
            $event=new \CModelEvent($this);
            $this->onBeforeFind($event);
        }
    }

    /**
     * This method is invoked before an Resource Model finder executes a count call.
     * The count calls include {@link count} and {@link countByAttributes}
     * The default implementation raises the {@link onBeforeCount} event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is raised properly.
     */
    protected function beforeCount()
    {
        if($this->hasEventHandler('onBeforeCount'))
            $this->onBeforeCount(new \CEvent($this));
    }

    /**
     * This method is invoked after each record is instantiated by a find method.
     * The default implementation raises the {@link onAfterFind} event.
     * You may override this method to do postprocessing after each newly found record is instantiated.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    protected function afterFind()
    {
        if($this->hasEventHandler('onAfterFind'))
            $this->onAfterFind(new \CEvent($this));
    }

    /**
     * Calls {@link beforeFind}.
     * This method is internally used.
     */
    public function beforeFindInternal()
    {
        $this->beforeFind();
    }

    /**
     * Calls {@link afterFind}.
     * This method is internally used.
     */
    public function afterFindInternal()
    {
        $this->afterFind();
    }

    /**
     * Performs an action on an existing resource instance
     *
     * @param string $actionName the name of the action to perform
     * @param array|null $params the extra URL parameters to include
     * @param array|null $data the data to send
     * @param array|null $headers the additional headers to send
     *
     * @return mixed the response to the action
     */
    public function performAction($actionName,  $params = null, $data = null, $headers = null)
    {
        $schema = $this->getResourceSchema();
        $action = $schema->getItemActions()->itemAt($actionName); /* @var \Restyii\Client\Schema\Action $action */
        if ($params !== null) {
            array_unshift($params, $this->getLink('self')->href);
            $url = $params;
        }
        else
            $url = $this->getLink('self')->href;

        $api = $this->getApiConnection();
        return $api->request($action->verb, $url, $data, $headers);
    }

    /**
     * Performs an action on an resource collection
     *
     * @param string $actionName the name of the action to perform
     * @param array|null $params the extra URL parameters to include
     * @param array|null $data the data to send
     * @param array|null $headers the additional headers to send
     *
     * @return mixed the response to the action
     */
    public function performCollectionAction($actionName,  $params = null, $data = null, $headers = null)
    {
        $schema = $this->getResourceSchema();
        $action = $schema->getCollectionActions()->itemAt($actionName); /* @var \Restyii\Client\Schema\Action $action */
        if ($params !== null) {
            array_unshift($params, $this->resourceName());
            $url = $params;
        }
        else
            $url = $this->resourceName();

        $api = $this->getApiConnection();
        return $api->request($action->verb, $url, $data, $headers);
    }


    /**
     * Inserts a row into the table based on this active record attributes.
     * If the table's primary key is auto-incremental and is null before insertion,
     * it will be populated with the actual value after insertion.
     * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
     * After the record is inserted to DB successfully, its {@link isNewRecord} property will be set false,
     * and its {@link scenario} property will be set to be 'update'.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     * @throws \CDbException if the record is not new
     */
    public function insert($attributes=null)
    {
        if(!$this->getIsNewRecord())
            throw new \CDbException(\Yii::t('restyiiclient','The resource cannot be inserted to API because it is not new.'));
        if($this->beforeSave())
        {
            \Yii::trace(get_class($this).'.insert()','restyiiclient.resource.model');
            $result = $this->performCollectionAction('create', null, $this->getAttributes($attributes));
            if($result)
            {
                $schema = $this->getResourceSchema();
                $primaryKey=$schema->primaryKey;
                if(is_string($primaryKey) && $this->$primaryKey===null)
                    $this->$primaryKey=$result[$primaryKey];
                elseif(is_array($primaryKey))
                {
                    foreach($primaryKey as $pk)
                    {
                        if($this->$pk===null)
                        {
                            $this->$pk=$result[$pk];
                            break;
                        }
                    }
                }
                $this->_pk=$this->getPrimaryKey();
                $this->afterSave();
                $this->setIsNewRecord(false);
                $this->setScenario('update');
                return true;
            }
        }
        return false;
    }

    /**
     * Updates the row represented by this active record.
     * All loaded attributes will be saved to the database.
     * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the update is successful
     * @throws \CDbException if the record is new
     */
    public function update($attributes=null)
    {
        if($this->getIsNewRecord())
            throw new \CDbException(\Yii::t('restyiiclient','The resource cannot be updated because it is new.'));
        if($this->beforeSave())
        {
            \Yii::trace(get_class($this).'.update()','restyiiclient.resource.model');
            if($this->_pk===null)
                $this->_pk=$this->getPrimaryKey();
            $this->performAction('update', null,$this->getAttributes($attributes));
            $this->_pk=$this->getPrimaryKey();
            $this->afterSave();
            return true;
        }
        else
            return false;
    }

    /**
     * Deletes the row corresponding to this active record.
     * @throws \CDbException if the record is new
     * @return boolean whether the deletion is successful.
     */
    public function delete()
    {
        if(!$this->getIsNewRecord())
        {
            \Yii::trace(get_class($this).'.delete()','restyiiclient.resource.model');
            if($this->beforeDelete())
            {
                $this->performAction('delete');
                $this->afterDelete();
                return true;
            }
            else
                return false;
        }
        else
            throw new \CDbException(\Yii::t('yii','The resouce cannot be deleted because it is new.'));
    }

    /**
     * Repopulates this resource model with the latest data.
     * @return boolean whether the row still exists in the database. If true, the latest data will be populated to this resource data.
     */
    public function refresh()
    {
        \Yii::trace(get_class($this).'.refresh()','restyiiclient.resource.model');
        if(($record=$this->findByPk($this->getPrimaryKey()))!==null)
        {
            $this->_attributes=array();
            $this->_linked=array();
            foreach($this->getResourceSchema()->getAttributes()->getKeys() as $name)
            {
                if(property_exists($this,$name))
                    $this->$name=$record->$name;
                else
                    $this->_attributes[$name]=$record->$name;
            }
            return true;
        }
        else
            return false;
    }

    /**
     * Compares current active record with another one.
     * The comparison is made by comparing table name and the primary key values of the two active records.
     * @param Model $record record to compare to
     * @return boolean whether the two active records refer to the same row in the database table.
     */
    public function equals($record)
    {
        return $this->resourceName()===$record->resourceName() && $this->getPrimaryKey()===$record->getPrimaryKey();
    }

    /**
     * Returns the primary key value.
     * @return mixed the primary key value. An array (column name=>column value) is returned if the primary key is composite.
     * If primary key is not defined, null will be returned.
     */
    public function getPrimaryKey()
    {
        $schema = $this->getResourceSchema();
        $pk = $schema->getPrimaryKey();
        if(is_string($pk))
            return $this->{$pk};
        elseif(is_array($pk))
        {
            $values=array();
            foreach($pk as $name)
                $values[$name]=$this->$name;
            return $values;
        }
        else
            return null;
    }

    /**
     * Sets the primary key value.
     * After calling this method, the old primary key value can be obtained from {@link oldPrimaryKey}.
     * @param mixed $value the new primary key value. If the primary key is composite, the new value
     * should be provided as an array (column name=>column value).
     */
    public function setPrimaryKey($value)
    {
        $this->_pk=$this->getPrimaryKey();
        $schema = $this->getResourceSchema();
        $pk = $schema->getPrimaryKey();
        if(is_string($pk))
            $this->{$pk}=$value;
        elseif(is_array($pk))
        {
            foreach($pk as $name)
                $this->$name=$value[$name];
        }
    }

    /**
     * Returns the old primary key value.
     * This refers to the primary key value that is populated into the record
     * after executing a find method (e.g. find(), findAll()).
     * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
     * @return mixed the old primary key value. An array (column name=>column value) is returned if the primary key is composite.
     * If primary key is not defined, null will be returned.
     * @since 1.1.0
     */
    public function getOldPrimaryKey()
    {
        return $this->_pk;
    }

    /**
     * Sets the old primary key value.
     * @param mixed $value the old primary key value.
     * @since 1.1.3
     */
    public function setOldPrimaryKey($value)
    {
        $this->_pk=$value;
    }

    /**
     * Performs the actual DB query and populates the Resource Model objects with the query result.
     * This method is mainly internally used by other Resource Model query methods.
     *
     * @param Criteria $criteria the query criteria
     * @param boolean $all whether to return all data
     *
     * @return mixed the Resource Model objects populated with the query result
     */
    protected function query(Criteria $criteria,$all=false)
    {
        $this->beforeFind();
        if(!$all)
            $criteria->limit=1;

        $this->applyScopes($criteria);
        $result = $this->performCollectionAction('search', $criteria->toArray());
        $this->_lastResponse = $result;

        if ($all)
            return $this->populateRecords($result);
        $rows = $result['_embedded'][$this->resourceName()];
        return isset($rows[0]) ? $this->populateRecord($rows[0]) : null;
    }

    /**
     * @return array|null
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }



    /**
     * Specifies which related objects should be eagerly loaded.
     *
     * @return Model the Resource Model object itself.
     */
    public function embed()
    {
        if(func_num_args()>0)
        {
            $embed=func_get_args();
            if(is_array($embed[0]))  // the parameter is given as an array
                $embed=$embed[0];
            if(!empty($embed))
                $this->getCriteria()->mergeWith(new Criteria(array('embed'=>$embed)));
        }
        return $this;
    }

    /**
     * Applies the query scopes to the given criteria.
     * This method merges {@link _criteria} with the given criteria parameter.
     * It then resets {@link _criteria} to be null.
     * @param Criteria $criteria the query criteria. This parameter may be modified by merging {@link dbCriteria}.
     */
    public function applyScopes(&$criteria)
    {
        if(!empty($criteria->scopes))
        {
            $scs=$this->scopes();
            $c=$this->getCriteria();
            foreach((array)$criteria->scopes as $k=>$v)
            {
                if(is_integer($k))
                {
                    if(is_string($v))
                    {
                        if(isset($scs[$v]))
                        {
                            $c->mergeWith($scs[$v],true);
                            continue;
                        }
                        $scope=$v;
                        $params=array();
                    }
                    elseif(is_array($v))
                    {
                        $scope=key($v);
                        $params=current($v);
                    }
                }
                elseif(is_string($k))
                {
                    $scope=$k;
                    $params=$v;
                }

                call_user_func_array(array($this,$scope),(array)$params);
            }
        }

        if(isset($c) || ($c=$this->getCriteria(false))!==null)
        {
            $criteria->mergeWith($c);
            $this->resetScope(false);
        }
    }

    /**
     * Finds a single active record with the specified condition.
     * @param string|array|Criteria|null the criteria
     * @return Model the record found. Null if no record is found.
     */
    public function find($criteria = null)
    {
        \Yii::trace(get_class($this).'.find()','restyiiclient.resource.model');
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);
        return $this->query($criteria);
    }

    /**
     * Finds all resource models satisfying the specified condition.
     * @param string|array|Criteria|null the criteria
     * @return Model[] list of resource models satisfying the specified condition. An empty array is returned if none is found.
     */
    public function findAll($criteria = null)
    {
        \Yii::trace(get_class($this).'.findAll()','restyiiclient.resource.model');
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);
        return $this->query($criteria,true);
    }

    /**
     * Finds a single resource model with the specified primary key.
     * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
     * @param string|array|Criteria|null the criteria
     * @return Model the record found. Null if none is found.
     */
    public function findByPk($pk, $criteria = null)
    {
        \Yii::trace(get_class($this).'.findByPk()','restyiiclient.resource.model');
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);

        $c = $this->getCriteria(false);

        if ($c) {
            $criteria->mergeWith(new Criteria(array(
                'q' => $c->q,
                'filter' => $c->filter,
                'limit' => $c->limit,
                'page' => $c->page,
                'embed' => $c->embed,
            )));
        }
        $this->applyScopes($criteria);
        $params = $criteria->toArray();

        if (is_array($pk)) {
            $url = $this->resourceName();
            $params = \CMap::mergeArray($params, $pk);
        }
        else
            $url = $this->resourceName().'/'.$pk;


        array_unshift($params, $url);

        $schema = $this->getResourceSchema();
        $action = $schema->getItemActions()->itemAt('read'); /* @var \Restyii\Client\Schema\Action $action */
        $api = $this->getApiConnection();
        return $this->populateRecord($api->request($action->verb, $params));
    }


    /**
     * Finds a single resource model that has the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the resource models should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param string|array|Criteria|null the criteria
     * @return Model the record found. Null if none is found.
     */
    public function findByAttributes($attributes,$criteria = null)
    {
        \Yii::trace(get_class($this).'.findByAttributes()','restyiiclient.resource.model');
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);
        if ($criteria->filter === array())
            $criteria->filter = $attributes;
        else
            $criteria->filter = \CMap::mergeArray($criteria->filter, $attributes);
        return $this->query($criteria);
    }

    /**
     * Finds all resource models that have the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the resource models should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param string|array|Criteria|null the criteria
     * @return Model[] the records found. An empty array is returned if none is found.
     */
    public function findAllByAttributes($attributes,$criteria = null)
    {
        \Yii::trace(get_class($this).'.findAllByAttributes()','restyiiclient.resource.model');
        if (!($criteria instanceof Criteria))
            $criteria = new Criteria($criteria);
        if ($criteria->filter === array())
            $criteria->filter = $attributes;
        else
            $criteria->filter = \CMap::mergeArray($criteria->filter, $attributes);
        return $this->query($criteria,true);
    }

    /**
     * Creates an resource model with the given attributes.
     * This method is internally used by the find methods.
     * @param array $attributes attribute values (column name=>column value)
     * @param boolean $callAfterFind whether to call {@link afterFind} after the record is populated.
     * @return Model the newly created resource model. The class of the object is the same as the model class.
     * Null is returned if the input data is false.
     */
    public function populateRecord($attributes,$callAfterFind=true)
    {
        if($attributes!==false)
        {
            $record=$this->instantiate($attributes);
            $record->setScenario('update');
            $record->init();
            $schema = $this->getResourceSchema();
            if (isset($attributes['_links'])) {
                $record->setLinks($attributes['_links']);
                unset($attributes['_links']);
            }
            if (isset($attributes['_errors'])) {
                $record->addErrors($attributes['_errors']);
                unset($attributes['_errors']);
            }

            $record->setLinked($attributes);
            foreach($attributes as $name=>$value)
            {
                if ($name === '_embedded')
                    continue;
                else if(property_exists($record,$name))
                    $record->$name=$value;
                else if($schema->hasAttribute($name))
                    $record->_attributes[$name]=$value;
            }
            $record->_pk=$record->getPrimaryKey();
            $record->attachBehaviors($record->behaviors());
            if($callAfterFind)
                $record->afterFind();
            return $record;
        }
        else
            return null;
    }

    /**
     * Creates a list of resource models based on the input data.
     * This method is internally used by the find methods.
     * @param array $data list of attribute values for the resource models.
     * @param boolean $callAfterFind whether to call {@link afterFind} after each record is populated.
     * @param string $index the name of the attribute whose value will be used as indexes of the query result array.
     * If null, it means the array will be indexed by zero-based integers.
     * @return Model[] list of resource models.
     */
    public function populateRecords($data,$callAfterFind=true,$index=null)
    {
        $embedded = $data['_embedded'][$this->resourceName()];
        $records=array();
        foreach($embedded as $attributes)
        {
            if(($record=$this->populateRecord($attributes,$callAfterFind))!==null)
            {
                if($index===null)
                    $records[]=$record;
                else
                    $records[$record->$index]=$record;
            }
        }
        return $records;
    }

    /**
     * Creates an resource model instance.
     * This method is called by {@link populateRecord} and {@link populateRecords}.
     * You may override this method if the instance being created
     * depends the attributes that are to be populated to the record.
     * For example, by creating a record based on the value of a column,
     * you may implement the so-called single-table inheritance mapping.
     * @param array $attributes list of attribute values for the resource models.
     * @return Model the resource model
     */
    protected function instantiate($attributes)
    {
        $class=get_class($this);
        $model=new $class(null);
        return $model;
    }

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }
}
