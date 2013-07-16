<?php

namespace Restyii\Action\Relation;

use Restyii\Model\ActiveRecord;

class Search extends Base implements \Restyii\Action\MultipleInterface
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
        return \CMap::mergeArray(parent::params(), array(
            'q' => array(
                'label' => \Yii::t('resource', 'Query'),
                'description' => \Yii::t('resource', 'The search query text'),
                'required' => false,
                'type' => 'string',
            )
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
        if ($loaded === null)
            $relatedModel = $this->instantiateRelatedModel();
        else
            $relatedModel = $loaded;

        $owner = $this->getOwnerModel();

        $params = $this->getParams();
        $criteria = $this->createRelationCriteria($owner, $relatedModel);
        $criteria->mergeWith($this->createEmbedCriteria($relatedModel));
        $relatedModel->getDbCriteria()->mergeWith($criteria);
        $dataProvider = $relatedModel->search($params);
        return array(200, $dataProvider);
    }






}
