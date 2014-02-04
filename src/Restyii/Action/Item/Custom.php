<?php
namespace Restyii\Action\Item;

/**
 * Custom
 *
 * A custom class that operates on items.
 */
class Custom extends Base implements \Restyii\Action\SingleTargetInterface
{
    /**
     * @var function|null a closure function to call when presenting the action
     */
    public $present;

    /**
     * @var function|null a closure function to call when peforming the action
     */
    public $perform;

    /**
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function present($userInput, $loaded = null)
    {
        if(is_callable($this->present)) {
            $present = $this->present->bindTo($this, $this);
            return $present($userInput, $loaded);
        } else {
            throw new \CException(\Yii::t('resource', "Custom {actionLabel} is missing a closure function for 'present'",
                array('{actionLabel}' => $this->label())));
        }
    }

    /**
     * @param array|boolean $userInput the user input
     * @param mixed|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if(is_callable($this->perform)) {
            $perform = $this->perform->bindTo($this, $this);
            return $perform($userInput, $loaded);
        } else {
            throw new \CException(\Yii::t('resource', "Custom {actionLabel} is missing a closure function for 'perform'",
                array('{actionLabel}' => $this->label())));
        }
    }
}
