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

$API_KEY = "~~~~~";
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
	curl_close( $ch ); 
	
	return $content; 
}

echo get_web_page("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=" . $_GET['steamids'] . "&key=$API_KEY" );

?>