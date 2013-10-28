<?php
namespace Restyii\Action;

/**
 * PerformEvent
 *
 * This event represents a action perform event. To cancel an action, you can set the
 * `$isValid` to `false` in `onBeforePerform`. You can also modify the HTTP status code,
 * headers and data sent to `respond()` in `onAfterPerform` and - if the action was
 * cancelled - in `onBeforePerform`.
 */
class PerformEvent extends \CModelEvent
{
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
}
