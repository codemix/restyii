<?php


namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as JSONP.
 *
 * @package Restyii\MimeType
 */
class JSONP extends Base
{
    /**
     * @var bool whether or not to allow custom callback names.
     */
    public $allowCustomCallback = true;

    /**
     * @var string the callback name
     */
    public $callbackName = 'callback';

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "jsonp",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "text/javascript"
    );

    /**
     * Overrides the parent to prevent JSONP parsing.
     * JSONP is not an acceptable input format for possible security reasons.
     * @param Request $request the request to check
     *
     * @return bool always false for JSONP
     */
    public function canParse(Request $request)
    {
        return false;
    }

    /**
     * Parse the given input and return either an array of attributes => values
     * or null if the input could not be parsed.
     *
     * @param Request $request the request object
     *
     * @return array|null
     */
    public function parse(Request $request)
    {
        return null;
    }

    /**
     * MimeType the given request response
     *
     * @param Response $response the response to format
     *
     * @return string the formatted response data
     */
    public function format(Response $response)
    {
        $prepared = $this->prepare($response->data);
        $callback = $this->getRequestCallbackName($response->getRequest());
        return $callback.'('.json_encode($prepared, $response->prettyPrint ? JSON_PRETTY_PRINT : 0).')';
    }


    /**
     * Gets the appropriate callback name to use for a given request.
     *
     * @param Request $request the request to check for
     *
     * @return string the callback name
     */
    public function getRequestCallbackName(Request $request)
    {
        if ($this->allowCustomCallback)
            return $request->getParam('jsonp', $request->getParam('callback', $this->callbackName));
        else
            return $this->callbackName;
    }



}
