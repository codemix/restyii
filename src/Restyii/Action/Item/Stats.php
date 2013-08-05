<?php

namespace Restyii\Action\Item;
use Restyii\Action\SingleTargetInterface;

/**
 * RESTful 'Stats' Action
 */
class Stats extends Base implements SingleTargetInterface
{
    /**
     * @var string the HTTP verb for this action.
     */
    public $verb = "GET";

    /**
     * @inheritDoc
     */
    public function label()
    {
        return \Yii::t('resource', "{resourceLabel} Stats", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Gets the statistics for the given {resourceLabel}.", array(
            '{resourceLabel}' => $model->classLabel(),
        ));
    }

    /**
     * Performs the action
     *
     * @param array|boolean $userInput the user input
     * @param \Restyii\Model\ModelInterface|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if ($loaded === null)
            $loaded = $this->load();
        $data = array(
            'stats' => $loaded->stats(),
            '_links' => array(
                'self' => array(
                    'label' => $this->label(),
                    'href' => $loaded->createUrl('stats'),
                ),
                'subject' => array(
                    'label' => $loaded->instanceLabel(),
                    'href' => $loaded->createUrl(),
                    'profile' => get_class($loaded),
                ),
            )
        );
        return array(200, $data);
    }


}
