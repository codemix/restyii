<?php

namespace Restyii\Web;


use CException;
use CUrlManager;
use CWebModule;
use Yii;

/**
 * # Web Application
 *
 * @property \Restyii\Meta\Schema $schema the application schema
 * @property \Restyii\Web\UrlManager $urlManager the url manager
 * @property \Restyii\Web\Request $request the http request
 * @property \Restyii\Event\AbstractEventStream $eventStream the application event stream
 *
 * @method \Restyii\Web\Request getRequest()
 *
 * @author Charles Pick <charles@codemix.com>
 * @package Restyii\Web
 */
class Application extends \CWebApplication
{

    /**
     * @var string the name of the default controller for the application.
     */
    public $defaultController = "default";

    /**
     * @var string the description of the web application
     */
    protected $_description;

    /**
     * @var string the application basepath
     */
    protected $_basePath;

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
            'eventStream' => array(
                'class' => 'Restyii\Event\LocalEventStream',
            ),
            'request' => array(
                'class' => 'Restyii\Web\Request',
            ),
            'schema'=>array(
                'class'=>'Restyii\Meta\Schema',
            ),
            'urlManager' => array(
                'class' => 'Restyii\Web\UrlManager',
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

    /**
     * @return \Restyii\Event\AbstractEventStream the application event stream
     */
    public function getEventStream()
    {
        return $this->getComponent('eventStream');
    }

    /**
     * @inheritDoc
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Sets the application base path, but performs no validation for speed reasons.
     *
     * @inheritDoc
     */
    public function setBasePath($path)
    {
        $this->_basePath=realpath($path);
    }


    /**
     * Overrides the parent to avoid disk seeks,
     * This means that 'grouped' controllers are
     * no longer supported.
     *
     * @inheritDoc
     */
    public function createController($route,$owner=null)
    {
        if($owner===null)
            $owner=$this;
        if(($route=trim($route,'/'))==='')
            $route=$owner->defaultController;
        $caseSensitive=$this->getUrlManager()->caseSensitive;

        if (($pos=strpos($route,'/'))!==false)
        {
            $id=substr($route,0,$pos);
            if(!preg_match('/^\w+$/',$id))
                return null;
            if(!$caseSensitive)
                $id=strtolower($id);
            $route=(string)substr($route,$pos+1);
            if(isset($owner->controllerMap[$id]))
            {
                return array(
                    Yii::createComponent($owner->controllerMap[$id],$id,$owner===$this?null:$owner),
                    $this->parseActionParams($route),
                );
            }

            if(($module=$owner->getModule($id))!==null)
                return $this->createController($route,$module);

            $basePath=$owner->getControllerPath();

            $className=ucfirst($id).'Controller';

            $classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';

            if($owner->controllerNamespace!==null) {
                $className=$owner->controllerNamespace.'\\'.$className;
                $id[0]=strtolower($id[0]);
                return array(
                    new $className($id,$owner===$this?null:$owner),
                    $this->parseActionParams($route),
                );
            }

            if(!class_exists($className, false))
                require($classFile);
            if(class_exists($className,false) && is_subclass_of($className,'CController'))
            {
                $id[0]=strtolower($id[0]);
                return array(
                    new $className($id,$owner===$this?null:$owner),
                    $this->parseActionParams($route),
                );
            }
        }
        return null;
    }


}
