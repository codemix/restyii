<?php

namespace Restyii\Meta;


use Restyii\Action\MultipleTargetInterface;
use Restyii\Action\SingleTargetInterface;

class Schema extends \CApplicationComponent
{

    /**
     * @var array the schema data
     */
    protected $_data = array();

    /**
     * @var array the service description
     */
    protected $_serviceDescription;

    /**
     * @return array
     */
    public function getServiceDescription()
    {
        if ($this->_serviceDescription === null)
            $this->_serviceDescription = $this->createServiceDescription();
        return $this->_serviceDescription;
    }


    protected function createServiceDescription()
    {
        $app = \Yii::app(); /* @var \Restyii\Web\Application $app */
        $desc = array(
            'name' => $app->name,
            'description' => $app->getDescription(),
            'baseUrl' => $app->getBaseUrl(true),
            'operations' => array(),
            'models' => array(),
        );
        $schema = $this->getModuleSchema($app);
        $modelClasses = array();
        foreach($schema['controllers'] as $controllerId => $controllerConfig) {
            $controller = $controllerConfig['instance']; /** @var \Restyii\Controller\Base $controller */
            foreach($controllerConfig['actions'] as $actionId => $action /* @var \Restyii\Action\Base $action */) {
                if (!($action instanceof MultipleTargetInterface) && !($action instanceof SingleTargetInterface))
                    continue;
                $modelClass = $action->getModelClass();
                if (!isset($modelClasses[$modelClass]))
                    $modelClasses[$modelClass] = $action->staticModel();
                $desc['operations'][$actionId.ucfirst($controllerId)] = array(
                    'httpMethod' => $action->verb,
                    'uri' => $action->createUrlTemplate(),
                    'summary' => $action->description(),
                    'responseClass' => $action instanceof MultipleTargetInterface ? $modelClass.'Collection' : $modelClass,
                    'parameters' => array_map(
                        function($config) {
                            return array(
                                'location' => 'uri',
                                'description' => !empty($config['description']) ? $config['description'] : '',
                                'required' => !empty($config['required']),
                            );
                        },
                        $action->params()
                    ),
                );
            }
        }
        foreach($modelClasses as $name => $model /* @var \Restyii\Model\ActiveRecord $model */) {
            $config = array(
                'type' => 'object',
                'properties' => array(),
            );
            foreach($model->getVisibleAttributeNames() as $attribute) {
                $config['properties'][$attribute] = array(
                    'location' => 'json',
                    'type' => $model->getAttributeType($attribute),
                );
            }
            $desc['models'][$name] = $config;
            $desc['models'][$name.'Collection'] = array('type' => 'array', 'items' => $config);

        }
        return $desc;
    }


    public function getModuleSchema($parentModule = null)
    {
        if ($parentModule === null)
            $parentModule = \Yii::app();
        $uniqueId = $this->getModuleUniqueId($parentModule);
        if (!isset($this->_data[$uniqueId]))
            $this->_data[$uniqueId] = array();
        $this->getModuleInstances($parentModule);
        foreach($this->getControllerInstances($parentModule) as $id => $controller) {
            $this->getActionInstances($controller);
        }

        return $this->_data[$uniqueId];
    }


    /**
     * Gets the unique id for a given module
     *
     * @param \CWebApplication|\CWebModule $module the parent module or application
     *
     * @return string the unique id for the module
     */
    public function getModuleUniqueId($module)
    {
        $parts = array();
        $parent = $module->getParentModule();
        if (is_object($parent))
            $parts[] = $this->getModuleUniqueId($parent);
        return implode('/', $parts);
    }

    /**
     * Gets the module instances for the given parent module or application
     *
     * @param \CWebApplication|\CWebModule $parentModule the parent module or application
     *
     * @return \CWebModule[] the modules
     */
    public function getModuleInstances($parentModule = null)
    {
        $uniqueId = $this->getModuleUniqueId($parentModule);
        if (!isset($this->_data[$uniqueId]))
            $this->_data[$uniqueId] = array();

        if (!isset($this->_data[$uniqueId]['modules']))
            $this->_data[$uniqueId]['modules'] = array_map(
                function(\CWebModule $module) {
                    return array('instance' => $module);
                },
                $this->createModuleInstances($parentModule)
            );

        return array_map(
            function($module) {
                return $module['instance'];
            },
            $this->_data[$uniqueId]['modules']
        );
    }

    /**
     * Gets the controller instances for the given module or application
     *
     * @param \CWebApplication|\CWebModule $module
     *
     * @return \CController[] the controllers
     */
    public function getControllerInstances($module = null)
    {
        $uniqueId = $this->getModuleUniqueId($module);
        if (!isset($this->_data[$uniqueId]))
            $this->_data[$uniqueId] = array();
        if (!isset($this->_data[$uniqueId]['controllers']))
            $this->_data[$uniqueId]['controllers'] = array_map(
                function(\CController $controller) {
                    return array('instance' => $controller);
                },
                $this->createControllerInstances($module)
            );
        return array_map(
            function($controller) {
                return $controller['instance'];
            },
            $this->_data[$uniqueId]['controllers']
        );
    }

    /**
     * Gets the action instances for the given controller
     * @param \CController $controller the controller to get actions for
     *
     * @return \CAction[] the controller actions
     */
    public function getActionInstances(\CController $controller)
    {
        $controllerId = $controller->getId();
        $module = $controller->getModule();
        if (!is_object($module))
            $module = \Yii::app();
        $uniqueId = $this->getModuleUniqueId($module);
        if (!isset($this->_data[$uniqueId]))
            $this->_data[$uniqueId] = array();
        if (!isset($this->_data[$uniqueId]['controllers']))
            $this->getControllerInstances($module);
        if (!isset($this->_data[$uniqueId]['controllers'][$controllerId]['actions']))
            $this->_data[$uniqueId]['controllers'][$controllerId]['actions'] = $this->createActionInstances($controller);

        return $this->_data[$uniqueId]['controllers'][$controllerId]['actions'];
    }

    /**
     * Creates the module instances for the given parent module or application
     *
     * @param \CWebApplication|\CWebModule $parentModule the parent module or application
     *
     * @return \CWebModule[] the modules
     */
    protected function createModuleInstances($parentModule = null)
    {
        if ($parentModule === null)
            $parentModule = \Yii::app(); /* @var \CWebApplication $parentModule */
        $modules = array();
        foreach($parentModule->getModules() as $id => $config)
            $modules[$id] = $parentModule->getModule($id);
        return $modules;
    }

    /**
     * Creates the controller instances for the given module or application
     *
     * @param \CWebApplication|\CWebModule $module
     *
     * @return \CController[] the controllers
     */
    protected function createControllerInstances($module = null)
    {
        if ($module === null)
            $module = \Yii::app(); /* @var \CWebApplication $module */

        $controllerPath = $module->getControllerPath();
        $controllers = array();
        foreach($module->controllerMap as $id => $config) {
            $controllers[$id] = \Yii::createComponent($config);
        }

        foreach(\CFileHelper::findFiles($controllerPath, array('fileTypes' => array('php'), 'level' => 0)) as $file) {
            if (substr($file,-14, 14) !== 'Controller.php')
                continue;
            $id = lcfirst(substr(basename($file), 0, -14));
            if (isset($controllers[$id]))
                continue;
            $className = substr(basename($file), 0, -4);
            require_once $file;
            $controllers[$id] = new $className($id, $module instanceof \CWebApplication ? null : $module);
        }
        return $controllers;
    }


    /**
     * Creates the action instances for the given controller
     * @param \CController $controller the controller to get actions for
     *
     * @return \CAction[] the controller actions
     */
    protected function createActionInstances(\CController $controller)
    {
        $actions = array();
        foreach($controller->actions() as $id => $config)
            $actions[$id] = $controller->createAction($id);
        $reflector = new \ReflectionObject($controller);
        foreach($reflector->getMethods() as $name => $method) {
            if ($name != 'actions' && substr($name, 0, 6) == 'action') {
                $id = lcfirst(substr($name, 6));
                $actions[$id] = $controller->createAction($id);
            }
        }
        return $actions;
    }
}
