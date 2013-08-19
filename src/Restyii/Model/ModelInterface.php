<?php

namespace Restyii\Model;

interface ModelInterface
{
    /**
     * Returns the name to use for this particular resource instance.
     * The result of this function will be used as default anchor text when
     * creating links to resource instances.
     * @return string the name for this resource
     */
    public function instanceLabel();

    /**
     * Returns the label for this resource type.
     *
     * @param bool $plural whether or not to return the plural version of the label, e.g. 'Users' instead of 'User'
     *
     * @return string the resource label
     */
    public function classLabel($plural = false);

    /**
     * Returns the url name to use when creating links to this resource.
     * This is usually equal to the route to the default controller that
     * deals with resources of this type.
     * @return string the url name for this resource
     */
    public function controllerID();

    /**
     * Returns the links to other related resources.
     *
     * @return array the links for this resource, name => array("href" => "...", "title" => ",,"), ...
     */
    public function links();

    /**
     * Gets the descriptions for the attributes.
     * @return array the descriptions, attribute => description
     */
    public function attributeDescriptions();

    /**
     * Gets the types for the attributes.
     * @return array the attribute types, attribute => type
     */
    public function attributeTypes();

    /**
     * Gets the names of the attributes that are visible.
     * @return array the visible attribute names
     */
    public function getVisibleAttributeNames();


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
    public function createUrl($action = "read", $params = array(), $schema = false, $ampersand = '&');

    /**
     * Sets the attribute values in a massive way.
     * @param array $values attribute values (name=>value) to be set.
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
     * @see getSafeAttributeNames
     * @see attributeNames
     */
    public function setAttributes($values,$safeOnly=true);

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute=null);

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     */
    public function getErrors($attribute=null);

    /**
     * Returns the static model instance, used for finding resource models.
     * @return ModelInterface the static model instance
     */
    public static function model();

    /**
     * Performs a search and returns a data provider with the results.
     * @return \CDataProvider the data provider containing the results.
     */
    public function search();

    /**
     * Return an array of statistics for the resource model instance.
     * @return array the stats for the model
     */
    public function stats();

    /**
     * Return an array of aggregate statistics for the resource model collection.
     * @return array the stats for the collection
     */
    public function aggregate();
}
