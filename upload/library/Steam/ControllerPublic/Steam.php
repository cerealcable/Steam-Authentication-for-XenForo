<?php

class Steam_ControllerPublic_Steam extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGameOwnersHoursStats()
			);

			return $this->responseView('Steam_ViewPublic_Index', 'steam_public_index', $viewParams);
		}
	}
	
	public function actionTopOwnedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGameStatisticsStats()
			);

			return $this->responseView('Steam_ViewPublic_Owned', 'steam_public_owned', $viewParams);
		}
	}
	
	public function actionTopPlayedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGamePlayedStatisticsStats()
			);

			return $this->responseView('Steam_ViewPublic_Played', 'steam_public_played', $viewParams);
		}
	}
	
	public function actionTopRecentlyPlayedGames()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		$visitorPerms = $visitor->getPermissions();

		if(!$visitorPerms['SteamAuth']['viewStats']){
			return $this->responseError(new XenForo_Phrase('steam_do_not_have_permission'));
		}
		else
		{
			$viewParams = array(
				'gameStats' => $sHelper->getGamePlayedRecentStatisticsStats()
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
