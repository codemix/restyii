<?php
namespace Restyii\Action;

/**
 * Event
 *
 * This event represents an action event. To cancel an action, you can set the
 * `$isValid` to `false` in `onBeforePerform` and `onBeforePresent`. You can also
 * modify the HTTP status code, headers and data sent to `respond()` in `onAfterPerform`
 * and `onAfterPresent`. If the action was cancelled you can also set those data in
 * `onBeforePerform` and `onBeforePresent`.
 */
class Event extends \CModelEvent
{
    /**
     * @var Restyii\Action\Base the action that triggered this event
     */
    public $action;

    /**
     * @var array|bool the user input, or false if none is present
     */
    public $userInput;

    /**
     * @var CActiveRecord|null 
     */
    public $loaded;

    /**
     * @var string the HTTP status code
     */
    public $status = 200;

    /**
     * @var mixed|null $data the data to respond with
     */
    public $data;

    /**
     * @var array|null $headers the extra headers to send
     */
    public $headers;

    /**
     * @var bool $terminateApplication whether or not to terminate the application
     */
    public $terminateApplication = true;

    /**
     * Load event data from a given result of present() or perform()
     *
     * @param array $result return value of Restyii\Action\Base::present() or perform().
     */
    public function loadFromResult($result)
    {
        if(isset($result[0]))
            $this->status = $result[0];
        if(isset($result[1]))
            $this->data = $result[1];
        if(isset($result[2]))
            $this->headers = $result[2];
        if(isset($result[3]))
            $this->terminateApplication = $result[3];
    }

    /**
     * @return array with arguments to use in the call to respond()
     */
    public function getResult()
    {
        return array($this->status, $this->data, $this->headers, $this->terminateApplication);
    }
}
