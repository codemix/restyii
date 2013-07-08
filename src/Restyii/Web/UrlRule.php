<?php

namespace Restyii\Web;


use CHttpRequest;
use CUrlManager;

class UrlRule extends \CBaseUrlRule
{
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
        // TODO: Implement createUrl() method.
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
        // TODO: Implement parseUrl() method.
    }

}
