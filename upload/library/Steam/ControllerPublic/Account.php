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

class Steam_ControllerPublic_Account extends XFCP_Steam_ControllerPublic_Account {

	public function actionSteam() {
		$sHelper = new Steam_Helper_Steam();
		$visitor = XenForo_Visitor::getInstance();

		$auth = $this->_getUserModel()->getUserAuthenticationObjectByUserId($visitor['user_id']);
		if(!$auth) {
			return $this->responseNoPermission();
		}

		if($this->isConfirmedPost()) {
			$disassociate = $this->_input->filter(array(
				'disassociate' => XenForo_Input::STRING,
				'disassociate_confirm' => XenForo_Input::STRING
			));
			if($disassociate['disassociate'] && $disassociate['disassociate_confirm']) {
				$this->getModelFromCache('XenForo_Model_UserExternal')->deleteExternalAuthAssociation('steam', $visitor['steam_auth_id'], $visitor['user_id']);
				$sHelper->deleteSteamData($visitor['user_id']);

				if(!$auth->hasPassword()) {
					$this->getModelFromCache('XenForo_Model_UserConfirmation')->resetPassword($visitor['user_id']);
				}
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('account/steam')
			);
		} else {
			if($visitor['steam_auth_id']) {
				$stUser = $sHelper->getUserInfo($visitor['steam_auth_id']);
			} else {
				$stUser = false;
			}

			$viewParams = array(
				'stUser' => $stUser,
				'hasPassword' => $auth->hasPassword()
			);

			return $this->_getWrapper(
				'account',
				'steam',
				$this->responseView('XenForo_ViewPublic_Account_Steam', 'account_steam', $viewParams)
			);
		}
	}
}

?>