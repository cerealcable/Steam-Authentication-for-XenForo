<?php
/**
 * Grabs JSON data for SteamProfile
 *
 * This takes steamID64 from ajax/steamprofile.js or ajax/steamprofilestats.js
 * and grabs JSON data from the Steam Community API to populate the
 * SteamProfile badge with user's online status, avatar, and game banner 
 * background.
 *
 * Written by Nico Bergemann <barracuda415@yahoo.de>
 * Copyright 2011 Nico Bergemann
 *
 * Code updated by Michael Linback Jr. <webmaster@ragecagegaming.com>
 * Copyright 2014 Michael Linback Jr.
 * Website: http://ragecagegaming.com 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *      
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *      
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

header('content-type: application/json; charset: utf-8'); 

/**
 * Bridge to XenForo
 */
$startTime = microtime(true);
$fileDir = '../../';
require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);
XenForo_Application::disablePhpErrorHandler();
XenForo_Application::setDebugMode(false);

XenForo_Application::$externalDataPath = $fileDir . '/data';
XenForo_Application::$externalDataUrl = $fileDir . '/data';
XenForo_Application::$javaScriptUrl = $fileDir . '/js';

restore_error_handler();
restore_exception_handler();

$options = XenForo_Application::get('options');
$STEAM_API_KEY = $options->steamAPIKey;
$STEAM_GAMEBANNER = $options->steamDisplayBanner;
$XF_IMAGE_KEY = $options->imageLinkProxyKey;
$XF_IMAGE_PROXY = $options->imageLinkProxy['images'];

/**
 * Decides to use cURL or file_get_contents to download JSON data from the
 * Steam Community API.
 *
 * @param string $profileUrl
 *
 * @return mixed The resulting JSON string, or false if the argument was not an array.
 */
function getJsonData($profileUrl)
{
	if((function_exists('curl_version')) 
        && !ini_get('safe_mode') 
        && !ini_get('open_basedir')
    ) {
        $contentJson = getWebPage($profileUrl);
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
function getWebPage($url) 
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

if (!empty($_GET['steamids']))
{

    /*
     * Fetch profile data
     */
    $steamIds = $_GET['steamids'];
    $fullProfile = $_GET['fullprofile'];
	$profileData = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids='
                    .$steamIds 
                    .'&key=' 
                    .$STEAM_API_KEY;
    
    $contentJson = getJsonData($profileData);
    
    if (isset($contentDecoded->response->players) || !empty($XF_IMAGE_PROXY) || $STEAM_GAMEBANNER > 0)
    { 
        $contentDecoded = json_decode($contentJson);
        foreach ($contentDecoded->response->players as $rows)
        {  
            /*
             * Setup image proxy on avatar urls
             */
            if (isset($rows->avatar) && !empty($XF_IMAGE_PROXY))
            {
                $avatar = $rows->avatar;
                $hash = hash_hmac('md5', $avatar,
                XenForo_Application::getConfig()->globalSalt . $XF_IMAGE_KEY
                );
                            
                $avatarProxy = 'proxy.php?' . 'image' . '=' . urlencode($avatar) . '&hash=' . $hash;
                            
                $rows->avatar = $avatarProxy;
            }
            
            /*
             * Apply game image to SteamProfile and use image proxy if enabled
             */
            if ($fullProfile == 1 && isset($rows->gameid) && $STEAM_GAMEBANNER > 0)
            {
                $appid = $rows->gameid;
                $steamid64 = $rows->steamid;
                
                $profileGameData = 'http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid='
                                    .$steamid64
                                    .'&key='
                                    .$STEAM_API_KEY;
                $gameInfo = getJsonData($profileGameData);
                
                $games_decoded = json_decode($gameInfo);
                if ($games_decoded !== null)
                {
                    foreach ($games_decoded->response->games as $rowsgames)
                    {
                        if ($rowsgames->appid == $appid)
                        {
                            if (isset($rowsgames->img_logo_url))
                            {
                                $logo = $rowsgames->img_logo_url;
                            }
                        }
                    }
                    
                    if (!empty($logo))
                    {
                        $logo = 'http://media.steampowered.com/steamcommunity/public/images/apps/'
                                .$appid
                                .'/'
                                .$logo
                                .'.jpg';
                        
                        if (!empty($XF_IMAGE_PROXY))
                        {
                            $hash = hash_hmac('md5', $logo,
                            XenForo_Application::getConfig()->globalSalt . $XF_IMAGE_KEY
                            );
                            
                            $logoProxy = 'proxy.php?' . 'image' . '=' . urlencode($logo) . '&hash=' . $hash;
                            
                            $rows->gameLogoSmall = $logoProxy;
                        }
                        else
                        {
                            $rows->gameLogoSmall = $logo;
                        }
                    }
                }
                else
                {
                    $rows->gameLogoSmall = '';
                }
            }
            
            $logo = '';
        }
        $contentJson = json_encode($contentDecoded);
    }

/*
 * Output JSON data
 */ 
if (function_exists('gzcompress') && (!ini_get('zlib.output_compression')))
{
	ob_start('ob_gzhandler');
}
else
{
	ob_start();
}
echo $contentJson;
ob_end_flush();
}
?>
