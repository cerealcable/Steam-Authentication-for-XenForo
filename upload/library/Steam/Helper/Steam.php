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

class Steam_Helper_Steam {

	// cURL Variable
	private $ch = null;

	public function __construct() {
		// Setup cURL
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 4);
	}	

	public function getUserInfo($steam_id) {
		// cURL
		curl_setopt($this->ch, CURLOPT_URL, "http://steamcommunity.com/profiles/{$steam_id}/?xml=1");
		$result = curl_exec($this->ch);
		$xml = simplexml_load_string($result);

        if(!empty($xml)) {
			return array(
    	        'username' => $xml->steamID,
	            'avatar' => $xml->avatarFull,
				'icon' => $xml->avatarIcon,
				'state' => $xml->onlineState

			);
        } else {
			return array(
				'username' => "Unknown Steam Account",
				'avatar' => "Ruh Roh!"
			);
		}
	}

    public function getUserGames($steam_id) {
        $games = array();

		// cURL
		curl_setopt($this->ch, CURLOPT_URL, "http://steamcommunity.com/profiles/$steam_id/games/?xml=1");
		$result = curl_exec($this->ch);
		$xml = simplexml_load_string($result);

        if(!empty($xml)) {
			if(isset($xml->games)) {
	            foreach($xml->games->children() as $game) {
                	$appId = isset($game->appID) ? $game->appID : 0;
            	    $appName = isset($game->name) ? addslashes($game->name) : "";
        	        $appLogo = isset($game->logo) ? addslashes($game->logo) : "";
    	            $appLink = isset($game->storeLink) ? addslashes($game->storeLink) : "";
	                $hours = isset($game->hoursOnRecord) ? $game->hoursOnRecord : 0;

            	    if($appId == 0 || $appName == "") {
        	            continue;
    	            }

	                $games["$appId"] = array (
                    	'name'  => $appName,
                	    'logo'  => $appLogo,
            	        'link'  => $appLink,
        	            'hours' => $hours
    	            );
	            }
			}
        }

        return $games;
    }

	public function deleteSteamData($user_id) {
        $db = XenForo_Application::get('db');
		$db->query("DELETE FROM xf_user_steam_games WHERE user_id = $user_id");
	}

	public function getGameStatistics($limit=25) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT g.game_name, g.game_logo, g.game_link, COUNT(*) AS count FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC LIMIT $limit;");
        foreach($results as $row) {
			$rVal[$row['game_name']] = array(
				'count' => $row['count'],
				'logo' => $row['game_logo'],
				'link' => $row['game_link']
			);
		}

		return $rVal;
	}

	public function getAvailableGames() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT game_id, game_name FROM xf_steam_games ORDER BY game_name;");
		foreach($results as $row) {
			$rVal[] = array( 'id' => $row['game_id'], 'name' => $row['game_name'] );
		}
		return $rVal;
	}
}

?>
