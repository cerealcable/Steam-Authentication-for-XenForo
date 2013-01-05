<?php

class Steam_ControllerPublic_Steam extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGameOwnersHours()
		);

		return $this->responseView('Steam_ViewPublic_Index', 'steam_public_index', $viewParams);
	}
}