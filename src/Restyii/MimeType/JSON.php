<?php


namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as JSON.
 *
 * @package Restyii\MimeType
 */
class JSON extends Base
{
    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "json",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "application/json",
        "application/hal+json",
        "text/json",
    );

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
        $data = json_decode($request->getRawBody(), true);
        return $data ? $data : null;
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
        return json_encode($prepared, $response->prettyPrint ? JSON_PRETTY_PRINT : 0);
    }
}
