<?php

namespace Restyii\Web;


use CUrlManager;

class Application extends \CWebApplication
{

    /**
     * @var string the description of the web application
     */
    protected $_description;

    /**
     * Sets the description for the application
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * Gets the description for the application
     * @return string
     */
    public function getDescription()
    {
        if ($this->_description === null)
            $this->_description = \Yii::t('resource', '{name} Web Application', array('{name}' => $this->name));
        return $this->_description;
    }


    /**
     * Registers the core application components.
     * This method overrides the parent implementation by registering additional core components.
     * @see setComponents
     */
    protected function registerCoreComponents()
    {
        parent::registerCoreComponents();

        $components=array(
            'schema'=>array(
                'class'=>'Restyii\\Meta\\Schema',
            ),
            'urlManager' => array(
                'class' => 'Restyii\\Web\\UrlManager',
            ),
            'request' => array(
                'class' => 'Restyii\\Web\\Request',
            ),
        );

        $this->setComponents($components);
    }

    /**
     * @return \Restyii\Meta\Schema the schema for the application
     */
    public function getSchema()
    {
        return $this->getComponent('schema');
    }


}
