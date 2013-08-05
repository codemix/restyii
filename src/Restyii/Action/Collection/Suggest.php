<?php

namespace Restyii\Action\Collection;

use Restyii\Model\ActiveRecord;
use Restyii\Model\ModelInterface;

class Suggest extends Base implements \Restyii\Action\MultipleTargetInterface
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "GET";

    /**
     * @var string[] the names of the attributes that should be auto-completed
     */
    protected $_attributes;

    /**
     * Sets the names of the attributes that should be auto-completed
     * @param \string[] $attributes the attribute names
     */
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * Gets the names of the attributes that should be auto-completed
     * @return \string[] the attribute names
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = array();
            $model = $this->staticModel();
            foreach($model->getVisibleAttributeNames() as $attribute) {
                if ($model->getAttributePrimitive($attribute) == 'string')
                    $this->_attributes[] = $attribute;
            }
        }
        return $this->_attributes;
    }

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Suggest {collectionLabel}", array(
            '{collectionLabel}' => $this->staticModel()->classLabel(true)
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Suggests {collectionLabel} based on user input.", array(
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
            'term' => array(
                'label' => \Yii::t('resource', 'Term'),
                'description' => \Yii::t('resource', 'The term to auto-complete.'),
                'required' => true,
                'type' => 'string',
            ),
            'limit' => array(
                'label' => \Yii::t('resource', 'Limit'),
                'description' => \Yii::t('resource', 'The number of suggestions to return'),
                'required' => false,
                'type' => 'integer',
                'default' => 5,
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
        $loaded->getDbCriteria()->mergeWith($this->createSuggestCriteria($loaded, $params));
        $dataProvider = $this->search($loaded, $params);
        return array(200, $dataProvider);
    }


    /**
     * Creates the suggestion criteria for a given model
     *
     * @param ActiveRecord $model the model to suggest for
     * @param array $params the action parameters
     *
     * @return \CDbCriteria the criteria to merge
     */
    protected function createSuggestCriteria(ActiveRecord $model, $params)
    {
        $criteria = new \CDbCriteria();
        $table = $model->getTableAlias(false, false);
        $conditions = array();
        foreach($this->getAttributes() as $attribute)
            $conditions[] = $table.'.'.$attribute.' LIKE :term';
        $criteria->params[':term'] = $params['term'].'%';
        $criteria->addCondition(implode(' OR ', $conditions));
        $criteria->limit = $params['limit'];
        return $criteria;
    }
}
