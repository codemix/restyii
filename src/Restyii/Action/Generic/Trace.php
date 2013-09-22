<?php

namespace Restyii\Action\Generic;

use Restyii\Action\MultipleTargetInterface;
use Restyii\Action\SingleTargetInterface;
use \Restyii\Model\ActiveRecord;

class Trace extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "TRACE";

    /**
     * @var bool whether the HTTP verb should be strictly enforced
     */
    public $enforceVerb = false;

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "{resourceLabel} Trace", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Traces a request for {collectionLabel}.", array(
            '{collectionLabel}' => $model->classLabel(true),
        ));
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
        return array(200, $this->getTrace($userInput, $loaded));
    }

    /**
     * Traces the request
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function getTrace($userInput, $loaded = null)
    {
        $trace = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'headers' => getallheaders(),
            'params' => $_GET,
            'input' => $userInput,
            'route' => $this->getController()->getRoute(),
        );

        return $trace;
    }

}
