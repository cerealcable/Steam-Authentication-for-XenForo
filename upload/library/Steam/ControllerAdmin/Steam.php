<?php
/**
 * This file is part of Steam Authentication for XenForo
 *
 * Originally Written by Morgan Humes <morgan@lanaddict.com>
 * Copyright 2012 Morgan Humes
 *
 * Code updated by Michael Linback Jr. <webmaster@ragecagegaming.com>
 * Copyright 2014 Michael Linback Jr.
 * Website: http://ragecagegaming.com
 *
 * Steam Authentication for XenForo is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Steam Authentication for XenForo is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SteamProfile.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Controller for handling the steam section in the admin control panel.
 */
class Steam_ControllerAdmin_Steam extends XenForo_ControllerAdmin_Abstract {

	public function _preDispatch($action) {
		$this->assertAdminPermission('viewStatistics');
	}

	/**
	 * Section splash page
	 */
	public function actionIndex() {
		return $this->responseView('XenForo_ViewAdmin_Steam', 'steam_splash');
	}

    /**
	 * Section that lists all games and individual game stats
	 */
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
			$steamModel = new Steam_Model_Steam();
            $gamesPerPage = 25;
            $page = $this->_input->filterSingle('page', XenForo_Input::UINT);
            $viewParams = array(
				'page' => $page,
                'totalGames' => $sHelper->getAvailableGamesCount(),
                'gamesPerPage' => $gamesPerPage,
                'games' => $steamModel->getAvailableGames(array('perPage' => $gamesPerPage, 'page' => $page))
			);
			$template = 'steam_stats_games';
		}

		return $this->responseView('XenForo_ViewAdmin_Steam_Games', $template, $viewParams);
	}

    /**
	 * Section that lists all steam users
	 */
	public function actionUsers() {
		$sHelper = new Steam_Helper_Steam();

		$viewParams = array(
			'users' => $sHelper->getSteamUsers()
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_Users', 'steam_info_users', $viewParams);
	}

    /**
	 * Section that lists all top owned games (count)
	 */
	public function actionTopOwned() {
		$sHelper = new Steam_Helper_Steam();
        //Make the following a XenForo Option
        $queryLimit = 25;

		$viewParams = array(
			'gameStats' => $sHelper->getGameStatistics($queryLimit)
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopOwned', 'steam_stats_topOwned', $viewParams);
	}

    /**
	 * Section that lists all top played games (in hours)
	 */
	public function actionTopPlayed() {
		$sHelper = new Steam_Helper_Steam();
        //Make the following a XenForo Option
        $queryLimit = 25;
        
		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedStatistics($queryLimit)
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopPlayed', 'steam_stats_topPlayed', $viewParams);
	}

    /**
	 * Section that lists all top recently played games (in hours)
	 */
    public function actionTopPlayedRecent() {
		$sHelper = new Steam_Helper_Steam();
        //Make the following a XenForo Option
        $queryLimit = 25;
        
		$viewParams = array(
			'gameStats' => $sHelper->getGamePlayedRecentStatistics($queryLimit)
		);

		return $this->responseView('XenForo_ViewAdmin_Steam_TopPlayedRecent', 'steam_stats_topPlayedRecent', $viewParams);
	}
}

?>