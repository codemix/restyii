<?php

namespace Restyii\Action\Item;

/**
 * RESTful 'Update' Action
 */
class Update extends Base implements \Restyii\Action\SingleTargetInterface
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "POST";

    /**
     * @var \Restyii\CacheHelper\Base|bool the action cache
     */
    protected $_cache = false;

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Update {resourceLabel}", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Update / edit the specified {resourceLabel}.", array(
            '{resourceLabel}' => $model->classLabel(),
        ));
    }


    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput = null, $loaded = null)
    {
        if ($loaded === null)
            $loaded = $this->load();
        if ($this->applyUserInput($userInput, $loaded) && $this->save($loaded))
            return array(200, $loaded);
        else
            return array(400, $loaded);
    }


}
