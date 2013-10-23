<?php

class Steam_Route_Prefix_Steam implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'steam_id');
        return $router->getRouteMatch('Steam_ControllerPublic_Steam', $action, 'steam');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $actions = explode('/', $action);

        switch ($actions[0])
        {
            case 'top-owned-games':        $intParams = 'owned_id';        $strParams = '';            break;
            case 'top-played-games':        $intParams = 'played_id';        $strParams = '';        break;
            case 'top-recently-played-games':        $intParams = 'recent_id';        $strParams = '';        break;
            default:            $intParams = '';                $strParams = '';                    break;
        }

        $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

        if ($intParams)
        {
            return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, $intParams, $strParams);
        }
        else
        {
            return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, $strParams);
        }
    }
}