<?php

class Steam_ControllerPublic_Steam_Recent extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();
		
		if(!$visitor->hasPermission("SteamAuth", "view")){
			throw $this->getErrorOrNoPermissionResponseException('steam_do_not_have_permission');
		}
		else
		{
		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedRecentStatistics()
		);
		
		return $this->responseView('Steam_ViewPublic_Recent', 'steam_public_recent', $viewParams);
		}
	}
}