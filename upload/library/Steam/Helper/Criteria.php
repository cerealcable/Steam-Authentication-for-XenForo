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

class Steam_Helper_Criteria {
	public static function criteriaUser($rule, array $data, array $user, &$returnValue) {
		switch($rule) {
			case 'steam_state':
				if(XenForo_Visitor::getUserId() != 0) {
					switch($data['state']) {
						case 'associated':
							if(!empty($user['externalAuth']['steam']) && $user['externalAuth']['steam'] > 0) {
								$returnValue = true;
							} else {
								$returnValue = false;
							}
							break;
						case 'deassociated':
							if(empty($user['externalAuth']['steam']) || $user['externalAuth']['steam'] <= 0) {
								$returnValue = true;
							} else {
								$returnValue = false;
							}
							break;
						default:
							$returnValue = false;
							break;
					}
				}
				break;
			case 'steam_game':
				if(array_key_exists('externalAuth', $user) && !empty($user['externalAuth']['steam']) && $user['externalAuth']['steam'] > 0) {
					// check if game is in users games table
					$games = implode(",", $data['games']);
					$db = XenForo_Application::get('db');
					$results = $db->fetchAll("SELECT COUNT(*) AS count FROM xf_user_steam_games WHERE user_id = {$user['user_id']} AND game_id IN ($games);");
					foreach($results as $row) {
						if($row['count'] > 0) {
							$returnValue = true;
						} else {
							$returnValue = false;
						}
						break;
					}
				}
				break;
			case 'steam_not_game':
				if(array_key_exists('externalAuth', $user) && !empty($user['externalAuth']['steam']) && $user['externalAuth']['steam'] > 0) {
					// check if game is NOT in users games table
					$games = implode(",", $data['games']);
					$db = XenForo_Application::get('db');
					$results = $db->fetchAll("SELECT COUNT(*) AS count FROM xf_user_steam_games WHERE user_id = {$user['user_id']} AND game_id IN ($games);");
					foreach($results as $row) {
						if($row['count'] > 0) {
							$returnValue = false;
						} else {
							$returnValue = true;
						}
						break;
					}
				}
				break;
		}
	}
}

?>