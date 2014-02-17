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

class Steam_ControllerPublic_Account extends XFCP_Steam_ControllerPublic_Account {
    
	public function actionExternalAccounts()
	{
        $response = parent::actionExternalAccounts();
        
		$stUser = false;

		if (!empty($response->subView->params['external']['steam']))
		{
			if (!empty($response->subView->params['external']['steam']['extra_data']))
			{
                $sHelper = new Steam_Helper_Steam();
                $stUser = $sHelper->getUserInfo($response->subView->params['external']['steam']['provider_key']);
			}
		}
        
        $stParams = $response->subView->params;
        
        $stParams['stUser'] = $stUser;
        
        $response->subView->params = $stParams;

		return $response;
	}

	public function actionExternalAccountsDisassociate()
    {
        $response = parent::actionExternalAccountsDisassociate();
        
        $input = $this->_input->filter(array(
			'disassociate' => XenForo_Input::STRING,
			'account' => XenForo_Input::STRING
		));
        
        $visitor = XenForo_Visitor::getInstance();
        
        if ($input['disassociate'] && $input['account'] == 'steam')
		{
			$sHelper = new Steam_Helper_Steam();
            $sHelper->deleteSteamData($visitor['user_id']);
		}

		return $response;
	}

	public function actionSteam()
	{
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			XenForo_Link::buildPublicLink('account/external-accounts')
		);
	}
}

?>