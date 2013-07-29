<?php

namespace Restyii\Action\Relation;

/**
 * RESTful 'Create' Relation Action
 */
class Create extends Base implements \Restyii\Action\SingleTargetInterface
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
        return \Yii::t('resource', "Create {resourceLabel} Relation", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Creates a new relation for a given {resourceLabel}.", array(
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
     * @return array an array of parameters to send to the `respond()` method
     */
    public function present($userInput, $loaded = null)
    {
        if ($loaded === null)
            $loaded = $this->instantiateRelatedModel();
        return array(200, $loaded);
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
            $loaded = $this->instantiateRelatedModel();
        if ($this->applyUserInput($userInput, $loaded) && $this->save($loaded))
            return array(303, $loaded, array('Location' => $loaded->createUrl()));
        else
            return array(400, $loaded);
    }


}
