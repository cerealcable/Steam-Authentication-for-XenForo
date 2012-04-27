<?php

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