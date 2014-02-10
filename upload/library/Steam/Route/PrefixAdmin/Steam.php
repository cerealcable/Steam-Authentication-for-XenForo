<?php
/**
 *      This file is part of Steam Authentication for XenForo
 *
 *      Originally Written by Morgan Humes <morgan@lanaddict.com>
 *      Copyright 2012 Morgan Humes
 *
 *      Code Forked and Updated by Michael Linback Jr. <webmaster@ragecagegaming.com>
 *      Copyright 2014 Michael Linback Jr.
 *      Website: http://ragecagegaming.com
 *
 *      Steam Authentication for XenForo is free software: you can redistribute
 *      it and/or modify it under the terms of the GNU General Public License
 *      as published by the Free Software Foundation, either version 3 of the
 *      License, or (at your option) any later version.
 *
 *      Steam Authentication for XenForo is distributed in the hope that it
 *      will be useful, but WITHOUT ANY WARRANTY; without even the implied
 *      warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *      See the GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with SteamProfile.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/**
 * Route prefix handler for cron in the admin control panel.
 *
 * @package XenForo_Cron
 */
class Steam_Route_PrefixAdmin_Steam implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
        $parts = explode('/', $routePath, 2);
		$action = $parts[0];

		if(isset($parts[1])) {
			switch($action) {
				case 'games':
					$action .= $router->resolveActionWithIntegerParam($parts[1], $request, 'game_id');
					break;
			}
		}

		return $router->getRouteMatch("Steam_ControllerAdmin_Steam", $action, 'steam');

		/**
		$subPrefix = strtolower(array_shift($urlComponents));

		$controllerName = 'Steam_ControllerAdmin_Steam';
		$routeName = "steam";

		//$action = $router->resolveActionWithIntegerParam($routePath, $request, 'garage_id');
		return $router->getRouteMatch($controllerName, $subPrefix, $routeName, $routePath);
		*/
	}
}