<?php

class Steam_ControllerPublic_Steam_Owned extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGameStatistics()
		);

		return $this->responseView('Steam_ViewPublic_Owned', 'steam_public_owned', $viewParams);
	}
	
}