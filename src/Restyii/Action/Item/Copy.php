<?php

namespace Restyii\Action\Item;

/**
 * RESTful 'Update' Action
 */
class Copy extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "COPY";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Copy {resourceLabel}", array(
            '{resourceLabel}' => $this->staticModel()->label()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Copies the {resourceLabel} to another location.", array(
            '{resourceLabel}' => $model->label(),
        ));
    }

    public function requestHeaders()
    {
        return array(
            'Destination' => array(
                'label' => \Yii::t('resource', "Destination"),
                'description' => \Yii::t('resource', "The destination URL to copy the resource to."),
                'required' => true,
            ),
        );
    }


    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @throws \CHttpException if the required destination header is not present
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if (isset($_SERVER['HTTP_DESTINATION']))
            $destination = $_SERVER['HTTP_DESTINATION'];
        else if(isset($_POST['_destination']))
            $destination = $_POST['_destination'];
        else
            throw new \CHttpException(400, \Yii::t('resource', "Invalid request, no 'Destination' header present."));

        if ($loaded === null)
            $loaded = $this->load();

        list($action, $params) = $this->getTargetAction($destination, 'PUT'); /* @var Base $action */
        $target = $action->instantiateResourceModel();
        $pk = $action->getPkFromContext($target, $params);
        $existing = $action->load($pk);
        if (!is_object($existing)) {
            $existing = $action->instantiateResourceModel('delete');
            $existing->setPrimaryKey($pk);
        }
        if ($userInput === false)
            $userInput = $loaded->getAttributes($loaded->getSafeAttributeNames());

        return $action->perform($userInput, $existing);
    }

    protected function getTargetAction($url, $verb)
    {
        $app = \Yii::app(); /* @var \CWebApplication  $app */
        $urlManager = $app->getUrlManager();
        $params = $urlManager->parseUrl($url, $verb);
        if ($params === false)
            throw new \CHttpException(400, \Yii::t('resource',"Invalid destination specified."));
        $route = array_shift($params);
        list($controller, $actionID) = $app->createController($route); /* @var \CController $controller */
        return array($controller->createAction($actionID), $params);
    }

}
