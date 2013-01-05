<?php

class Steam_ControllerPublic_Steam_Recent extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedRecentStatistics()
		);
		
		return $this->responseView('Steam_ViewPublic_Recent', 'steam_public_recent', $viewParams);
	}
}