<?php

namespace Restyii\Controller;

use Restyii\Utils\String;
use \Restyii\Web\Response;
use Yii;

class Base extends \CController
{
    /**
     * @var string the default layout view
     */
    public $layout = "/layouts/main";

    /**
     * @var string the page description
     */
    protected $_pageDescription;

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
     * Sets the description for the page
     * @param string $pageDescription
     */
    public function setPageDescription($pageDescription)
    {
        $this->_pageDescription = $pageDescription;
    }

    /**
     * Gets the description for the page
     * @return string the page description
     */
    public function getPageDescription()
    {
        if ($this->_pageDescription === null) {
            $action = $this->getAction();
            if (method_exists($action, 'label')) /* @var \Restyii\Action\Base $action */
                $this->_pageDescription = $action->label();
        }
        return $this->_pageDescription;
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

    /**
     * @inheritDoc
     */
    public function resolveViewFile($viewName, $viewPath, $basePath, $moduleViewPath = null)
    {
        $app = \Yii::app(); /* @var \Restyii\Web\Application $app */
        if(empty($viewName))
            return false;

        if($moduleViewPath===null)
            $moduleViewPath=$basePath;

        if(($renderer=$app->getViewRenderer())!==null)
            $extension=$renderer->fileExtension;
        else
            $extension='.php';
        if($viewName[0]==='/')
        {
            if(strncmp($viewName,'//',2)===0)
                $viewFile=$basePath.$viewName;
            else
                $viewFile=$moduleViewPath.$viewName;
        }
        elseif(strpos($viewName,'.'))
            $viewFile=Yii::getPathOfAlias($viewName);
        else
            $viewFile=$viewPath.DIRECTORY_SEPARATOR.$viewName;

        return $viewFile.$extension;
    }


}
