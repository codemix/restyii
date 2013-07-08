<?php

namespace Restyii\Web;
use Yii;

/**
 * Extend the default URL manager to allow parsing of string based URLs instead of just requests objects.
 * @package Restyii\Web
 */
class UrlManager extends \CUrlManager
{
    /**
     * Override the default implementation to return a routing object when passed a string instead of a request object.
     * @param \CHttpRequest|string $url the request or string to parse
     * @param string|null $verb the http verb to assume when parsing string urls
     *
     * @return array|bool|string false if the request or url could not be parsed, otherwise the route or routing object
     */
    public function parseUrl($url, $verb = 'GET')
    {
        if (!is_string($url)) {
            return parent::parseUrl($url);
        }
        $request = new Request();
        $request->setRequestType($verb);
        $request->setUrl($url);
        $oldGET = $_GET;
        $oldREQUEST = $_REQUEST;
        $_GET = array();
        $parsed = parent::parseUrl($request);
        $params = $_GET;
        $_GET = $oldGET;
        $_REQUEST = $oldREQUEST;
        if ($parsed === false)
            return false;

        array_unshift($params, $parsed);
        return $params;
    }

    /**
     * @inheritDoc
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        $oldUrlSuffix = $this->urlSuffix;
        if (isset($params['_ext'])) {
            $this->urlSuffix = '.'.$params['_ext'];
            unset($params['_ext']);
        }
        $url = parent::createUrl($route, $params, $ampersand);
        $this->urlSuffix = $oldUrlSuffix;
        return $url;
    }


}
