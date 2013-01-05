<?php

class Steam_ControllerPublic_Steam extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$text = 'Hello Index!';

		$viewParams = array(
			'text' => $text,
		);

		return $this->responseView('Steam_ViewPublic_Index', 'steam_public_index', $viewParams);
	}
}