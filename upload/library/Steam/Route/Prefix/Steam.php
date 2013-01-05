<?php

class Steam_Route_Prefix_Steam implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $components = explode('/', $routePath);
        $subPrefix = strtolower(array_shift($components));
        $subSplits = explode('.', $subPrefix);

        $controllerName = '';
        $action = '';
        $intParams = '';
        $strParams = '';
        $slice = false;

        switch ($subPrefix)
        {
            case 'top-owned-games':        $controllerName = '_Owned';    $intParams = '';        $slice = true;    break;
            case 'top-played-games':    $controllerName = '_Played';    $intParams = '';        $slice = true;    break;
            case 'top-recently-played-games':    $controllerName = '_Recent';    $intParams = '';        $slice = true;    break;
            default :
                if (is_numeric(end($subSplits))) { $controllerName = '_Steam'; $intParams = ''; }
        }

        $routePathAction = ($slice ? implode('/', array_slice($components, 0, 2)) : $routePath).'/';
        $routePathAction = str_replace('//', '/', $routePathAction);

        if ($strParams)
        {
            $action = $router->resolveActionWithStringParam($routePathAction, $request, $strParams);
        }
        else
        {
            $action = $router->resolveActionWithIntegerParam($routePathAction, $request, $intParams);
        }

        $action = $router->resolveActionAsPageNumber($action, $request);
        return $router->getRouteMatch('Steam_ControllerPublic_Steam'.$controllerName, $action, 'steam', $routePath);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $components = explode('/', $action);
        $subPrefix = strtolower(array_shift($components));

        $intParams = '';
        $strParams = '';
        $title = '';
        $slice = false;

        switch ($subPrefix)
        {
            case 'top-owned-games':        $intParams = '';                                    $slice = true;    break;
            case 'top-played-games':    $intParams = '';        $title = '';    $slice = true;    break;
            case 'top-recently-played-games':   $intParams = '';        $title = '';    $slice = true;    break;
            default:            $intParams = '';        $title = '';
        }

        if ($slice)
        {
            $outputPrefix .= '/'.$subPrefix;
            $action = implode('/', $components);
        }

        $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

        if ($strParams)
        {
            return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
        }
        else
        {
            return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $intParams, $title);
        }
    }
}