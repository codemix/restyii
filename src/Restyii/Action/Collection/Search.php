<?php

namespace Restyii\Action\Collection;

class Search extends Base implements \Restyii\Action\MultipleTargetInterface
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
            '{collectionLabel}' => $this->staticModel()->classLabel(true)
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Searches for {collectionLabel} that optionally match a given filter.", array(
            '{resourceLabel}' => $model->classLabel(),
            '{collectionLabel}' => $model->classLabel(true),
        ));
    }

    /**
     * @inheritDoc
     */
    public function params()
    {
        return array(
            'q' => array(
                'label' => \Yii::t('resource', 'Query'),
                'description' => \Yii::t('resource', 'The search query text'),
                'required' => false,
                'type' => 'string',
            ),
            'filter' => array(
                'label' => \Yii::t('resource', 'Filter'),
                'description' => \Yii::t('resource', 'A list of attributes that will be used to filter results.'),
                'required' => false,
                'type' => 'array',
            ),
            'page' => array(
                'label' => \Yii::t('resource', 'Page'),
                'description' => \Yii::t('resource', 'The number of the page'),
                'required' => false,
                'type' => 'integer',
            ),
            'limit' => array(
                'label' => \Yii::t('resource', 'Limit'),
                'description' => \Yii::t('resource', 'The number of results to return per page'),
                'required' => false,
                'type' => 'integer',
            ),
            '_embed' => array(
                'label' => \Yii::t('resource', 'Embed'),
                'description' => \Yii::t('resource', 'The comma separated names of the links to embed in the results.'),
                'type' => 'string',
            ),
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
        $params = $this->getParams();

        $dataProvider = $this->search($loaded, $params);
        return array(200, $dataProvider);
    }


}
