<?php

namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as HTML.
 *
 * @package Restyii\MimeType
 */
class HTML extends Base
{

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "html",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "text/html",
        "application/xhtml+xml",
    );

    /**
     * @inheritDoc
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
        $request = $response->getRequest();
        $controller = $request->getAction()->getController();

        $params = array(
            'response' => $this,
            'format' => \Yii::app()->getComponent('format'),
        );
        if ($response->data instanceof \CModel)
            $params['model'] = $response->data;
        else if ($response->data instanceof \CDataProvider)
            $params['dataProvider'] = $response->data;
        else if ($response->data instanceof \Exception)
            $params['error'] = $response->data;
        else
            $params['data'] = $response->data;

        if ($request->getIsAjaxRequest())
            return $controller->renderPartial($response->getViewName(), $params, true);
        else
            return $controller->render($response->getViewName(), $params, true);
    }


}
