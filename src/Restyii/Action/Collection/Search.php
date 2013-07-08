<?php

namespace Restyii\Action\Collection;

class Search extends Base
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
        return \Yii::t('resource', "Search {collectionLabel}", array(
            '{collectionLabel}' => $this->staticModel()->collectionLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Searches for {collectionLabel} that optionally match a given filter.", array(
            '{resourceLabel}' => $model->label(),
            '{collectionLabel}' => $model->collectionLabel(),
        ));
    }

    public function params()
    {
        return array(
            'q' => array(
                'label' => \Yii::t('resource', 'Query'),
                'description' => \Yii::t('resource', 'The search query text'),
                'required' => false,
                'type' => 'string',
            )
        );
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
        if ($loaded === null)
            $loaded = $this->instantiateResourceModel();
        $dataProvider = $loaded->search();
        return array(200, $dataProvider);
    }


}
