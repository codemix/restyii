<?php

namespace Restyii\Action;

use Restyii\Model\ModelInterface;
use Restyii\Model\ActiveRecord;


/**
 * Base class for RESTful actions.
 */
abstract class Base extends \CAction
{
    /**
     * @var string the HTTP verb for this resource action.
     */
    public $verb = 'GET';

    /**
     * @var string the name of the scenario for the action
     */
    protected $_scenario;

    /**
     * @var string the base resource model class
     */
    protected $_modelClass;

    /**
     * @var \CDbCriteria the criteria to use with this action, if any.
     */
    protected $_criteria;


    /**
     * Return a label for the action.
     * @return string the label for the action
     */
    public function label()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', '{actionName} {collectionLabel}', array(
            '{actionName}' => $model->generateAttributeLabel($this->getId()),
            '{collectionLabel}' => $model->collectionLabel()
        ));
    }

    /**
     * Return a short description of the action.
     * @return string a short description of the action.
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', '{pluralActionName} {collectionLabel}', array(
            '{pluralActionName}' => $model->pluralize($model->generateAttributeLabel($this->getId())),
            '{collectionLabel}' => $model->collectionLabel()
        ));
    }

    /**
     * Returns the appropriate static model instance for this resource
     * @param string|null $className the class name
     *
     * @return ModelInterface|ActiveRecord the resource model
     */
    public function staticModel($className = null)
    {
        if ($className === null)
            $className = $this->getModelClass();
        return $className::model();
    }

    /**
     * Sets the scenario to use when dealing with models in this action
     * @param string $scenario the scenario name
     */
    public function setScenario($scenario)
    {
        $this->_scenario = $scenario;
    }

    /**
     * Gets the scenario to use when dealing with models in this action
     * @return string the scenario name
     */
    public function getScenario()
    {
        if ($this->_scenario === null)
            $this->_scenario = $this->getId();
        return $this->_scenario;
    }



    /**
     * Set the DB criteria to use with this action
     * @param \CDbCriteria $criteria
     */
    public function setCriteria($criteria)
    {
        $this->_criteria = $criteria;
    }

    /**
     * Get the DB criteria to use with this action
     * @return \CDbCriteria
     */
    public function getCriteria()
    {
        if ($this->_criteria === null)
            $this->_criteria = new \CDbCriteria();
        return $this->_criteria;
    }

    /**
     * Sets the name of the resource model class to use with this action
     * @param string $modelClass
     */
    public function setModelClass($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    /**
     * Gets the name of the model class to use with this action
     * @return string
     */
    public function getModelClass()
    {
        if ($this->_modelClass === null) {
            $controller = $this->getController();  /* @var \Restyii\Controller\Model $controller */
            $this->_modelClass = $controller->getModelClass();
        }
        return $this->_modelClass;
    }

    /**
     * Gets the model primary key from the `$_GET` parameters.
     * Returns null if the required parameters do not exist.
     *
     * @param ActiveRecord $finder the model to get the pk values for
     * @param array $context the context to load from, defaults to $_GET
     *
     * @return mixed|null the pk, or null if the required params are not present
     */
    public function getPkFromContext(ActiveRecord $finder, $context = null)
    {
        if ($context === null)
            $context = $_GET;
        $pkName = $finder->getTableSchema()->primaryKey;
        if (is_array($pkName)) {
            $pk = array();
            foreach($pkName as $name) {
                if (!isset($context[$name]))
                    return null;
                $pk[$name] = $context[$name];
            }
        }
        else {
            if (!isset($context[$pkName]))
                return null;
            $pk = $context[$pkName];
        }
        return $pk;
    }


    /**
     * Loads the appropriate resource for the current request.
     * @param mixed|null $pk the primary key to load, defaults to null meaning use the GET parameters.
     * @param ActiveRecord|null $finder the model to use when finding the resource
     * @return ActiveRecord the loaded resource model
     * @throws \CHttpException if the request doesn't contain the right parameters or the resource does not exist
     */
    protected function load($pk = null, $finder = null)
    {
        if ($finder === null)
            $finder = $this->staticModel();
        if ($pk === null)
            $pk = $this->getPkFromContext($finder);
        if ($pk === null)
            throw new \CHttpException(401, 'Invalid Request, missing parameter(s).');
        $model = $finder->findByPk($pk);
        if (!is_object($model))
            throw new \CHttpException(404, 'The specified resource cannot be found.');
        return $model;
    }

    /**
     * Save a particular model
     *
     * @param ActiveRecord $model the model being saved
     * @param bool $runValidation whether or not to run validation
     *
     * @return bool true if save was successful, otherwise false.
     */
    protected function save(ActiveRecord $model, $runValidation = true)
    {
        return $model->save($runValidation);
    }

    /**
     * Delete a particular model
     *
     * @param ActiveRecord $model the model to delete
     *
     * @return bool true if the delete was successful, otherwise false.
     */
    protected function delete(ActiveRecord $model)
    {
        return $model->delete();
    }


    /**
     * Declares the required  and optional HTTP headers for the action.
     * @return array the headers. name => config
     */
    public function requestHeaders()
    {
        return array();
    }


    /**
     * Declares the required and optional GET parameters for the action.
     * @return array the parameters, name => config
     */
    public function params()
    {
        return array();
    }

    /**
     * Runs the action
     */
    public function run()
    {
        $userInput = $this->getUserInput();
        $requestType = \Yii::app()->getRequest()->getRequestType();
        if ($requestType != $this->verb) {
            if ($requestType == 'GET')
                $result = $this->present($userInput);
            else
                throw new \CHttpException(405, \Yii::t('resource', "{actionLabel} does not support the '{methodName}' method.",
                    array('{actionLabel}' => $this->label(), '{methodName}' => $requestType)));
        }
        else
            $result = $this->perform($userInput);

        call_user_func_array(array($this, 'respond'), $result);
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
    abstract public function present($userInput, $loaded = null);

    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    abstract public function perform($userInput, $loaded = null);

    /**
     * Instantiates a resource model for this action.
     * @param string|null the scenario to use
     * @return ActiveRecord the instantiated model
     */
    protected function instantiateResourceModel($scenario = null)
    {
        $modelClass = $this->getModelClass();
        return new $modelClass($scenario === null? $this->getScenario() : $scenario);
    }

    /**
     * Gets the user input, if any, or returns false if there is none.
     * @return array|bool the user input, or false if none is present
     */
    protected function getUserInput()
    {
        $app = \Yii::app();
        $request = $app->getRequest(); /* @var \Restyii\Web\Request $request */
        return $request->getUserInput();

    }

    /**
     * Applies the user input to a given model. Child classes should
     * override this if they need to do anything special.
     *
     * @param array $input the user input
     * @param ModelInterface $model the model to apply the input to
     * @return bool true if the input was applied successfully, note this is not
     * the same as validation.
     */
    protected function applyUserInput($input, ModelInterface $model)
    {
        $model->setAttributes($input);
        return true;
    }

    /**
     * Respond to a request
     *
     * @param int $code the http response code
     * @param mixed|null $data the data to respond with
     * @param array|null $headers the extra headers to send
     * @param bool $terminateApplication whether or not to terminate the application
     */
    protected function respond($code, $data = null, $headers = null, $terminateApplication = true)
    {
        $controller = $this->getController(); /* @var \Restyii\Controller\Base $controller */
        $controller->respond($code, $data, $headers, $terminateApplication);
    }

}
