<?php
/**
 * This file is part of Steam Authentication for XenForo
 *
 * Written by Michael Linback Jr. <webmaster@ragecagegaming.com>
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

class Steam_ControllerAdmin_User extends XFCP_Steam_ControllerAdmin_User {
    /**
	 * Adds Steam Integration to External Accounts
	 */
    
    public function actionExtra() 
    {
        $response = parent::actionExtra();
        
		$stUser = false;

		if (!empty($response->params['external']['steam']))
		{
			if (!empty($response->params['external']['steam']['extra_data']))
			{
                $sHelper = new Steam_Helper_Steam();
                $stUser = $sHelper->getUserInfo($response->params['external']['steam']['provider_key']);
			}
		}
        
        $stParams = $response->params;
        
        $stParams['stUser'] = $stUser;
        
        $response->params = $stParams;

		return $response;
    }
}
?>