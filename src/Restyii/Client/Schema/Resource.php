<?php

namespace Restyii\Client\Schema;

/**
 * # Resource
 *
 * @package Restyii\Client\Schema
 */
class Resource extends AbstractEntity
{
    /**
     * @var string the name of the resource
     */
    public $name;

    /**
     * @var string the resource label
     */
    public $label;

    /**
     * @var string the resource collection label
     */
    public $collectionLabel;

    /**
     * @var string the resource description
     */
    public $description;

    /**
     * @var Attribute[] the resource attributes
     */
    protected $_attributes;

    /**
     * @var Action[] the resource actions
     */
    protected $_itemActions;

    /**
     * @var Action[] the resource actions
     */
    protected $_collectionActions;

    /**
     * @param \Restyii\Client\Schema\Action[] $actions
     */
    public function setItemActions($actions)
    {
        foreach($actions as $name => $action) {
            if (!($action instanceof Action)) {
                $actions[$name] = new Action($action);
                $actions[$name]->name = $name;
            }
        }
        if (!($actions instanceof \CAttributeCollection)) {
            $c = new \CAttributeCollection();
            $c->caseSensitive = true;
            $c->copyFrom($actions);
            $actions = $c;
        }
        $this->_itemActions = $actions;
    }

    /**
     * @return \CAttributeCollection the collection containing the actions
     */
    public function getItemActions()
    {
        if ($this->_itemActions === null) {
            $this->_itemActions = new \CAttributeCollection();
            $this->_itemActions->caseSensitive = true;
        }
        return $this->_itemActions;
    }

    /**
     * @param \Restyii\Client\Schema\Action[] $actions
     */
    public function setCollectionActions($actions)
    {
        foreach($actions as $name => $action) {
            if (!($action instanceof Action)) {
                $actions[$name] = new Action($action);
                $actions[$name]->name = $name;
            }
        }
        if (!($actions instanceof \CAttributeCollection)) {
            $c = new \CAttributeCollection();
            $c->caseSensitive = true;
            $c->copyFrom($actions);
            $actions = $c;
        }
        $this->_collectionActions = $actions;
    }

    /**
     * @return \CAttributeCollection the collection containing the actions
     */
    public function getCollectionActions()
    {
        if ($this->_collectionActions === null) {
            $this->_collectionActions = new \CAttributeCollection();
            $this->_collectionActions->caseSensitive = true;
        }
        return $this->_collectionActions;
    }

    /**
     * Determines whether or not the resource has a given action
     *
     * @param string $name the action name
     *
     * @return bool true if the action exists
     */
    public function hasAction($name)
    {
        return $this->getItemActions()->itemAt($name) !== null;
    }

    /**
     * @param \Restyii\Client\Schema\Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        foreach($attributes as $name => $attribute) {
            if (!($attribute instanceof Attribute)) {
                $attributes[$name] = new Attribute($attribute);
                $attributes[$name]->name = $name;
            }
        }
        if (!($attributes instanceof \CAttributeCollection)) {
            $c = new \CAttributeCollection();
            $c->caseSensitive = true;
            $c->copyFrom($attributes);
            $attributes = $c;
        }
        $this->_attributes = $attributes;
    }

    /**
     * @return \CAttributeCollection the collection containing the attributes
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = new \CAttributeCollection();
            $this->_attributes->caseSensitive = true;
        }
        return $this->_attributes;
    }

    /**
     * Determines whether or not the resource has a given attribute
     *
     * @param string $name the attribute name
     *
     * @return bool true if the attribute exists
     */
    public function hasAttribute($name)
    {
        return $this->getAttributes()->itemAt($name) !== null;
    }

    /**
     * Gets the primary key for the resource
     * @return mixed the primary key, an array if composite
     */
    public function getPrimaryKey()
    {
        $pk = array();
        foreach($this->getAttributes() as $name => $attribute /* @var Attribute $attribute */) {
            if ($attribute->isPrimaryKey)
                $pk[] = $name;
        }
        $count = count($pk);
        if (!$count)
            return 'id';
        else if ($count == 1)
            return $pk[0];
        else
            return $pk;
    }

    /**
     * Gets the default attribute values
     * @return array the default attribute values, name => value
     */
    public function getAttributeDefaults()
    {
        $attributes = array();
        foreach($this->getAttributes() as $name => $attribute /* @var Attribute $attribute */)
            $attributes[$name] = $attribute->defaultValue;

        return $attributes;
    }
}
