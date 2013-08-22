<?php

namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as HTML.
 *
 * @package Restyii\MimeType
 */
class HTML extends Form
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
        try {
            if ($request->getIsPjaxRequest()) {
                if (isset($controller->pjaxLayout))
                    $controller->layout = $controller->pjaxLayout;
                else
                    $controller->layout = '//layouts/pjax';
                return $controller->render($response->getViewName(), $params, true);
            }
            else if ($request->getIsAjaxRequest())
                return $controller->renderPartial($response->getViewName(), $params, true);
            else
                return $controller->render($response->getViewName(), $params, true);
        }
        catch (\Exception $e) {
            echo '<pre>'.print_r($e, true).'</pre>';
            return null;
        }
    }


}
