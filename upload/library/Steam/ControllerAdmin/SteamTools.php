<?php
/**
 *      This file is part of Steam Authentication for XenForo
 *
 *      Written by Morgan Humes <morgan@lanaddict.com>
 *      Copyright 2012 Morgan Humes
 *
 *      Steam Authentication for XenForo is free software: you can redistribute
 *      it and/or modify it under the terms of the GNU General Public License
 *      as published by the Free Software Foundation, either version 3 of the
 *      License, or (at your option) any later version.
 *
 *      Steam Authentication for XenForo is distributed in the hope that it
 *      will be useful, but WITHOUT ANY WARRANTY; without even the implied
 *      warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *      See the GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with SteamProfile.  If not, see <http://www.gnu.org/licenses/>.
 */

class Steam_ControllerAdmin_SteamTools extends XenForo_ControllerAdmin_Tools {

	public function _preDispatch($action) {
		$this->assertAdminPermission('viewStatistics');
	}

	public function actionSteamTop25() {
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGameStatistics()
		);

		return $this->responseView('XenForo_ViewAdmin_Tools_SteamTop25', 'tools_steam_top25', $viewParams);
	}
}

?>
