<?php

namespace Restyii\Action\Generic;

abstract class Base extends \Restyii\Action\Base
{
    /**
     * Gets the instances for the controller
     * @return \Restyii\Action\Base[] the action instances
     */
    protected function getActionInstances()
    {
        $controller = $this->getController(); /* @var \Restyii\Controller\Base $controller */
        $actions = array(
            $this->getId() => $this,
        );
        foreach($controller->actions() as $name => $config) {
            if ($name != $this->getId())
                $actions[$name] = $this->getActionInstance($name);
        }
        return $actions;
    }

    /**
     * Gets the named action instance.
     *
     * @param string $name the action name / id
     *
     * @return \Restyii\Action\Base|null $action the action, or null if no such action exists
     */
    protected function getActionInstance($name)
    {
        $controller = $this->getController(); /* @var \Restyii\Controller\Base $controller */
        $actions = $controller->actions();
        if (!isset($actions[$name]))
            return null;
        return $controller->createAction($name);
    }
}
