<?php
/**
 * This file is part of Steam Authentication for XenForo
 *
 * Originally Written by Morgan Humes <morgan@lanaddict.com>
 * Copyright 2012 Morgan Humes
 *
 * Code updated by Michael Linback Jr. <webmaster@ragecagegaming.com>
 * Copyright 2014 Michael Linback Jr.
 * Website: http://ragecagegaming.com
 *
 * Steam Authentication for XenForo is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Steam Authentication for XenForo is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SteamProfile.  If not, see <http://www.gnu.org/licenses/>.
 */

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