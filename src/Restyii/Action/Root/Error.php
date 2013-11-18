<?php

namespace Restyii\Action\Root;

use Restyii\Utils\String;

class Error extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = null;

    /**
     * @var string the name of the view to render for this action
     */
    protected $_viewName = "//default/error";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Resource Error");
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        return \Yii::t('resource', "Displays information about an error.");
    }

    /**
     * Runs the action
     */
    public function run()
    {
        $controller = $this->getController();
        $controller->setPageTitle($this->label());
        $userInput = $this->getUserInput();
        $result = $this->perform($userInput);
        call_user_func_array(array($this, 'respond'), $result);
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
        $errorHandler = $app->getErrorHandler();
        $app->getRequest()->getResponse()->setViewName($this->getViewName());
        if ($error = $errorHandler->getError()) {
            if ($exception = $errorHandler->getException())
                return array($error['code'], $exception);
            else
                return array($error['code'], array(
                    'code' => $error['code'],
                    'type' => $error['type'],
                    'message' => $error['message'],
                ));
        }
        else
            return array(500, new \Exception('Internal Server Error'));
    }

}
