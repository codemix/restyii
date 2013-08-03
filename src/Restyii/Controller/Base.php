<?php

namespace Restyii\Controller;

use Restyii\Utils\String;
use \Restyii\Web\Response;

class Base extends \CController
{
    /**
     * @var string the default layout view
     */
    public $layout = "/layouts/main";


    /**
     * Return a label for the controller
     * @param bool $plural true if the plural form should be returned
     *
     * @return string the label
     */
    public function classLabel($plural = false)
    {
        $humanized = String::humanize(substr(get_class($this), 0, -10));
        return $plural ? String::pluralize($humanized) : $humanized;
    }

    /**
     * Returns a description of the controller
     *
     * @return string the description
     */
    public function classDescription()
    {
        return null;
    }

    /**
     * Respond to a request
     *
     * @param int $code the http response code
     * @param mixed|null $data the data to respond with
     * @param array|null $headers the extra headers to send
     * @param bool $terminateApplication whether or not to terminate the application after responding
     */
    public function respond($code, $data = null, $headers = null, $terminateApplication = true)
    {
        $app = \Yii::app();
        $request = $app->getRequest(); /* @var \Restyii\Web\Request $request */
        $request->respond($code, $data, $headers, $terminateApplication);
    }

    /**
     * Determines whether or not the given url or route matches the current route.
     *
     * @param array|string $url the url route or string.
     *
     * @return bool true if the route matches, otherwise false
     */
    public function matchCurrentRoute($url)
    {
        if (!is_array($url))
            $url = \Yii::app()->getUrlManager()->parseUrl($url);

        $route = trim(array_shift($url), "/");
        $params = $url;
        if ($route != $this->getId() && $route != $this->getRoute())
            return false;

        foreach($params as $key => $value) {
            if (!isset($_GET[$key]) || $_GET[$key] != $value)
                return false;

        }
        return true;
    }

}
