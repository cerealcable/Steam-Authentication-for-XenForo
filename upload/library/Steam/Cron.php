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

class Steam_Cron {

	public static function update() {
		set_time_limit(0);
		ignore_user_abort(1);
		$options = XenForo_Application::get('options');
		$gamestats = $options->steamGameStats;
		if ($gamestats > 0)
		{
		$db = XenForo_Application::get('db');
		$sHelper = new Steam_Helper_Steam();
		$results = $db->fetchAll("SELECT user_id, provider_key FROM xf_user_external_auth WHERE provider = 'steam' AND provider_key > 0");
		foreach($results as $row) {
			$games = $sHelper->getUserGames($row['provider_key']);
			foreach($games as $id => $data) {
				
				// game info
				$db->query("INSERT IGNORE INTO xf_steam_games(game_id, game_name, game_logo, game_link) VALUES($id, '{$data['name']}', '{$data['logo']}', '{$data['link']}');");

				// update
				$r = $db->fetchRow("SELECT * FROM xf_user_steam_games WHERE user_id = {$row['user_id']} AND game_id = $id;");
				if($r == NULL) {
					// Insert
					$db->insert("xf_user_steam_games", array('user_id'=>$row['user_id'], 'game_id'=>$id, 'game_hours'=>$data['hours'], 'game_hours_recent'=>$data['hours_recent']));
				} else {
					// Update
					$db->query("UPDATE xf_user_steam_games SET game_hours = {$data['hours']}, game_hours_recent = {$data['hours_recent']} WHERE user_id = {$row['user_id']} AND game_id = $id;");
				}
			}
		}
		}
	}
}