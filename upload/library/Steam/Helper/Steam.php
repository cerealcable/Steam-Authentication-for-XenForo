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

class Steam_Helper_Steam {

	// cURL Variable
	private $ch = null;

	public function __construct() {
		// Setup cURL
		if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
		{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 6);
        if(!ini_get('safe_mode') && !ini_get('open_basedir'))
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
        }
	}

	public function getUserInfo($steam_id) {
		
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		
		//Check for cURL. If it can be used, use it!
		if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
		{
			curl_setopt($this->ch, CURLOPT_URL, "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json");
			ob_start();
			$json_object = curl_exec($this->ch);
			echo $json_object;
			$json_object = ob_get_clean();
			$json_object = trim($json_object);
			
			//Check to make sure nothing went wrong, if it did, try to fix it.
			if (strpos($json_object,'response:') !== false) {
				$i = 0;
				while (($i < 3) || ((strpos($json_object,'response:') !== false))) {
					curl_setopt($this->ch, CURLOPT_URL, "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json");
					ob_start();
					$json_object = curl_exec($this->ch);
					echo $json_object;
					$json_object = ob_get_clean();
					$json_object = trim($json_object);
					$i++;
					sleep(3);
				}
			}
		}
		
		//No cURL?! NOOOOOOOOOOOOOOOOOOOOOOOOO-- Wait, that's okay, let's use file_get_contents!
		else
		{
			$json_object = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json" );
			
			//Check to make sure nothing went wrong, if it did, try to fix it.
			if ($json_object === false) {
				$i = 0;
				while ($json_object === false && $i < 2) {
					$json_object = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$steam_id}&format=json" );
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

		//Check for cURL. If it can be used, use it!
		if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
		{
			curl_setopt($this->ch, CURLOPT_URL, "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json");
			ob_start();
			$json_object = curl_exec($this->ch);
			echo $json_object;
			$json_object = ob_get_clean();
			$json_object = trim($json_object);
			
			//Check to make sure nothing went wrong, if it did, try to fix it.
			if (strpos($json_object,'response:') !== false) {
				$i = 0;
				while (($i < 3) || ((strpos($json_object,'response:') !== false))) {
					curl_setopt($this->ch, CURLOPT_URL, "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json");
					ob_start();
					$json_object = curl_exec($this->ch);
					echo $json_object;
					$json_object = ob_get_clean();
					$json_object = trim($json_object);
					$i++;
					sleep(3);
				}
			}
		}
		
		//No cURL?! NOOOOOOOOOOOOOOOOOOOOOOOOO-- Wait, that's okay, let's use file_get_contents!
		else
		{
			$json_object = file_get_contents("http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json" );
			
			//Check to make sure nothing went wrong, if it did, try to fix it.
			if ($json_object === false) {
				$i = 0;
				while ($json_object === false && $i < 2) {
					$json_object = file_get_contents("http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$steamapikey}&steamid={$steam_id}&include_appinfo=1&include_played_free_games=1&format=json" );
					$i++;
					sleep(1);
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
					if (strcmp($game->img_logo_url,"") == 0)
					{
						$appLogo = "styles/default/steamauth/unknown_game.png";
					}
					else
					{
						$appLogo = isset($game->img_logo_url) ? addslashes($game->img_logo_url) : "";
						$appLogo = "http://media.steampowered.com/steamcommunity/public/images/apps/" . $appId . "/" . $appLogo . ".jpg";
    	            }
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
        $results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g WHERE g.user_id = u.user_id AND g.user_id = p.user_id AND p.provider = 'steam' GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC LIMIT $limit;");
        
		foreach($results as $row) {
		
            $rVal[$row['user_id']] = array(
                'hours' => $row['hours'],
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'gravatar' => $row['gravatar'],
				'avatar_date' => $row['avatar_date'],
				'steamprofileid' => $row['provider_key']
            );
        }

        return $rVal;
	}
	
	public function getGameOwnersHoursStats($limit=25) {
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist))
		{
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g WHERE g.user_id = u.user_id AND g.user_id = p.user_id AND p.provider = 'steam' GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC LIMIT $limit;");
        }
		elseif (!empty($includelist))
		{
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g WHERE g.user_id = u.user_id AND g.user_id = p.user_id AND p.provider = 'steam' AND g.game_id IN ($includelist) GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC LIMIT $limit;");
		}
		else
		{
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g WHERE g.user_id = u.user_id AND g.user_id = p.user_id AND p.provider = 'steam' AND g.game_id NOT IN ($excludelist) GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC LIMIT $limit;");
		}
		
		
		foreach($results as $row) {
		
            $rVal[$row['user_id']] = array(
                'hours' => $row['hours'],
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'gravatar' => $row['gravatar'],
				'avatar_date' => $row['avatar_date'],
				'steamprofileid' => $row['provider_key']
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
	
	public function getGameStatisticsStats($limit=25) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist))
		{
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC LIMIT $limit;");
        }	
		elseif (!empty($includelist))
		{
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id IN ($includelist) GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC LIMIT $limit;");
        }	
		else
		{
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id NOT IN ($excludelist) GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC LIMIT $limit;");
        }
		
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
	
	public function getGamePlayedStatisticsStats($limit=25) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist))
		{
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }	
		elseif (!empty($includelist))
		{
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id IN ($includelist) GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }	
		else
		{
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id NOT IN ($excludelist) GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }
		
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
	
	public function getGamePlayedRecentStatisticsStats($limit=25) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist))
		{
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }	
		elseif (!empty($includelist))
		{
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id IN ($includelist) GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }	
		else
		{
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours FROM xf_user_steam_games u, xf_steam_games g WHERE u.game_id = g.game_id AND g.game_id NOT IN ($excludelist) GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC LIMIT $limit;");
        }
		
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
		$results = $db->fetchAll("SELECT u.provider_key, p.user_id, p.username FROM xf_user_external_auth u, xf_user p WHERE u.user_id = p.user_id AND u.provider = 'steam' ORDER BY p.username;");
		foreach($results as $row) {
			$rVal[] = array(
				'id' => Steam_Helper_Steam::convertIdToString($row['provider_key']),
				'id64' => $row['provider_key'],
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
    /*
    public static function stDebug($data) {
    foreach ($data as $columnName => $columnData) {
    $stData .= 'Column name: ' . $columnName . ' Column data: ' . $columnData . '<br />';
    }

     return $stData;
    }
    */
}