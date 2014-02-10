<?php
/**
 *      This file is part of Steam Authentication for XenForo
 *
 *      Originally Written by Morgan Humes <morgan@lanaddict.com>
 *      Copyright 2012 Morgan Humes
 *
 *      Code Forked and Updated by Michael Linback Jr. <webmaster@ragecagegaming.com>
 *      Copyright 2014 Michael Linback Jr.
 *      Website: http://ragecagegaming.com
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

class Steam_ControllerAdmin_Steam extends XenForo_ControllerAdmin_Abstract {

	public function _preDispatch($action) {
		$this->assertAdminPermission('viewStatistics');
	}

	public function actionIndex() {
		return $this->responseView('XenForo_ViewAdmin_Steam', 'steam_splash');
	}

	public function actionGames() {
		$sHelper = new Steam_Helper_Steam();

		$gameId = $this->_input->filterSingle('game_id', XenForo_Input::UINT);
		if($gameId) {
			$userModel = XenForo_Model::create('XenForo_Model_User');
			$sHelper = new Steam_Helper_Steam();
			$users = $sHelper->getGameOwners($gameId);
			$owners = array();
			$hours = 0;
			foreach($users as $user) {
				$u = $userModel->getUserById($user['user_id']);
				$user['avatar_url'] = XenForo_Template_Helper_Core::callHelper('avatar', array($u, 's', null, true));
				$owners[] = $user;
				$hours += $user['hours'];
			}
			if (count($owners) == 0)
			{
				$hoursAvgMath = 0;
			}
			else
			{
				$hoursAvgMath = round($hours/count($owners));
			}
			$viewParams = array(
				'count' => count($owners),
				'game' => $sHelper->getGameInfo($gameId),
				'users' => $owners,
				'hours' => $hours,
				'hoursAvg' => $hoursAvgMath
			);
			$template = 'steam_stats_game_view';
		} else {
			$viewParams = array(
				'games' => $sHelper->getAvailableGames()
			);
			$template = 'steam_stats_games';
		}

		return $this->responseView('XenForo_ViewAdmin_Steam_Games', $template, $viewParams);
	}

	public function actionUsers() {
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'users' => $sHelper->getSteamUsers()
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_Users', 'steam_info_users', $viewParams);
	}

	public function actionTopOwned() {
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGameStatistics()
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopOwned', 'steam_stats_topOwned', $viewParams);
	}

	public function actionTopPlayed() {
		$sHelper = new Steam_Helper_Steam();
	
		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedStatistics()
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopPlayed', 'steam_stats_topPlayed', $viewParams);
	}

	public function actionTopPlayedRecent() {
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedRecentStatistics()
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopPlayedRecent', 'steam_stats_topPlayedRecent', $viewParams);
	}
}

?>