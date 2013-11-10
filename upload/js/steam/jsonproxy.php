<?php

header('content-type: application/json; charset: utf-8'); 

/**
 *     Written by Nico Bergemann <barracuda415@yahoo.de>
 *     Copyright 2011 Nico Bergemann
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

$options = XenForo_Application::get('options');
$API_KEY = $options->steamAPIKey;
$STEAM_GAMEBANNER = $options->steamDisplayBanner;

restore_error_handler();
restore_exception_handler();

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
			sleep(1);
		}
	}
	
	curl_close( $ch ); 
	
	return $content;
}

$content_json = '';
$content_decoded = '';

if (!empty($_GET['steamids']))
{
    
	$steam_ids = $_GET['steamids'];
	
	if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
    {
        $content_json = get_web_page("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=" . $steam_ids . "&key=$API_KEY" );
    }
    else
    {
        $content_json = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=" . $steam_ids . "&key=$API_KEY" );
        if ($content_json === false) {
            $i = 0;
            while ($content_json === false && $i < 2) {
                $content_json = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=" . $steam_ids . "&key=$API_KEY" );
                $i++;
                sleep(1);
            }
        }
    }

    $content_decoded = json_decode($content_json);
    unset($content_json);
    
    if (isset($content_decoded->response->players) && $STEAM_GAMEBANNER > 0)
    {
        foreach ($content_decoded->response->players as $rows)
        {
            if (isset($rows->gameid))
            {
                $appid = $rows->gameid;
                $steamid64 = $rows->steamid;
                
                if((function_exists('curl_version')) && !ini_get('safe_mode') && !ini_get('open_basedir'))
                {
                    $game_info = get_web_page("http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid=" . $steamid64 . "&key=$API_KEY" );
                }
                else
                {
                    $game_info = file_get_contents("http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid=" . $steamid64 . "&key=$API_KEY" );
                    if ($game_info === false) {
                        $i = 0;
                        while ($game_info === false && $i < 2) {
                            $game_info = file_get_contents("http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid=" . $steamid64 . "&key=$API_KEY" );
                            $i++;
                            sleep(1);
                        }
                    }
                }
                
                //DEBUG using cURL only
                //$game_info = get_web_page("http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid=" . $steamid64 . "&key=$API_KEY" );
                $games_decoded = json_decode($game_info);
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
                        $logo = 'http://media.steampowered.com/steamcommunity/public/images/apps/' . $appid . '/' . $logo . '.jpg';
                        
                        $rows->gameLogoSmall = $logo;
                    }
                }
                else
                {
                    $rows->gameLogoSmall = '';
                }
            }
            
            $logo = '';
        }
    }

    //$content_json = json_encode($content_decoded);
    //unset($content_decoded);

if (function_exists('gzcompress') && (!ini_get('zlib.output_compression')))
{
	ob_start('ob_gzhandler');
}
else
{
	ob_start();
}

echo json_encode($content_decoded);
unset($content_decoded);
ob_end_flush();
}
?>
