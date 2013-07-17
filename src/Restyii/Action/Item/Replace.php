<?php

namespace Restyii\Action\Item;

/**
 * RESTful 'Replace' Action
 */
class Replace extends Base implements \Restyii\Action\SingleTargetInterface
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "PUT";

    /**
     * @var \Restyii\CacheHelper\Base|bool the action cache
     */
    protected $_cache = false;


    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "Replace {resourceLabel}", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Replace the specified {resourceLabel}.", array(
            '{resourceLabel}' => $model->classLabel(),
        ));
    }

    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @throws \CHttpException if any existing resource could not be deleted.
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if ($userInput === false)
            return array(400, $loaded);

        if ($loaded === null) {
            $finder = $this->staticModel();
            $pk = $this->getPkFromContext($finder);
            if ($pk === null)
                throw new \CHttpException(400, \Yii::t('resource', 'No resource specified'));
            $loaded = $finder->findByPk($pk);
        }
        else
            $pk = $loaded->getPrimaryKey();

        if (is_object($loaded) && !$loaded->getIsNewRecord() && !$this->delete($loaded)) {
            if ($loaded->hasErrors())
                return array(405, $loaded);
            else
                throw new \CHttpException(500, \Yii::t('resource', "Could not delete the existing resource."));
        }

        $model = $this->instantiateResourceModel('create');
        $model->setPrimaryKey($pk);

        if ($this->applyUserInput($userInput, $model) && $this->save($model))
            return array(201, $model);
        else
            return array(400, $model);
    }


}
