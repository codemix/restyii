<?php

namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as form input.
 *
 * @package Restyii\MimeType
 */
class Form extends Base
{

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array('form');

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "application/x-www-form-urlencoded",
        "multipart/form-data"
    );

    /**
     * @inheritDoc
     * Overrides the parent to work around a http header parsing bug in Yii.
     */
    public function matchAcceptTypes(Request $request)
    {
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            foreach($this->mimeTypes as $mime)
                if (stristr($_SERVER['HTTP_ACCEPT'], $mime))
                    return true;
        }
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
        if ($request->getIsPostRequest())
            return count($_POST) ? $_POST : null;
        else if ($request->getIsPutRequest()) {
            $data = $request->getRestParams();
            return $data && count($data) ? $data : null;
        }
        else
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
        return http_build_query($prepared);
    }


}
