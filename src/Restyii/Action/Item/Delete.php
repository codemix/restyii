<?php

namespace Restyii\Action\Item;

/**
 * RESTful 'Delete' Action
 */
class Delete extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "DELETE";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Delete {resourceLabel}", array(
            '{resourceLabel}' => $this->staticModel()->label()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Delete the specified {resourceLabel}.", array(
            '{resourceLabel}' => $model->label(),
        ));
    }


    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @throws \CHttpException if the resource could not be deleted
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if ($loaded === null)
            $loaded = $this->load();
        if ($this->delete($loaded))
            return array(204, $loaded);
        else if ($loaded->hasErrors())
            return array(405, $loaded);
        else
            throw new \CHttpException(500, \Yii::t('resource','Could not delete the specified resource.'));
    }


}
