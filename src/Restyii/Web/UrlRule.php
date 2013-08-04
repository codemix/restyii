<?php

namespace Restyii\Web;


use CHttpRequest;
use CUrlManager;
use Restyii\Utils\String;

/**
 * # Resource Url Rules
 *
 * Custom URL rule class that routes RESTful requests to the appropriate actions.
 *
 * @package Restyii\Web
 */
class UrlRule extends \CBaseUrlRule
{
    /**
     * @var bool true if camelCase in routes should be converted to dashes
     */
    public $useDashes = true;

    /**
     * @var string the name of the attribute on the resource that should be used in urls.
     */
    public $idName = 'id';

    /**
     * @var string the name of the related attribute on the resource that should be used in urls.
     */
    public $relationIdName = 'relationId';

    /**
     * @var string the name of the default action for collections
     */
    public $defaultCollectionAction = 'search';

    /**
     * @var string the name of the default action for items
     */
    public $defaultItemAction = 'read';

    /**
     * A map of HTTP verbs to item action names
     * @var array verb => action name
     */
    public $itemVerbMap = array(
        'HEAD' => 'head',
        'OPTIONS' => 'options',
        'GET' => 'read',
        'POST' => 'update',
        'PATCH' => 'update',
        'PUT' => 'replace',
        'COPY' => 'copy',
        'DELETE' => 'delete',
    );

    /**
     * A map of HTTP verbs to collection action names
     * @var array verb => action name
     */
    public $collectionVerbMap = array(
        'HEAD' => 'head',
        'OPTIONS' => 'options',
        'GET' => 'search',
        'POST' => 'create',
    );

    /**
     * Creates a URL based on this rule.
     *
     * @param CUrlManager $manager the manager
     * @param string $route the route
     * @param array $params list of parameters (name=>value) associated with the route
     * @param string $ampersand the token separating name-value pairs in the URL.
     *
     * @return mixed the constructed URL. False if this rule does not apply.
     */
    public function createUrl($manager, $route, $params, $ampersand)
    {
        $parts = explode('/', $route);
        $segments = array();
        if (count($parts) > 2)
            $segments[] = array_shift($parts);

        $controller = array_shift($parts);
        if ($this->useDashes)
            $controller = String::camelCaseToDashes($controller);

        $segments[] = $controller;#
        $action = array_shift($parts);

        if (!$action)
            return false;

        if (isset($params[$this->idName])) {
            $segments[] = $params[$this->idName];
            unset($params[$this->idName]);
            $isItemAction = true;
        }
        else
            $isItemAction = false;

        if (isset($params['relation'])) {
            $relation = $params['relation'];
            unset($params['relation']);
            $segments[] = ($this->useDashes ? String::camelCaseToDashes($relation) : $relation);
            $defaultItemAction = $this->defaultItemAction.'Related';
            $defaultCollectionAction = $this->defaultCollectionAction.'Related';
            if (isset($params[$this->relationIdName])) {
                $segments[] = $params[$this->relationIdName];
                unset($params[$this->relationIdName]);
                $isItemAction = true;
            }
            else
                $isItemAction = false;
        }
        else {
            $defaultItemAction = $this->defaultItemAction;
            $defaultCollectionAction = $this->defaultCollectionAction;
        }

        if ($isItemAction && $action != $defaultItemAction)
            $segments[] = '_'.($this->useDashes ? String::camelCaseToDashes($action) : $action);
        else if (!$isItemAction && $action != $defaultCollectionAction)
            $segments[] = '_'.($this->useDashes ? String::camelCaseToDashes($action) : $action);

        if (isset($params['#'])) {
            $fragment = $params['#'];
            unset($params['#']);
        }
        else
            $fragment = null;

        $url = implode('/', $segments);

        if (isset($params['urlSuffix'])) {
            $url .= '.'.$params['urlSuffix'];
            unset($params['urlSuffix']);
        }

        if (count($params))
            $url .= '?'.http_build_query($params);
        if ($fragment !== null)
            $url .= '#'.$fragment;
        return $url;
    }

    /**
     * Parses a URL based on this rule.
     *
     * @param CUrlManager $manager the URL manager
     * @param CHttpRequest $request the request object
     * @param string $pathInfo path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string $rawPathInfo path info that contains the potential URL suffix
     *
     * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
     */
    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        $params = array();
        $segments = array();

        if (preg_match('/\.([\w|-]+)$/', $pathInfo, $matches)) {
            $params['urlSuffix'] = $matches[1];
            $pathInfo = mb_substr($pathInfo, 0, - strlen($matches[0]));
        }

        $parts = explode('/', $pathInfo);
        $partCount = count($parts);
        if (!$partCount)
            return false;


        $module = \Yii::app(); /* @var Application $module */
        $controllerID = null;
        $actionID = null;

        $expectModule = true;
        $expectController = true;
        $expectId = false;
        $expectAction = false;
        $expectRelation = false;
        $expectRelationId = false;
        $expectRelationAction = false;

        while($part = array_shift($parts)) {
            if ($this->useDashes)
                $p =  String::dashesToCamelCase($part);
            else
                $p = $part;

            if ($expectModule) {
                if ($module->hasModule($p)) {
                    $segments[] = $p;
                    $module = $module->getModule($p);
                    continue;
                }
                else
                    $expectModule = false;
            }
            if ($expectController) {
                $expectController = false;
                $expectModule = false;
                if (!is_numeric($p) && preg_match('/^(\w+)$/', $p)) {
                    $controllerID = $p;
                    $expectAction = true;
                    $expectId = true;
                    continue;
                }
            }

            if ($expectAction && $p{0} == '_') {
                $expectAction = false;
                $actionID = substr($p, 1);
                continue;
            }

            if ($expectRelationAction && $p{0} == '_') {
                $expectRelationAction = false;
                $expectRelationId = false;
                $actionID = substr($p, 1).'Related';
                continue;
            }
            if ($expectRelation) {
                $expectRelation = false;
                if (preg_match('/^(\w+)$/', $p)) {
                    $params['relation'] = $p;
                    $expectRelationAction = true;
                    continue;
                }
            }

            if ($expectId) {
                $params[$this->idName] = $part;
                $expectId = false;
                $expectAction = true;
                $expectRelation = true;
                continue;
            }

            if ($expectRelationId) {
                $params[$this->relationIdName] = $part;
                $expectRelationId = false;
                continue;
            }
            return false;

        }

        if ($controllerID === null)
            $controllerID = $module->defaultController;

        if ($actionID === null) {
            $requestType = $request->getRequestType();
            if (isset($params['relation'])) {
                if (isset($params[$this->relationIdName]))
                    $actionID = isset($this->itemVerbMap[$requestType]) ? $this->itemVerbMap[$requestType].'Related' : $this->defaultItemAction.'Related';
                else
                    $actionID = isset($this->collectionVerbMap[$requestType]) ? $this->collectionVerbMap[$requestType].'Related' : $this->defaultCollectionAction.'Related';
            }
            else if (isset($params[$this->idName]))
                $actionID = isset($this->itemVerbMap[$requestType]) ? $this->itemVerbMap[$requestType] : $this->defaultItemAction;
            else
                $actionID = isset($this->collectionVerbMap[$requestType]) ? $this->collectionVerbMap[$requestType] : $this->defaultCollectionAction;
        }

        $segments[] = $controllerID;
        $segments[] = $actionID;
        foreach($params as $key => $value)
            $_GET[$key] = $value;
        return implode('/', $segments);
    }

}
