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
        return $params;
    }


}
