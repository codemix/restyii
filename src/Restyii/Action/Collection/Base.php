<?php

namespace Restyii\Action\Collection;

abstract class Base extends \Restyii\Action\Base
{
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
