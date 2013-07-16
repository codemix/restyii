<?php

namespace Restyii\Action\Root;

class Guzzle extends Base
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
        return \Yii::t('resource', "Guzzle Service Description");
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        return \Yii::t('resource', "Displays the guzzle service description.");
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
        $request = $app->getRequest(); /* @var \Restyii\Web\Request $request */
        $request->getResponse()->prettyPrint = true;
        return array(200, $app->getSchema()->getServiceDescription());
    }

}
