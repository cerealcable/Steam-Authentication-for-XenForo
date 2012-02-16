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

class Steam_Helper_Criteria {
	public static function criteriaUser($rule, array $data, array $user, &$returnValue) {
		switch($rule) {
			case 'steam_state':
				if(XenForo_Visitor::getUserId() != 0) {
					switch($data['state']) {
						case 'associated':
							if($user['steam_auth_id'] > 0) {
								$returnValue = true;
							} else {
								$returnValue = false;
							}
							break;
						case 'deassociated':
							if($user['steam_auth_id'] <= 0) {
								$returnValue = true;
							} else {
								$returnValue = false;
							}
							break;
						default:
							$returnValue = false;
							break;
					}
				}
				break;
		}
	}
}

?>
