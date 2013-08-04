<?php

namespace Restyii\Action\Root;

use Restyii\Utils\String;

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
        $app = \Yii::app(); /* @var \Restyii\Web\Application $app */

        $controller = $this->getController();
        $module = $controller->getModule();
        if (!$module)
            $module = $app;

        $data = array(
            'name' => $module->name,
            'description' => $module->getDescription(),
            '_links' => $this->createLinks(),
        );
        return array(200, $data);
    }

    /**
     * Create the links for resources in the resource root
     * @return array the links, name => config
     */
    protected function createLinks()
    {
        $app = \Yii::app(); /* @var \Restyii\Web\Application $app */

        $controller = $this->getController();
        $module = $controller->getModule();
        if (!$module)
            $module = $app;

        $controllers = $app->getSchema()->getControllerInstances($module); /* @var \Restyii\Controller\Base[]|\CController[] $controllers */

        $links = array(
            'self' => array(
                'title' => $module->name,
                'href' => trim($app->getBaseUrl(), '/').'/',
            ),
        );
        foreach($controllers as $id => $controller) {
            if ($id === $module->defaultController)
                continue;

           $links[$id] = array(
                'title' =>  method_exists($controller, 'classLabel') ? $controller->classLabel(true) : String::pluralize(String::humanize(substr(get_class($controller), 0, -10))),
                'href' => $controller->createUrl('search'),
            );
            if (method_exists($controller, 'classDescription'))
               $links[$id]['description'] = $controller->classDescription();
            if (isset($controller->modelClass))
               $links[$id]['profile'] = array($controller->modelClass);
        }

        return $links;
    }
}
