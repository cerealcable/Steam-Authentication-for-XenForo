<?php

class Steam_ControllerPublic_Steam_Played extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();
	
		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedStatistics()
		);
		
		return $this->responseView('Steam_ViewPublic_Played', 'steam_public_played', $viewParams);
	}
}