<?php

namespace Restyii\Action\Collection;

class Bulk extends Base implements \Restyii\Action\MultipleInterface
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "POST";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Bulk {collectionLabel} Actions", array(
            '{collectionLabel}' => $this->staticModel()->classLabel(true)
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Performs bulk actions on {collectionLabel}.", array(
            '{resourceLabel}' => $model->classLabel(),
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
     * @throws \CHttpException if the request is invalid
     * @return array an array of parameters to send to the `respond()` method
     */
    public function present($userInput, $loaded = null)
    {
        throw new \CHttpException(501, \Yii::t('resource', 'Not Implemented'));
    }

    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @throws \CHttpException if the request is invalid
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        throw new \CHttpException(501, \Yii::t('resource', 'Not Implemented'));
    }




}
