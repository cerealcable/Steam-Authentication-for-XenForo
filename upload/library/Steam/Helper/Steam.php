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

	/*private $ch = null;

	public function __construct() {
			// Setup cURL
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_TIMEOUT, 6);
			if(!ini_get('safe_mode') && !ini_get('open_basedir'))
			{
				curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
			}
		}
	*/
	function get_web_page( $url ) {
		$res = array();
		$options = array( 
			CURLOPT_RETURNTRANSFER => true,     // return web page 
			CURLOPT_HEADER         => false,    // do not return headers 
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects 
			CURLOPT_USERAGENT      => "spider", // who am i 
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect 
			CURLOPT_CONNECTTIMEOUT => 5,      // timeout on connect 
			CURLOPT_TIMEOUT        => 5,      // timeout on response 
			CURLOPT_MAXREDIRS      => 2,       // stop after 10 redirects 
			CURLOPT_ENCODING       => 'UTF-8',

		); 
		$ch      = curl_init( $url ); 
		curl_setopt_array( $ch, $options ); 
		$content = curl_exec( $ch ); 
		$err     = curl_errno( $ch ); 
		$errmsg  = curl_error( $ch ); 
		$header  = curl_getinfo( $ch ); 
		
		if ($content === false) {
			$i = 0;
			while ($content === false && $i < 2) {
				$content = curl_exec( $ch ); 
				$err     = curl_errno( $ch ); 
				$errmsg  = curl_error( $ch ); 
				$header  = curl_getinfo( $ch ); 
				$i++;
				sleep(2);
			}
		}
		
		curl_close( $ch ); 
		
		return $content;
	}

	public function getUserInfo($steam_id) {
		
		
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		
		//Check to make sure we get the data
		
		//cURL settings are good, let's do this
		if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
		{
			$json_object = get_web_page("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json");
		}
		
		//cURL isn't a good idea, let's try file_get_contents
		else
		{
			$json_object = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json");

			if ($content_json === false) {
				$i = 0;
				while ($content_json === false && $i < 2) {
					$json_object = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json");
					$i++;
					sleep(1);
				}
			
			}
		}
		
		$json_decoded = json_decode($json_object);
		
		if(!empty($json_decoded)) {
			return array(
    	        'username' =>  $json_decoded->response->players[0]->personaname,
	            'avatar' => $json_decoded->response->players[0]->avatarfull,
				'icon' => $json_decoded->response->players[0]->avatar,
				'state' => $json_decoded->response->players[0]->personastate

			);
        } else {
			return array(
				'username' => "Unknown Steam Account",
				'avatar' => "Ruh Roh!"
			);
		}
	}

    public function getUserGames($steam_id) {
        $options = XenForo_Application::get('options');
		$gamestats = $options->steamGameStats;
		if ($gamestats > 0)
		{
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
        $games = array();

		//Check to make sure we get the data
		
		if((function_exists('curl_version')) &&!ini_get('safe_mode') && !ini_get('open_basedir'))
		//cURL is good, let's do this
		{
			$json_object = get_web_page("http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json");
		}
		
		//cURL isn't a good idea, let's try file_get_contents
		else
		{
			$json_object = file_get_contents("http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json");

			if ($content_json === false) {
				$i = 0;
				while ($content_json === false && $i < 2) {
					$json_object = file_get_contents("http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json");
					$i++;
					sleep(2);
				}
			
			}
			
		}
		
		$json_usergames = json_decode($json_object);
		
		/*
		
		DEV NOTES:
		
		storeLink is not in JSON, however store links are just http://steamcommunity.com/app/<appid>
		appLogo is no longer a full url in JSON. Needs to be this: http://media.steampowered.com/steamcommunity/public/images/apps/<appid>/<img_logo_url>.jpg
		playtime_forever and playtime_2weeks are now in minutes instead of hours like in the XML. Divide by 60.
		*/
		
        if(!empty($json_usergames->response)) {
			if(!empty($json_usergames->response->games)) {
	            foreach($json_usergames->response->games as $game) {
                	$appId = isset($game->appid) ? $game->appid : 0;
            	    $appName = isset($game->name) ? addslashes($game->name) : "";
        	        $appLogo = isset($game->img_logo_url) ? addslashes($game->img_logo_url) : "";
					$appLogo = "http://media.steampowered.com/steamcommunity/public/images/apps/" . $appId . "/" . $appLogo . ".jpg";
    	            //Following line is no longer needed, this was for XML
					//$appLink = isset($game->storeLink) ? addslashes($game->storeLink) : "";
					$appLink = "http://steamcommunity.com/app/" . $appId;
	                $hours = isset($game->playtime_forever) ? $game->playtime_forever : 0;
					$hoursRecent = isset($game->playtime_2weeks) ? $game->playtime_2weeks : 0;

					//JSON stores playtime in minutes (without commas) instead of hours like XML data
					//$hours = str_replace(",", "",$hours);
					//$hoursRecent = str_replace(",", "",$hoursRecent);
					$hours = ($hours / 60);
					$hoursRecent = ($hoursRecent / 60);
					
            	    if($appId == 0 || $appName == "") {
        	            continue;
    	            }

	                $games["$appId"] = array (
                    	'name'  => $appName,
                	    'logo'  => $appLogo,
            	        'link'  => $appLink,
        	            'hours' => $hours,
						'hours_recent' => $hoursRecent
    	            );
	            }
			}
        }

        return $games;
		}
    }

	public function deleteSteamData($user_id) {
        $db = XenForo_Application::get('db');
		$db->query("DELETE FROM xf_user_steam_games WHERE user_id = $user_id");
	}

	public function getGameInfo($id) {
		$db = XenForo_Application::get('db');
		$row = $db->fetchRow("SELECT game_id, game_name, game_logo, game_link FROM xf_steam_games WHERE game_id = $id");
		$rVal = array(
			'id' => $row['game_id'],
			'name' => $row['game_name'],
			'logo' => $row['game_logo'],
			'link' => $row['game_link']
		);

        return $rVal;
	}

	public function getGameOwners($id) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT u.user_id, u.username, g.game_hours, g.game_hours_recent FROM xf_user_steam_games g, xf_user u WHERE g.user_id = u.user_id AND g.game_id = $id");
		foreach($results as $row) {
			$rVal[] = array(
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'hours' => $row['game_hours'],
				'hours_recent' => $row['game_hours_recent']
			);
		}

		return $rVal;
	}
	
	public function getGameOwnersHours($limit=25) {
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		$rVal = array();
        $db = XenForo_Application::get('db');
        $results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.steam_auth_id, SUM(g.game_hours_recent) AS hours FROM xf_user u, xf_user_profile p, xf_user_steam_games g WHERE g.user_id = u.user_id AND g.user_id = p.user_id GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC LIMIT $limit;");
        
		foreach($results as $row) {
		
            $rVal[$row['user_id']] = array(
                'hours' => $row['hours'],
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'gravatar' => $row['gravatar'],
				'avatar_date' => $row['avatar_date'],
				'steamprofileid' => $row['steam_auth_id']
            );
        }

        return $rVal;
	}

	public function getGameStatistics($limit=25) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC LIMIT $limit;");
        foreach($results as $row) {
			$rVal[$row['game_id']] = array(
				'name' => $row['game_name'],
				'count' => $row['count'],
				'logo' => $row['game_logo'],
				'link' => $row['game_link']
			);
		}

		return $rVal;
	}

	public function getGamePlayedStatistics($limit=25) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
		foreach($results as $row) {
			$rVal[$row['game_id']] = array(
				'name' => $row['game_name'],
				'hours' => $row['hours'],
				'logo' => $row['game_logo'],
				'link' => $row['game_link']
			);
		}

		return $rVal;
	}

	public function getGamePlayedRecentStatistics($limit=25) {
        $rVal = array();
        $db = XenForo_Application::get('db');
        $results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        foreach($results as $row) {
            $rVal[$row['game_id']] = array(
                'name' => $row['game_name'],
                'hours' => $row['hours'],
                'logo' => $row['game_logo'],
                'link' => $row['game_link']
            );
        }

        return $rVal;
	}

	public function getAvailableGames() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT game_id, game_name, game_link, game_logo FROM xf_steam_games ORDER BY game_name;");
		foreach($results as $row) {
			$rVal[] = array(
				'id' => $row['game_id'],
				'name' => $row['game_name'],
				'link' => $row['game_link'],
				'logo' => $row['game_logo']
			);
		}
		return $rVal;
	}

	public function getSteamUsers() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT u.user_id, u.username, p.steam_auth_id FROM xf_user u, xf_user_profile p WHERE u.user_id = p.user_id AND p.steam_auth_id > 0 ORDER BY u.username;");
		foreach($results as $row) {
			$rVal[] = array(
				'id' => Steam_Helper_Steam::convertIdToString($row['steam_auth_id']),
				'id64' => $row['steam_auth_id'],
				'username' => $row['username'],
				'user_id' => $row['user_id']
			);
		}
		return $rVal;
	}
	
	/*
	
	This function is used to convert a 64ID to a STEAMID
	
	*/

	public static function convertIdToString($id) {
        $steamId1  = substr($id, -1) % 2;
        $steamId2a = intval(substr($id, 0, 4)) - 7656;
        $steamId2b = substr($id, 4) - 1197960265728;
        $steamId2b = $steamId2b - $steamId1;

        if($steamId2a <= 0 && $steamId2b <= 0) {
            throw new SteamCondenserException("SteamID $id is too small.");
        }

        return "STEAM_0:$steamId1:" . (($steamId2a + $steamId2b) / 2);
    }
}

?>