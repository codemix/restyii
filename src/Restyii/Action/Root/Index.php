<?php

namespace Restyii\Action\Root;

class Index extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "GET";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Resource Root");
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        return \Yii::t('resource', "Displays the resources under the resource root.");
    }

    /**
     * Presents the action without performing it.
     * This is used to e.g. show a html form to a user so that they can
     * enter data and *then* perform the action.
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function present($userInput, $loaded = null)
    {
        return $this->perform($userInput, $loaded);
    }

    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        $app = \Yii::app();
        $controller = $this->getController();
        $module = $controller->getModule();
        if (!$module) {
            $module = $app;
            $baseRoute = '';
        }
        else
            $baseRoute = $module->getId().'/';

        $controllers = $this->getControllerInstances($module);
        $data = array(
            'name' => $module->name,
            '_links' => array(),
        );
        foreach($controllers as $id) {
            $data['_links'][$id] = array(
                'href' => $app->createUrl($baseRoute.$id.'/search'),
            );
        }
        return array(200, $data);
    }

    /**
     * @param \CWebApplication|\CModule $module
     *
     * @return array
     */
    protected function getControllerInstances(\CModule $module)
    {
        $items = array();
        $files = \CFileHelper::findFiles($module->getControllerPath(), array('fileTypes' => array('php', 'level' => 0)));
        $controllerNames = array_keys($module->controllerMap);

        foreach($files as $file) {
            if (substr($file,-14, 14) !== 'Controller.php')
                continue;
            $id = lcfirst(substr(basename($file), 0, -14));
            if ($id !== 'default' && !in_array($id, $controllerNames))
                $controllerNames[] = $id;
        }
        return $controllerNames;
    }
}
