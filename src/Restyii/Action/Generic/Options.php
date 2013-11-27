<?php

namespace Restyii\Action\Generic;

use Restyii\Action\MultipleTargetInterface;
use Restyii\Action\SingleTargetInterface;
use \Restyii\Model\ActiveRecord;

class Options extends Base
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "OPTIONS";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "{resourceLabel} Options", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Displays the options for {collectionLabel}.", array(
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

        $model = $this->staticModel();
        $actions = $this->getActionInstances();
        $pk = $this->getPkFromContext($model);
        if ($pk !== null)
            return $this->performItem($actions, $model, $pk);
        else
            return $this->performCollection($actions, $model);
    }

    /**
     * Perform the action on an individual item.
     *
     * @param array $actions the list of existing actions
     * @param ActiveRecord $model the model to perform the action on
     * @param mixed $pk the primary key of the resource
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    protected function performItem($actions, ActiveRecord $model, $pk)
    {
        $model->setScenario('read');
        $itemActions = array();
        $collectionActions = array();
        foreach($actions as $name => $action) {
            if ($action instanceof MultipleTargetInterface)
                $collectionActions[$name] = $this->prepareAction($action);
            else if ($action instanceof SingleTargetInterface)
                $itemActions[$name] = $this->prepareAction($action);
            else
                unset($actions[$name]);
        }
        $attributes = $this->getAttributeConfigs($model);
        return array(200, array(
            'label' => $model->classLabel(),
            'collectionLabel' => $model->classLabel(true),
            'attributes' => $attributes,
            'itemActions' => $itemActions,
            'collectionActions' => $collectionActions,
        ));
    }

    /**
     * Perform the action on a collection of items.
     *
     * @param array $actions the list of existing actions
     * @param ActiveRecord $model the model to perform the action on
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    protected function performCollection($actions, ActiveRecord $model)
    {
        $model->setScenario('create');
        $itemActions = array();
        $collectionActions = array();
        foreach($actions as $name => $action) {
            if ($action instanceof MultipleTargetInterface)
                $collectionActions[$name] = $this->prepareAction($action);
            else if ($action instanceof SingleTargetInterface)
                $itemActions[$name] = $this->prepareAction($action);
            else
                unset($actions[$name]);
        }
        $attributes = $this->getAttributeConfigs($model);
        return array(200, array(
            'label' => $model->classLabel(),
            'collectionLabel' => $model->classLabel(true),
            'attributes' => $attributes,
            'itemActions' => $itemActions,
            'collectionActions' => $collectionActions,
        ));
    }

    /**
     * Gets the configurations for the given model's  attributes
     * @param ActiveRecord $model the active record to get attributes for
     *
     * @return array the attribute config
     */
    protected function getAttributeConfigs(ActiveRecord $model)
    {
        $attributes = array();
        $pk = $model->getTableSchema()->primaryKey;
        foreach($model->getVisibleAttributeNames() as $attribute) {
            $validators = array();
            foreach($model->getValidators($attribute) as $validator /* @var \CValidator $validator */) {
                if ($validator->enableClientValidation && ($js = $validator->clientValidateAttribute($model, $attribute)) !== null)
                    $validators[] = $js;
            }
            if (is_array($pk))
                $isPk = in_array($attribute, $pk);
            else
                $isPk = $attribute == $pk;
            $attributes[$attribute] = array(
                'label' => $model->getAttributeLabel($attribute),
                'description' => $model->getAttributeDescription($attribute),
                'primitive' => $model->getAttributePrimitive($attribute),
                'type' => $model->getAttributeType($attribute),
                'format' => $model->getAttributeFormat($attribute),
                'input' => $model->getAttributeInput($attribute),
                'validators' => $validators,
                'isWritable' => $model->isAttributeSafe($attribute),
                'isRequired' => $model->isAttributeRequired($attribute),
                'isPrimaryKey' => (bool) $isPk,
            );
        }
        return $attributes;
    }

    /**
     * Prepare the given action
     * @param \Restyii\Action\Base $action
     *
     * @return array
     */
    protected function prepareAction(\Restyii\Action\Base $action)
    {
        return array(
            'label' => $action->label(),
            'description' => $action->description(),
            'verb' => is_array($action->verb) ? $action->verb[0] : $action->verb,
            'params' => $action->params(),
            'headers' => $action->requestHeaders(),
            'link' => array(
                'href' => $action->createUrlTemplate(),
                'templated' => true,
                'title' => $action->label(),
            ),
        );
    }
}
