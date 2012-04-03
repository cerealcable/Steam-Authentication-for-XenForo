<?php
/**
 *      This file is part of Steam Authentication for XenForo
 *
 *      Written by Morgan Humes <morgan@lanaddict.com>
 *      Copyright 2012 Morgan Humes
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

class Steam_Listener {
	public static function loadClassController($class, &$extend) {
		switch($class) {
			case 'XenForo_ControllerPublic_Register':
				$extend[] = 'Steam_ControllerPublic_Register';
				break;
			case 'XenForo_ControllerPublic_Account':
				$extend[] = 'Steam_ControllerPublic_Account';
				break;
			case 'XenForo_ControllerAdmin_Tools':
				$extend[] = 'Steam_ControllerAdmin_SteamTools';
				break;
		}
	}

	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template) {
		switch($templateName) {
			case 'PAGE_CONTAINER':
				$params['eAuth'] = 1;
				$template->preloadTemplate('steam_login_bar_item');
				$template->preloadTemplate('steam_navigation_visitor_tab_link');
				$template->preloadTemplate('steam_account_wrapper_sidebar_settings');
				$template->preloadTemplate('steam_message_user_info');
				$template->preloadTemplate('steam_js');
				$template->preloadTemplate('steam_member_view_info');
				$template->preloadTemplate('steam_message_content');
				$template->preloadTemplate('steam_helper_criteria_privs');
				break;
		}
	}

	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template) {
		switch($hookName) {
			case 'login_bar_eauth_items':
				$contents .= $template->create('steam_login_bar_item', $hookParams);
				break;
			case 'navigation_visitor_tab_links1':
				$contents .= $template->create('steam_navigation_visitor_tab_link', $hookParams);
				break;
			case 'account_wrapper_sidebar_settings':
				$contents .= $template->create('steam_account_wrapper_sidebar_settings', $hookParams);
				break;
			case 'message_user_info_text':
				$contents .= $template->create('steam_message_user_info', array_merge($hookParams, $template->getParams()));
				break;
			case 'member_view_info_block':
				$contents .= $template->create('steam_member_view_info', array_merge($hookParams, $template->getParams()));
				break;
			case 'page_container_head':
				$contents .= $template->create('steam_js', $hookParams);
				break;
			case 'message_content':
				$contents = $template->create('steam_message_content', array_merge($hookParams, $template->getParams())) . $contents;
				break;
			case 'user_criteria_extra':
				$s = new Steam_Helper_Steam();
				$contents .= $template->create('steam_helper_criteria_privs', array_merge($hookParams, $template->getParams(), array_merge($hookParams, $template->getParams(), array("steam_games" => $s->getAvailableGames()))));
				break;
		}
	}
}

?>
