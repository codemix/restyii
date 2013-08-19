<?php

namespace Restyii\Action\Collection;
use Restyii\Action\MultipleTargetInterface;

/**
 * RESTful 'Aggregate' Stats Action
 */
class Aggregate extends Base implements MultipleTargetInterface
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
        return \Yii::t('resource', "{resourceLabel} Aggregate Stats", array(
            '{resourceLabel}' => $this->staticModel()->classLabel()
        ));
    }

    /**
     * @inheritDoc
     */
    public function description()
    {
        $model = $this->staticModel();
        return \Yii::t('resource', "Gets the aggregate statistics for {resourceLabel}.", array(
            '{resourceLabel}' => $model->classLabel(true),
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
     * @param \Restyii\Model\ModelInterface|null $loaded the pre-loaded data for the action, if any
     *
     * @return array an array of parameters to send to the `respond()` method
     */
    public function perform($userInput, $loaded = null)
    {
        if ($loaded === null)
            $loaded = $this->staticModel();
        $data = array(
            'stats' => $loaded->aggregate(),
            '_links' => array(
                'self' => array(
                    'label' => $this->label(),
                    'href' => $loaded->createUrl('aggregate'),
                ),
                'subject' => array(
                    'label' => $loaded->classLabel(true),
                    'href' => $loaded->createUrl('search'),
                    'profile' => array(get_class($loaded)),
                ),
            )
        );
        return array(200, $data);
    }


}
