<?php


namespace Restyii\Web\Form;

use CActiveRecord;
use CHtml;
use CJavaScriptExpression;
use CModel;

class ActiveForm extends \CActiveForm
{
    /**
     * @var bool whether or not form field names should be qualified
     * using the model name as a prefix (default Yii behavior).
     */
    public $qualifyNames = true;

    /**
     * Gets the appropriate itemprop name for an attribute
     * @param string $attribute the attribute name
     *
     * @return string the itemprop name
     */
    public function getItemPropName($attribute)
    {
        if (preg_match('/^(.*)\[(.+)?\]$/', $attribute, $matches))
            return trim($matches[1]);
        else
            return $attribute;
    }

    /**
     * @inheritDoc
     */
    public function textField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::textField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function error($model, $attribute, $htmlOptions = array(), $enableAjaxValidation = true, $enableClientValidation = true)
    {
        if (!$this->qualifyNames && !isset($htmlOptions['inputID']))
            $htmlOptions['inputID'] = \CHtml::getIdByName($attribute);
        if (!isset($htmlOptions['itemerror']))
            $htmlOptions['itemerror'] = $attribute;
        return parent::error($model, $attribute, $htmlOptions, $enableAjaxValidation, $enableClientValidation);
    }

    /**
     * @inheritDoc
     */
    public function label($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['for']))
            $htmlOptions['for'] = \CHtml::getIdByName($attribute);
        return parent::label($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function labelEx($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['for']))
            $htmlOptions['for'] = \CHtml::getIdByName($attribute);
        return parent::labelEx($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function urlField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::urlField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function emailField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::emailField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function numberField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::numberField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function rangeField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::rangeField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function dateField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        if (!isset($htmlOptions['value']) && $model->{$attribute} instanceof \DateTime)
            $htmlOptions['value'] = $model->{$attribute}->format('Y-m-d');
        return parent::dateField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function timeField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        if (!isset($htmlOptions['value']) && $model->{$attribute} instanceof \DateTime)
            $htmlOptions['value'] = $model->{$attribute}->format('H:i:s');
        return parent::timeField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function dateTimeField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        if (!isset($htmlOptions['value']) && $model->{$attribute} instanceof \DateTime)
            $htmlOptions['value'] = $model->{$attribute}->format('Y-m-d\\TH:i:s');
        return parent::dateTimeField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function dateTimeLocalField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        if (!isset($htmlOptions['value']) && $model->{$attribute} instanceof \DateTime)
            $htmlOptions['value'] = $model->{$attribute}->format('Y-m-d\\TH:i:s');
        return parent::dateTimeLocalField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function weekField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::weekField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function colorField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::colorField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function telField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::telField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function searchField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::searchField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function hiddenField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::hiddenField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function passwordField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::passwordField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function textArea($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::textArea($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function fileField($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::fileField($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function radioButton($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::radioButton($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function checkBox($model, $attribute, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        return parent::checkBox($model, $attribute, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function dropDownList($model, $attribute, $data, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::dropDownList($model, $attribute, $data, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function listBox($model, $attribute, $data, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::listBox($model, $attribute, $data, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function checkBoxList($model, $attribute, $data, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::checkBoxList($model, $attribute, $data, $htmlOptions);
    }

    /**
     * @inheritDoc
     */
    public function radioButtonList($model, $attribute, $data, $htmlOptions = array())
    {
        if (!$this->qualifyNames && !isset($htmlOptions['name']))
            $htmlOptions['name'] = $attribute;
        if (!isset($htmlOptions['itemprop']))
            $htmlOptions['itemprop'] = $this->getItemPropName($attribute);
        return parent::radioButtonList($model, $attribute, $data, $htmlOptions);
    }

}
