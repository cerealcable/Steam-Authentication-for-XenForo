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
 
class Steam_ControllerPublic_Steam extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();
        //Make the following a XenForo Option
        $queryLimit = 25;

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGameOwnersHours($queryLimit)
			);

			return $this->responseView('Steam_ViewPublic_Index', 'steam_public_index', $viewParams);
		}
	}
	
	public function actionTopOwnedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();
        //Make the following a XenForo Option
        $queryLimit = 25;

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGameStatistics($queryLimit)
			);

			return $this->responseView('Steam_ViewPublic_Owned', 'steam_public_owned', $viewParams);
		}
	}
	
	public function actionTopPlayedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();
        //Make the following a XenForo Option
        $queryLimit = 25;

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGamePlayedStatistics($queryLimit)
			);

			return $this->responseView('Steam_ViewPublic_Played', 'steam_public_played', $viewParams);
		}
	}
	
	public function actionTopRecentlyPlayedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();
        //Make the following a XenForo Option
        $queryLimit = 25;
        
		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGamePlayedRecentStatistics($queryLimit)
			);
		
			return $this->responseView('Steam_ViewPublic_Recent', 'steam_public_recent', $viewParams);
		}
	}

	/**
	* Session activity details.
	* @see XenForo_Controller::getSessionActivityDetailsForList()
	*/
	public static function getSessionActivityDetailsForList(array $activities)
	{
		return new XenForo_Phrase('checking_out_steam_stats', array('steamUrl' => XenForo_Link::buildPublicLink('steam')));
	}    	
}
