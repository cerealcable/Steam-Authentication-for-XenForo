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

if (!empty($_GET['steamids'])) {

    /*
     * Fetch profile data
     */
    $steamIds = $_GET['steamids'];
    $fullProfile = $_GET['fullprofile'];
	$profileData = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids='
                    .$steamIds 
                    .'&key=' 
                    .$STEAM_API_KEY;
    
    $sHelper = new Steam_Helper_Steam();
    $contentJson = $sHelper->getJsonData($profileData);
    
    if (isset($contentDecoded->response->players) || !empty($XF_IMAGE_PROXY) || $STEAM_GAMEBANNER > 0) { 
        $contentDecoded = json_decode($contentJson);
        foreach ($contentDecoded->response->players as $rows) {  
            /*
             * Setup image proxy on avatar urls
             */
            if (isset($rows->avatar) && !empty($XF_IMAGE_PROXY)) {
                $avatarProxy = $sHelper->getImageProxy($rows->avatar);   
                $rows->avatar = $avatarProxy;
            }
            
            /*
             * Apply game image to SteamProfile and use image proxy if enabled
             */
            if ($fullProfile == 1 && isset($rows->gameid) && $STEAM_GAMEBANNER > 0) {
                $appid = $rows->gameid;
                $steamid64 = $rows->steamid;
                
                $profileGameData = 'http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?steamid='
                                    .$steamid64
                                    .'&key='
                                    .$STEAM_API_KEY;
                $gameInfo = $sHelper->getJsonData($profileGameData);
                
                $games_decoded = json_decode($gameInfo);
                if ($games_decoded !== null) {
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
                        
                        if (!empty($XF_IMAGE_PROXY)) {
                            $logoProxy = $sHelper->getImageProxy($logo);
                            $rows->gameLogoSmall = $logoProxy;
                        } else {
                            $rows->gameLogoSmall = $logo;
                        }
                    }
                } else {
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
    if (function_exists('gzcompress') && (!ini_get('zlib.output_compression'))) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }
    echo $contentJson;
    ob_end_flush();
}
?>
