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

class Steam_Helper_Steam {

    /**
     * Checks if image proxy settings need to be applied and applies them
     * 
     * @param string $imgUrl
     *
     * @return string
     */
    public function getImageProxy($logoProxy)
    {
        if (!empty(XenForo_Application::getOptions()->imageLinkProxy['images']))
        {
            $hash = hash_hmac('md5', $logoProxy,
            XenForo_Application::getConfig()->globalSalt . XenForo_Application::getOptions()->imageLinkProxyKey
            );
            $logoProxy = 'proxy.php?' . 'image' . '=' . urlencode($logoProxy) . '&hash=' . $hash;
        }
        return $logoProxy;
    }
    
    /**
     * Decides to use cURL or file_get_contents to download JSON data from the
     * Steam Community API.
     *
     * @param string $profileUrl
     *
     * @return mixed The resulting JSON string, or false if the argument was not an array.
     */
    public function getJsonData($profileUrl)
    {
        if((function_exists('curl_version')) 
            && !ini_get('safe_mode') 
            && !ini_get('open_basedir')
        ) {
            $contentJson = $this->getWebPage($profileUrl);
        } else {
            $contentJson = file_get_contents($profileUrl);
            if ($contentJson === false) {
                $i = 0;
                while ($contentJson === false && $i < 2) {
                    $contentJson = file_get_contents($profileUrl);
                    $i++;
                    sleep(1);
                }
            }
        }
        return $contentJson;
    }

    /**
     * Uses cURL to get JSON data from Steam Community API.
     * 
     * @param string $url
     *
     * @return mixed The resulting JSON string, or false if the argument was not an array.
     */
    public function getWebPage($url) 
    {
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
                sleep(1);
            }
        }
        curl_close( $ch ); 
        
        return $content;
    }

    /**
     * Get profile info for a provided steam ID
     * 
     * Used in the following:
     * ControllerPublic/Account.php
     *
     * @param int $steam_id
     *
     * @return array
     */
	public function getUserInfo($steam_id) {
		
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
        $profileUrl = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='
                       .$steamapikey
                       .'&steamids='
                       .$steam_id
                       .'&format=json';
        $json_object = $this->getJsonData($profileUrl);
		$json_decoded = json_decode($json_object);
        $logoProxy = $json_decoded->response->players[0]->avatar;
        $logoProxy = $this->getImageProxy($logoProxy);
		
		if(!empty($json_decoded)) {
			return array(
    	        'username' => $json_decoded->response->players[0]->personaname,
	            'avatar' => $json_decoded->response->players[0]->avatarfull,
				'icon' => $logoProxy,
				'state' => $json_decoded->response->players[0]->personastate

			);
        } else {
			return array(
				'username' => "Unknown Steam Account",
				'avatar' => "Ruh Roh!"
			);
		}
	}

    /**
     * Get game info for a provided steam ID
     * 
     * Used in the following:
     * ControllerPublic\Register.php
     * Steam\Cron.php
     *
     * @param int $steam_id
     *
     * @return array
     */
    public function getUserGames($steam_id) {
        $options = XenForo_Application::get('options');
		$gamestats = $options->steamGameStats;
		if ($gamestats > 0) {
            $steamapikey = $options->steamAPIKey;
            $games = array();
            $profileGamesUrl = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key='
                                .$steamapikey
                                .'&steamid='
                                .$steam_id
                                .'&include_appinfo=1'
                                .'&include_played_free_games=1'
                                .'&format=json';

            $json_object = $this->getJsonData($profileGamesUrl);
            $json_usergames = json_decode($json_object);
            
            /*
             * Full storeLink is not in JSON, however store links are just http://steamcommunity.com/app/<appid>
             *
             * appLogo is no longer a full url in JSON. Needs to be this:
             * http://media.steampowered.com/steamcommunity/public/images/apps/<appid>/<img_logo_url>.jpg
             *
             * playtime_forever and playtime_2weeks are now in minutes instead of hours like in the XML. Divide by 60.
             */
            
            if(!empty($json_usergames->response->games)) {
                foreach($json_usergames->response->games as $game) {
                    $appId = isset($game->appid) ? $game->appid : 0;
                    $appName = isset($game->name) ? addslashes($game->name) : '';
                    if (strcmp($game->img_logo_url,'') == 0)
                    {
                        $appLogo = 'styles/default/steamauth/unknown_game.png';
                    }
                    else
                    {
                        $appLogo = isset($game->img_logo_url) ? addslashes($game->img_logo_url) : '';
                        $appLogo = 'http://media.steampowered.com/steamcommunity/public/images/apps/' 
                                    .$appId 
                                    .'/' 
                                    .$appLogo 
                                    .'.jpg';
                    }

                    $appLink = 'http://steamcommunity.com/app/' . $appId;
                    $hours = isset($game->playtime_forever) ? $game->playtime_forever : 0;
                    $hoursRecent = isset($game->playtime_2weeks) ? $game->playtime_2weeks : 0;

                    $hours = ($hours / 60);
                    $hoursRecent = ($hoursRecent / 60);
                    
                    if($appId == 0 || $appName == '') {
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

    /**
     * Deletes steam stats data for user
     * 
     * Used in the following:
     * ControllerPublic\Account.php
     *
     * @param int $user_id
     */
    public function deleteSteamData($user_id) {
        $db = XenForo_Application::get('db');
		$db->query("DELETE FROM xf_user_steam_games 
                    WHERE user_id = $user_id");
	}

    /**
     * Get individual game stats
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     *
     * @param int $id
     *
     * @return array
     */
    public function getGameInfo($id) {
		$db = XenForo_Application::get('db');
		$row = $db->fetchRow("SELECT game_id, game_name, game_logo, game_link 
                            FROM xf_steam_games 
                            WHERE game_id = $id");
		$logoProxy = $this->getImageProxy($row['game_logo']);
        $rVal = array(
			'id' => $row['game_id'],
			'name' => $row['game_name'],
			'logo' => $logoProxy,
			'link' => $row['game_link']
		);
        
        return $rVal;
	}

    /**
     * Get all users that own a particular steam game
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     *
     * @param int $id
     *
     * @return array
     */
    public function getGameOwners($id) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT u.user_id, u.username, g.game_hours, g.game_hours_recent 
                                FROM xf_user_steam_games g, xf_user u 
                                WHERE g.user_id = u.user_id 
                                AND g.game_id = $id 
                                AND u.is_banned NOT IN (1)");
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
	
    /**
     * Get all users that own a particular steam game
     * 
     * Used in the following:
     * ControllerPublic\Steam.php
     *
     * @param int $limit
     *
     * @return array
     */
	public function getGameOwnersHours($limit) {
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist))
		{
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours 
                                    FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g 
                                    WHERE g.user_id = u.user_id 
                                    AND g.user_id = p.user_id 
                                    AND p.provider = 'steam' 
                                    AND u.is_banned NOT IN (1) 
                                    GROUP BY u.user_id 
                                    ORDER BY hours DESC, u.user_id ASC 
                                    LIMIT $limit;");
        } elseif (!empty($includelist)) {
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours 
                                    FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g 
                                    WHERE g.user_id = u.user_id 
                                    AND g.user_id = p.user_id 
                                    AND p.provider = 'steam' 
                                    AND g.game_id IN ($includelist) 
                                    AND u.is_banned NOT IN (1) 
                                    GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC 
                                    LIMIT $limit;");
		} else {
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT u.user_id, u.username, gravatar, avatar_date, p.provider_key, SUM(g.game_hours_recent) AS hours 
                                    FROM xf_user u, xf_user_external_auth p, xf_user_steam_games g 
                                    WHERE g.user_id = u.user_id 
                                    AND g.user_id = p.user_id 
                                    AND p.provider = 'steam' 
                                    AND g.game_id NOT IN ($excludelist) 
                                    AND u.is_banned NOT IN (1) 
                                    GROUP BY u.user_id ORDER BY hours DESC, u.user_id ASC
                                    LIMIT $limit;");
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
    
    /**
     * Get count of all games people own
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     * ControllerPublic\Steam.php
     *
     * @param int $limit
     *
     * @return array
     */
	public function getGameStatistics($limit) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist)) {
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY count DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } elseif (!empty($includelist)) {
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id IN ($includelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY count DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } else {
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, COUNT(*) AS count 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id 
                                    NOT IN ($excludelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id ORDER BY count DESC, g.game_id ASC 
                                    LIMIT $limit;");
        }
		
		foreach($results as $row) {
            $logoProxy = $this->getImageProxy($row['game_logo']);
			$rVal[$row['game_id']] = array(
				'name' => $row['game_name'],
				'count' => $row['count'],
				'logo' => $logoProxy,
				'link' => $row['game_link']
			);
		}

		return $rVal;
	}
	
    /**
     * Get total hours for games
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     * ControllerPublic\Steam.php
     *
     * @param int $limit
     *
     * @return array
     */
    public function getGamePlayedStatistics($limit) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist)) {
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } elseif (!empty($includelist)) {
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id IN ($includelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } else {
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id 
                                    NOT IN ($excludelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        }
		
		foreach($results as $row) {
			$logoProxy = $this->getImageProxy($row['game_logo']);
			$rVal[$row['game_id']] = array(
				'name' => $row['game_name'],
				'hours' => $row['hours'],
				'logo' => $logoProxy,
				'link' => $row['game_link']
			);
		}

		return $rVal;
	}

    /**
     * Get total hours for recently played games
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     * ControllerPublic\Steam.php
     *
     * @param int $limit
     *
     * @return array
     */
    public function getGamePlayedRecentStatistics($limit) {
		$options = XenForo_Application::get('options');
		$includelist = $options->steamIncludeGames;
		$excludelist = $options->steamExcludeGames;
		$rVal = array();
		
		if (empty($includelist) && empty($excludelist)) {
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } elseif (!empty($includelist)) {
			$includelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $includelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id 
                                    IN ($includelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        } else {
			$excludelist = preg_replace('/[^,;0-9_-]|[,;]$/s', '', $excludelist);
			$db = XenForo_Application::get('db');
			$results = $db->fetchAll("SELECT g.game_id, g.game_name, g.game_logo, g.game_link, SUM(u.game_hours_recent) AS hours 
                                    FROM xf_user_steam_games u, xf_user p, xf_steam_games g 
                                    WHERE p.user_id = u.user_id 
                                    AND u.game_id = g.game_id 
                                    AND g.game_id NOT IN ($excludelist) 
                                    AND p.is_banned NOT IN (1) 
                                    GROUP BY u.game_id 
                                    ORDER BY hours DESC, g.game_id ASC 
                                    LIMIT $limit;");
        }
		
        foreach($results as $row) {
        	$logoProxy = $this->getImageProxy($row['game_logo']);
            $rVal[$row['game_id']] = array(
                'name' => $row['game_name'],
                'hours' => $row['hours'],
                'logo' => $logoProxy,
                'link' => $row['game_link']
            );
        }

        return $rVal;
	}
    
    /**
     * Lists all games in the database
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     * Listener.php
     *
     * @return array
     */
    public function getAvailableGames() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT game_id, game_name, game_link, game_logo 
                                FROM xf_steam_games 
                                ORDER BY game_name;");
		foreach($results as $row) {
			$logoProxy = $this->getImageProxy($row['game_logo']);
            $rVal[] = array(
				'id' => $row['game_id'],
				'name' => $row['game_name'],
				'link' => $row['game_link'],
				'logo' => $logoProxy
			);
		}
		return $rVal;
	}
    
    /**
     * Count all games in the database
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     *
     * @return int
     */
    public function getAvailableGamesCount() {
		$rVal = array();
		$db = XenForo_Application::get('db');
        $results = $db->fetchAll('SELECT COUNT(*) as total_count FROM xf_steam_games');
		foreach($results as $row) {
            $gameCount = $row['total_count'];
		}
		return $gameCount;
	}

    /**
     * List all steam users in the database
     * 
     * Used in the following:
     * ControllerAdmin\Steam.php
     *
     * @return array
     */
    public function getSteamUsers() {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$results = $db->fetchAll("SELECT u.provider_key, p.user_id, p.username 
                                FROM xf_user_external_auth u, xf_user p 
                                WHERE u.user_id = p.user_id 
                                AND u.provider = 'steam' 
                                ORDER BY p.username;");
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

    /**
     * This function is used to convert a 64ID to a STEAMID
     * 
     * Used in the following:
     * Listener.php
     *
     * @param int $id
     *
     * @return string
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