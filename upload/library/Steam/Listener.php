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

class Steam_Listener {
	public static function loadClassController($class, array &$extend) {
		switch($class) {
			case 'XenForo_ControllerPublic_Register':
				$extend[] = 'Steam_ControllerPublic_Register';
				break;
			case 'XenForo_ControllerPublic_Account':
				$extend[] = 'Steam_ControllerPublic_Account';
				break;
			case 'XenForo_ControllerAdmin_User':
				$extend[] = 'Steam_ControllerAdmin_User';
				break;
		}
	}
	
    public static function init(XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        XenForo_Template_Helper_Core::$helperCallbacks += array(
            'steamid' => array('Steam_Helper_Steam', 'convertIdToString'),
            'steamid3' => array('Steam_Helper_Steam', 'convertIdToSteam3')
        );
    }
	
	public static function addNavbarTab(array &$extraTabs, $selectedTabId)
	{
		$options = XenForo_Application::get('options');
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();
		
		if($options->steamNavTab && $options->steamAPIKey)
		{
			if($visitor->hasPermission('SteamAuth', 'viewStats'))
			{
				$extraTabs['steam'] = array(
					'title' => 'Steam',
					'href' => XenForo_Link::buildPublicLink('full:steam'),
					'linksTemplate' => 'steam_navtabs',
					'position'  =>  'middle'
				);
			}
		}
	}

	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template) {
		switch($templateName) {
			case 'PAGE_CONTAINER':
				$template->preloadTemplate('steam_helper_criteria_privs');
				$template->preloadTemplate('steam_navtabs');
				$template->preloadTemplate('steam_public_index');
				break;
		}
	}

	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template) {
		switch($hookName) {
			case 'user_criteria_extra':
				$s = new Steam_Helper_Steam();
				$contents .= $template->create('steam_helper_criteria_privs', array_merge($hookParams, $template->getParams(), array_merge($hookParams, $template->getParams(), array("steam_games" => $s->getAvailableGames()))));
				break;
		}
	}
}
