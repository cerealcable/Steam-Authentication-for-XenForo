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

class Steam_Model_Steam extends XenForo_Model
{
	public function getAvailableGames($fetchOptions) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $results = $db->fetchAll($this->limitQueryResults('SELECT game_id, game_name, game_link, game_logo FROM xf_steam_games ORDER BY game_name ASC', $limitOptions['limit'], $limitOptions['offset']));
		foreach($results as $row) {
            $sHelper = new Steam_Helper_Steam();
            $logoFixed = $this->getSteamCDNDomain($row['game_logo']);
			$rVal[] = array(
				'id' => $row['game_id'],
				'name' => $row['game_name'],
				'link' => $row['game_link'],
				'logo' => $logoFixed
			);
		}
		return $rVal;
	}
}