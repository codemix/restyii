<?php

namespace Restyii\Model;
use CEvent;

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
            $label = $this->pluralize(get_class($this));
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
     * @return array the links for this resource, name => array("href" => "...", "label" => ",,"), ...
     */
    public function links()
    {
        return array();
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
     * Declares whether or not certain attributes are visible or not.
     * @return array the attributes with their visibilities
     */
    public function attributeVisibilities()
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
            case 'integer';
            case 'float';
            case 'boolean';
            case 'string';
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
                $inputType = 'numberField';
                break;
            case 'boolean';
                $inputType = 'checkbox';
                break;
            case 'choice';
                $inputType = 'radioButtonList';
                $methodName = $attribute.'Labels';
                if (method_exists($this, $methodName))
                    $htmlOptions['items'] = $this->{$methodName}();
                break;
            case 'date';
                $inputType = 'dateField';
                break;
            case 'datetime';
                $inputType = 'datetimeField';
                break;
            case 'time';
                $inputType = 'timeField';
                break;
            case 'text';
                $inputType = 'textField';
                break;
            case 'password';
                $inputType = 'passwordField';
                break;
            case 'email';
                $inputType = 'emailField';
                break;
            case 'phone';
                $inputType = 'telField';
                break;
            case 'ntext';
                $inputType = 'textArea';
                break;
            default;
                $inputType = 'textField';
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
     * Determines whether or not the given attribute is visible.
     * @param string $attribute the name of the attribute
     *
     * @return bool true if the attribute is visible
     */
    public function isAttributeVisible($attribute)
    {
        $visibilities = $this->attributeVisibilities();
        if (isset($visibilities[$attribute]))
            return $visibilities[$attribute];
        if ($this->isAttributeSafe($attribute))
            return true;
        $pk = $this->getTableSchema()->primaryKey;
        if (is_array($pk))
            return in_array($attribute, $pk);
        else
            return $attribute == $pk;
    }

    /**
     * Gets the names of the attributes that are visible.
     * @return array the visible attribute names
     */
    public function getVisibleAttributeNames()
    {
        $attributes = array();
        $visibilities = $this->attributeVisibilities();

        $pk = $this->getTableSchema()->primaryKey;
        if (is_array($pk)) {
            foreach($pk as $name) {
                if (isset($visibilities[$name]))
                    continue;
                else
                    $attributes[] = $name;
            }
        }
        else {
            if (!isset($visibilities[$pk]))
                $attributes[] = $pk;
        }

        foreach($visibilities as $name => $isVisible) {
            if ($isVisible)
                $attributes[] = $name;
        }

        foreach($this->getSafeAttributeNames() as $name) {
            if (!in_array($name, $attributes))
                $attributes[] = $name;
        }
        return $attributes;
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
        $modelClass = get_class($this);

        if ($this !== $modelClass::model()) {
            $pks = $this->getPrimaryKey();
            if (!is_array($pks))
                $pks = array($this->getTableSchema()->primaryKey => $pks);
            foreach($pks as $key => $value) {
                if (!array_key_exists($key, $params))
                    $params[$key] = $value;
                else if ($params[$key] === null)
                    unset($params[$key]);
            }
        }
        $route = '/'.$this->controllerID().'/'.$action;
        if ($schema === false)
            return \Yii::app()->createUrl($route, $params, $ampersand);
        else if ($schema === true)
            return \Yii::app()->createAbsoluteUrl($route, $params, '', $ampersand);
        else
            return \Yii::app()->createAbsoluteUrl($route, $params, $schema, $ampersand);
    }

    /**
     * Converts a word to its plural form.
     * Note that this is for English only!
     * For example, 'apple' will become 'apples', and 'child' will become 'children'.
     * @param string $name the word to be pluralized
     * @return string the pluralized word
     */
    public function pluralize($name)
    {
        $rules=array(
            '/(m)ove$/i' => '\1oves',
            '/(f)oot$/i' => '\1eet',
            '/(c)hild$/i' => '\1hildren',
            '/(h)uman$/i' => '\1umans',
            '/(m)an$/i' => '\1en',
            '/(s)taff$/i' => '\1taff',
            '/(t)ooth$/i' => '\1eeth',
            '/(p)erson$/i' => '\1eople',
            '/([m|l])ouse$/i' => '\1ice',
            '/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/(shea|lea|loa|thie)f$/i' => '\1ves',
            '/([ti])um$/i' => '\1a',
            '/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
            '/(bu)s$/i' => '\1ses',
            '/(ax|test)is$/i' => '\1es',
            '/s$/' => 's',
        );
        foreach($rules as $rule=>$replacement)
        {
            if(preg_match($rule,$name))
                return preg_replace($rule,$replacement,$name);
        }
        return $name.'s';
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
        if (!empty($params['q'])) {
            $safeAttributes = $this->getSafeAttributeNames();
            $alias = $this->getTableAlias(false, false);
            $columns = $this->getTableSchema()->columns;
            foreach($safeAttributes as $attribute) {
                if (!isset($columns[$attribute]))
                    continue;
                $column = $columns[$attribute];
                if ($column->type == "string")
                    $criteria->compare($alias.'.'.$attribute, $params['q'], true, 'OR');
                else
                    $criteria->compare($alias.'.'.$attribute, $params['q'], false, 'OR');
            }
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
}
