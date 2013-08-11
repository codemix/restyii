<?php

namespace Restyii\Action\Item;

use \Restyii\Model\ActiveRecord;

abstract class Base extends \Restyii\Action\Base
{
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
            $loaded = $this->load();
        return array(200, $loaded);
    }

    /**
     * @inheritDoc
     */
    public function params()
    {
        $model = $this->staticModel();
        $pk = $model->getTableSchema()->primaryKey;
        if (!is_array($pk))
            $pk = array($pk);

        $params = array();

        foreach($pk as $attribute)
            $params[$attribute] = array(
                'label' => $model->getAttributeLabel($attribute),
                'description' => $model->getAttributeDescription($attribute),
                'type' => $model->getAttributeType($attribute),
                'required' => true
            );
        $params['_embed'] = array(
            'label' => \Yii::t('resource', 'Embed'),
            'description' => \Yii::t('resource', 'The comma separated names of the links to embed in the results.'),
            'type' => 'string',
        );
        return $params;
    }

    /**
     * Gets the user input, if any, or returns false if there is none.
     * @return array|bool the user input, or false if none is present
     */
    protected function getUserInput()
    {
        $input = parent::getUserInput();
        $model = $this->staticModel();
        $modelClass = get_class($model);
        if (isset($input[$modelClass]) && count($input[$modelClass]))
            $input = $input[$modelClass];

        return $input;
    }

}
