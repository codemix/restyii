<?php

namespace Restyii\Model;
use CEvent;
use Restyii\Utils\String;

/**
 * Base class for RESTful active records
 *
 * @package Restyii\Model
 */
abstract class ActiveRecord extends \CActiveRecord implements ModelInterface
{
    /**
     * @var array the old attributes of the model
     */
    protected $_oldAttributes = array();

    /**
     * @var bool whether or not this model has been deleted
     */
    protected $_isDeleted = false;

    /**
     * Returns the name to use for this particular resource instance.
     * The result of this function will be used as default anchor text when
     * creating links to resource instances.
     * @return string the name for this resource
     */
    public function instanceLabel()
    {
        $pk = $this->getPrimaryKey();
        if (is_array($pk))
            return implode(" ", $pk);
        else
            return $pk;
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
            $label = String::humanize(String::pluralize(get_class($this)));
        else
            $label = get_class($this);
        return $this->generateAttributeLabel($label);
    }

    /**
     * Returns the url name to use when creating links to this resource.
     * This is usually equal to the route to the default controller that
     * deals with resources of this type.
     * @return string the url name for this resource
     */
    public function controllerID()
    {
        return lcfirst(get_class($this));
    }

    /**
     * Returns the links to other related resources.
     *
     * @return array the links for this resource, name => array("href" => "...", "title" => ",,"), ...
     */
    public function links()
    {
        return array();
    }

    /**
     * Returns a list of scenarios and the corresponding safe attributes.
     *
     * Child classes should override this method to define which attributes
     * are safe under which scenario.
     *
     * The default implementation returns null, which gives you the default
     * behavior of CActiveRecord.
     *
     * @return array|null list of scenarios and corresponding active attributes or null for default behavior
     */
    public function scenarios()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSafeAttributeNames()
    {
        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if($scenarios===null) {
            return parent::getSafeAttributeNames();
        } elseif(isset($scenarios[$scenario])) {
            return $scenarios[$scenario];
        } else {
            return array();
        }
    }

    /**
     * Declares the descriptions for the attributes.
     * @return array the descriptions, attribute => description
     */
    public function attributeDescriptions()
    {
        return array();
    }

    /**
     * Declares the types for the attributes.
     * @return array the attribute types, attribute => type
     */
    public function attributeTypes()
    {
        return array();
    }

    /**
     * Declares the primitive types for the attributes.
     * @return array the attribute types, attribute => type
     */
    public function attributePrimitives()
    {
        return array();
    }

    /**
     * Gets the names of the formats for the attributes.
     * @return array the attribute types, attribute => type
     */
    public function attributeFormats()
    {
        return array();
    }

    /**
     * Declares the appropriate input types for the attributes.
     * @return array the attribute input types, attribute => config
     */
    public function attributeInputs()
    {
        return array();
    }

    /**
     * @return array the names of attributes that should not be visible
     */
    public function hiddenAttributes()
    {
        return array();
    }


    /**
     * Gets the appropriate description for a given attribute
     * @param string $attribute the name of the attribute to describe
     *
     * @return string the attribute description, empty string if none is present
     */
    public function getAttributeDescription($attribute)
    {
        $descriptions = $this->attributeDescriptions();
        if (isset($descriptions[$attribute]))
            return $descriptions[$attribute];
        else
            return '';
    }


    /**
     * Gets the appropriate type for a given attribute
     * @param string $attribute the name of the attribute
     *
     * @return string the attribute type, e.g. 'string' or 'integer'
     */
    public function getAttributeType($attribute)
    {
        $types = $this->attributeTypes();
        if (isset($types[$attribute]))
            return $types[$attribute];

        $columns = $this->getTableSchema()->columns;
        if (!isset($columns[$attribute]))
            return 'string';
        $column = $columns[$attribute];
        if ($column->type == 'double')
            return 'float';
        if (strncmp($column->dbType,'enum',4)===0)
            return 'enum';
        else if (strstr($column->dbType, 'unsigned'))
            return 'integer';
        else if (strstr($column->dbType, 'tinyint(1)'))
            return 'boolean';
        else if (strstr($column->dbType, 'datetime') || strstr($column->dbType, 'timestamp'))
            return 'datetime';
        else if (strstr($column->dbType, 'date'))
            return 'date';
        else if (strstr($column->dbType, 'time'))
            return 'time';
        else
            return $column->type;
    }

    /**
     * Gets the appropriate primitive type for a given attribute
     * @param string $attribute the name of the attribute
     *
     * @return string the attribute type, e.g. 'string' or 'integer'
     */
    public function getAttributePrimitive($attribute)
    {
        $primitives = $this->attributePrimitives();
        if (isset($primitives[$attribute]))
            return $primitives[$attribute];
        $type = $this->getAttributeType($attribute);
        switch($type) {
            case 'integer':
            case 'float':
            case 'boolean':
            case 'string':
            case 'array':
                return $type;
            default;
                return 'string';
        }

    }

    /**
     * Gets the name of the format for the given attribute.
     * @param string $attribute the attribute to get the format for
     *
     * @return string the attribute format
     */
    public function getAttributeFormat($attribute)
    {
        $formats = $this->attributeFormats();
        if (isset($formats[$attribute]))
            return $formats[$attribute];
        $type = $this->getAttributeType($attribute);
        switch ($type) {
            case 'float';
            case 'integer';
                return 'number';
            case 'boolean';
                return 'boolean';
            case 'enum';
                return 'choice';
            case 'array';
                return 'array';
            case 'date';
                return 'date';
            case 'datetime';
                return 'datetime';
            case 'time';
                return 'time';
            case 'string';
                $columns = $this->getTableSchema()->columns;
                if (stristr($attribute, 'password'))
                    return 'password';
                elseif (stristr($attribute, 'email'))
                    return 'email';
                elseif (stristr($attribute, 'phone'))
                    return 'phone';
                elseif (stristr($attribute, 'phone'))
                    return 'phone';
                elseif (isset($columns[$attribute]) && ($columns[$attribute]->size == 0 || $columns[$attribute]->size > 500))
                    return 'ntext';
                else
                    return 'text';
                break;
            default;
                return 'text';
        }
    }

    /**
     * Gets the input field configuration for the given attribute.
     * @param string $attribute the attribute to get the input config for
     *
     * @return array an array containing the name of the input type,
     * optionally followed by the input type configuration array.
     */
    public function getAttributeInput($attribute)
    {
        $inputs = $this->attributeInputs();
        if (isset($inputs[$attribute]))
            return $inputs[$attribute];
        $format = $this->getAttributeFormat($attribute);
        $htmlOptions = array();
        switch ($format) {
            case 'number';
                $inputType = 'number';
                break;
            case 'boolean';
                $inputType = 'checkbox';
                break;
            case 'array';
                $inputType = 'checkboxlist';
                $methodName = $attribute.'Labels';
                if (method_exists($this, $methodName))
                    $htmlOptions['items'] = $this->{$methodName}();
                break;
            case 'choice';
                $inputType = 'radiolist';
                $methodName = $attribute.'Labels';
                if (method_exists($this, $methodName))
                    $htmlOptions['items'] = $this->{$methodName}();
                break;
            case 'date';
                $inputType = 'date';
                break;
            case 'datetime';
                $inputType = 'datetime';
                break;
            case 'time';
                $inputType = 'time';
                break;
            case 'text';
                $inputType = 'text';
                break;
            case 'password';
                $inputType = 'password';
                break;
            case 'email';
                $inputType = 'email';
                break;
            case 'phone';
                $inputType = 'tel';
                break;
            case 'ntext';
                $inputType = 'textarea';
                break;
            default;
                $inputType = 'text';
        }
        foreach($this->getValidators($attribute) as $validator) {
            if ($validator instanceof \CStringValidator) {
                if (!empty($validator->max))
                    $input['maxlength'] = $validator->max;
            }
            else if ($validator instanceof \CNumberValidator) {
                if ($validator->min !== null)
                    $input['min'] = $validator->min;
                if ($validator->max !== null)
                    $input['max'] = $validator->max;
            }
        }
        if (count($htmlOptions))
            return array($inputType, $htmlOptions);
        else
            return array($inputType);
    }

    /**
     * @param string $attribute the name of the attribute
     *
     * @return bool whether the attribute is visible
     */
    public function isAttributeVisible($attribute)
    {
        return !in_array($attribute, $this->hiddenAttributes());
    }

    /**
     * Gets the names of the attributes that are visible.
     * @return array the visible attribute names
     */
    public function getVisibleAttributeNames()
    {
        $attributes = array();

        $hiddenAttributes = $this->hiddenAttributes();

        foreach($this->attributeNames() as $name) {
            if(!in_array($name, $hiddenAttributes)) {
                $attributes[] = $name;
            }
        }

        return $attributes;
    }

    /**
     * @param array list of attributes to return. If not provided, all visible attributes will be returned.
     * @param bool $resolveForeignKeys whether to resolve Fk attributes to the item label of the related model
     * @return array an array of attributes formatted in their respective format indexed by their name
     */
    public function getFormattedAttributes($attributes = array(), $resolveForeignKeys=true)
    {
        $values = array();

        if($attributes===array())
            $attributes = $this->getVisibleAttributeNames();

        foreach($attributes as $name)
            $values[$name] = $this->getFormattedAttribute($name, $resolveForeignKeys);

        return $values;
    }

    /**
     * @param string $name the name of an attribute
     * @param bool whether to resolve foreign key attributes to the instance label of the related record. Default is true.
     * @return string the attribute formatted in its corresponding format
     */
    public function getFormattedAttribute($name, $resolveForeignKey=true)
    {
        if($resolveForeignKey) {
            $fks = $this->getTableSchema()->foreignKeys;
            if(isset($fks[$name])) {
                foreach($this->getMetaData()->relations as $n => $r) {
                    if(($r instanceof \CBelongsToRelation) && $r->foreignKey===$name && ($related=$this->getRelated($n)))
                        return $related->instanceLabel();
                }
            }
        }
        return \Yii::app()->getComponent('format')->attribute($this, $name);
    }

    /**
     * @param array $oldAttributes
     */
    public function setOldAttributes($oldAttributes)
    {
        $this->_oldAttributes = $oldAttributes;
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }

    /**
     * Casts a value for the given attribute name
     *
     * @param string $name the name of the attribute to retrieve the type for
     * @param mixed $value the value to cast
     *
     * @return mixed the typecast value
     */
    public function castAttribute($name, $value)
    {
        return $this->castValue($this->getAttributePrimitive($name), $value);

    }

    /**
     * Cast a given value to the given type.
     *
     * @param string $type the php type, e.g. 'string'
     * @param mixed $value the value to cast
     * @param bool $allowNull if null should be returned for empty values
     *
     * @return mixed the typecast value
     */
    public function castValue($type, $value, $allowNull = true)
    {
        if ($value instanceof \CDbExpression)
            return $value;
        if ($allowNull && ($value === '' || $value === null))
            return null;
        settype($value, $type);
        return $value;
    }


    /**
     * Sets the attribute value.
     *
     * @param string $name the attribute name
     * @param mixed $value the attribute value
     * @param bool $cast whether to cast the value to the appropriate type, defaults to true
     *
     * @return boolean whether the attribute exists and the assignment is conducted successfully
     */
    public function setAttribute($name, $value, $cast = true)
    {
        if ($cast) {
            if(property_exists($this,$name))
                $value = $this->castAttribute($name, $value);
            elseif(isset($this->getMetaData()->columns[$name]))
                $value = $this->castAttribute($name, $value);
        }
        return parent::setAttribute($name, $value);
    }

    /**
     * Sets whether the model has been deleted
     * @param boolean $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->_isDeleted = $isDeleted;
    }

    /**
     * @return boolean true if the model has been deleted
     */
    public function getIsDeleted()
    {
        return $this->_isDeleted;
    }



    /**
     * @inheritDoc
     */
    protected function afterFind()
    {
        parent::afterFind();
        $this->_oldAttributes = $this->getAttributes();
    }

    /**
     * @inheritDoc
     */
    protected function afterSave()
    {
        parent::afterSave();
        $this->_oldAttributes = $this->getAttributes();
    }

    /**
     * @inheritDoc
     */
    protected function afterDelete()
    {
        parent::afterDelete();
        $this->_isDeleted = true;
    }
    /**
     * Returns the difference between the old attributes and the new attributes
     * of the owner model.
     * @return array a collection of attributes, name => array(oldValue, newValue)
     */
    public function diff()
    {
        $oldAttributes = $this->getOldAttributes();
        $diff = array();
        foreach($this->getAttributes() as $attribute => $value)
            if (!array_key_exists($attribute, $oldAttributes))
                $diff[$attribute] = array(null, $value);
            else if ($oldAttributes[$attribute] != $value)
                $diff[$attribute] = array($oldAttributes[$attribute], $value);
        return $diff;
    }


    /**
     * Creates a URL for this resource.
     *
     * @param string $action the action to link to, defaults to 'read'
     * @param array $params the parameters to include in the link
     * @param bool|string $schema the schema, if a value other than false is specified, an absolute url will be created.
     * @param string $ampersand the string to use as a query separator in generated urls
     *
     * @return string the generated URL
     */
    public function createUrl($action = "read", $params = array(), $schema = false, $ampersand = '&')
    {
        $params = $this->applyUrlParams($params);
        $route = '/'.$this->controllerID().'/'.$action;
        if ($schema === false)
            return \Yii::app()->createUrl($route, $params, $ampersand);
        else if ($schema === true)
            return \Yii::app()->createAbsoluteUrl($route, $params, '', $ampersand);
        else
            return \Yii::app()->createAbsoluteUrl($route, $params, $schema, $ampersand);
    }

    /**
     * Applies the resource specific parameters when creating a URL for a resource.
     * @param array $params the existing list of parameters
     *
     * @return array the parameter list
     */
    protected function applyUrlParams($params)
    {
        $modelClass = get_class($this);
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this !== $modelClass::model()) {
            $pks = $this->getPrimaryKey();
            if (!is_array($pks))
                $pks = array($this->getTableSchema()->primaryKey => $pks);
            foreach ($pks as $key => $value) {
                if (!array_key_exists($key, $params))
                    $params[$key] = $value;
                else if ($params[$key] === null)
                    unset($params[$key]);
            }
        }
        return $params;
    }

    /**
     * Performs a search and returns a data provider that can return the results.
     *
     *
     * @param array $params the search parameters
     *
     * @return ActiveDataProvider the data provider containing the results.
     */
    public function search($params = array())
    {
        $criteria = new \CDbCriteria();
        $alias = $this->getTableAlias(false, false);
        if (!empty($params['q'])) {
            $columns = $this->getTableSchema()->columns;
            $safeAttributes = $this->getSafeAttributeNames();
            $conditions = array();
            $bind = array();

            foreach($safeAttributes as $attribute) {
                if (!isset($columns[$attribute]))
                    continue;
                $column = $columns[$attribute];
                if ($column->type == "string") {
                    $conditions[] = $alias.'.'.$attribute.' LIKE :search_wc_'.$attribute;
                    $bind[':search_wc_'.$attribute] = '%'.$params['q'].'%';
                }
                else {
                    $conditions[] = $alias.'.'.$attribute.' LIKE :search_'.$attribute;
                    $bind[':search_'.$attribute] = $params['q'];
                }
            }

            if (count($conditions)) {
                $criteria->params = \CMap::mergeArray($criteria->params, $bind);
                $criteria->addCondition(implode(' OR ', $conditions));
            }

        }
        if (!empty($params['filter'])) {
            $filterCriteria = $this->getCommandBuilder()->createColumnCriteria(
                $this->getTableSchema(),
                $params['filter'],
                '',
                array(),
                $alias.'.'

            );
            $criteria->mergeWith($filterCriteria);
        }
        return new ActiveDataProvider($this, array(
            'criteria' => $criteria,
            'params' => $params,
            'pagination' => array(
                'pageVar' => 'page',
                'pageSize' => isset($params['limit']) ? $params['limit'] : 20,
                'validateCurrentPage' => false,
            )
        ));
    }

    /**
     * Return an array of statistics for the resource model instance.
     * @return array the stats for the model
     */
    public function stats()
    {
        return array();
    }


    /**
     * Return an array of aggregate statistics for the resource model collection.
     * @return array the stats for the collection
     */
    public function aggregate()
    {
        return array();
    }

}
