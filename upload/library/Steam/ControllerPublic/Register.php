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

class Steam_ControllerPublic_Register extends XFCP_Steam_ControllerPublic_Register {

	const STEAM_LOGIN = 'https://steamcommunity.com/openid/login';

	public function actionSteam() {
		$assocUserId = $this->_input->filterSingle('assoc', XenForo_Input::UINT);
		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);

		$session = XenForo_Application::get('session');

		if($this->_input->filterSingle('reg', XenForo_Input::UINT)) {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->_genUrl()
			);
		}

		// Validate Response
		$id = $this->_validate();
		if(empty($id)) {
			return $this->responseError('Error during authentication.  Please try again.');
		}

		$session->set('steam_id', $id);
		$userModel = $this->_getUserModel();
		$userExternalModel = $this->_getUserExternalModel();

		$stAssoc = $userExternalModel->getExternalAuthAssociation('steam', $id);
		if($stAssoc && $userModel->getUserById($stAssoc['user_id'])) {
			XenForo_Application::get('session')->changeUserId($stAssoc['user_id']);
			XenForo_Visitor::setup($stAssoc['user_id']);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}

		$existingUser = false;
		if(XenForo_Visitor::getUserId()) {
			$existingUser = XenForo_Visitor::getInstance();
		} else if($assocUserId) {
			$existingUser = $userModel->getUserById($assocUserId);
		}

		if($existingUser) {
			// must associate: matching user
			return $this->responseView('XenForo_ViewPublic_Register_Steam', 'register_steam', array(
				'associateOnly'	=> true,
				'existingUser'	=> $existingUser,
				'redirect'		=> $redirect
			));
		}

		if(!XenForo_Application::get('options')->get('registrationSetup', 'enabled')) {
			$this->_assertRegistrationActive();
		}

		$username = "";
		//$xml = simplexml_load_file("http://steamcommunity.com/profiles/{$id}/?xml=1");
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		$json_object=file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$id}");
		$json_decoded = json_decode($json_object);
		
		if(!empty($json_decoded)) {
			$username = $json_decoded->response->players[0]->personaname;
			
			if (isset($json_decoded->response->players[0]->locstatecode))
			{
			$location = $json_decoded->response->players[0]->locstatecode;
			
switch($location){
case "AL": $location = "Alabama"; break;
case "AK": $location = "Alaska"; break;
case "AZ": $location = "Arizona"; break;
case "AR": $location = "Arkansas"; break;
case "CA": $location = "California"; break;
case "CO": $location = "Colorado"; break;
case "CT": $location = "Connecticut"; break;
case "DE": $location = "Delaware"; break;
case "FL": $location = "Florida"; break;
case "GA": $location = "Georgia"; break;
case "HI": $location = "Hawaii"; break;
case "ID": $location = "Idaho"; break;
case "IL": $location = "Illinois"; break;
case "IN": $location = "Indiana"; break;
case "IA": $location = "Iowa"; break;
case "KS": $location = "Kansas"; break;
case "KY": $location = "Kentucky"; break;
case "LA": $location = "Louisiana"; break;
case "ME": $location = "Maine"; break;
case "MD": $location = "Maryland"; break;
case "MA": $location = "Massachusetts"; break;
case "MI": $location = "Michigan"; break;
case "MN": $location = "Minnesota"; break;
case "MS": $location = "Mississippi"; break;
case "MO": $location = "Missouri"; break;
case "MT": $location = "Montana"; break;
case "NE": $location = "Nebraska"; break;
case "NV": $location = "Nevada"; break;
case "NH": $location = "New Hampshire"; break;
case "NJ": $location = "New Jersey"; break;
case "NM": $location = "New Mexico"; break;
case "NY": $location = "New York"; break;
case "NC": $location = "North Carolina"; break;
case "ND": $location = "North Dakota"; break;
case "OH": $location = "Ohio"; break;
case "OK": $location = "Oklahoma"; break;
case "OR": $location = "Oregon"; break;
case "PA": $location = "Pennsylvania"; break;
case "RI": $location = "Rhode Island"; break;
case "SC": $location = "South Carolina"; break;
case "SD": $location = "South Dakota"; break;
case "TN": $location = "Tennessee"; break;
case "TX": $location = "Texas"; break;
case "UT": $location = "Utah"; break;
case "VT": $location = "Vermont"; break;
case "VA": $location = "Virginia"; break;
case "WA": $location = "Washington"; break;
case "WV": $location = "West Virginia"; break;
case "WI": $location = "Wisconsin"; break;
case "WY": $location = "Wyoming"; break;
default: $location = $location;
}
}
else
{
	$location = "Parts Unknown";
}
		}

		$i = 2;
		$origName = $username;
		while($username != "" && $userModel->getUserByName($username)) {
			$username = "$origName $i";
			$i++;
		}

		return $this->responseView('XenForo_ViewPublic_Register_Steam', 'register_steam', array(
			'username'		=> $username,
			'redirect'		=> $redirect,
			'customFields'	=> $this->_getFieldModel()->prepareUserFields(
				$this->_getFieldModel()->getUserFields(array('registration' => true)),
				true
			),
			'timeZones'		=> XenForo_Helper_TimeZone::getTimeZones(),
			'tosUrl'		=> XenForo_Dependencies_Public::getTosUrl(),
			'location'		=> $location
		), $this->_getRegistrationContainerParams());
	}

	public function actionSteamRegister() {
		$this->_assertPostOnly();
		$session = XenForo_Application::get('session');

		if(!$session->get('steam_id')) {
			return $this->responseError('Lost Steam ID');
		}

		// Get User Profile Data
		$id = $session->get('steam_id');
		//$xml = simplexml_load_file("http://steamcommunity.com/profiles/{$id}/?xml=1");
		$options = XenForo_Application::get('options');
		$steamapikey = $options->steamAPIKey;
		$json_object=file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamapikey}&steamids={$id}");
		$json_decoded = json_decode($json_object);
		
		if(!empty($json_decoded)) {
			$username = $json_decoded->response->players[0]->personaname;
			$avatar = $json_decoded->response->players[0]->avatarfull;
		}

		$userModel = $this->_getUserModel();
		$userExternalModel = $this->_getUserExternalModel();

		$doAssoc = ($this->_input->filterSingle('associate', XenForo_Input::STRING) || $this->_input->filterSingle('force_assoc', XenForo_Input::UINT));

		if($doAssoc) {
			$associate = $this->_input->filter(array(
				'associate_login'		=> XenForo_Input::STRING,
				'associate_password'	=> XenForo_Input::STRING
			));

			$loginModel = $this->_getLoginModel();

			if($loginModel->requireLoginCaptcha($associate['associate_login'])) {
				return $this->responseError(new XenForo_Phrase('your_account_has_temporarily_been_locked_due_to_failed_login_attempts'));
			}

			$userId = $userModel->validateAuthentication($associate['associate_login'], $associate['associate_password'], $error);
			if(!$userId) {
				$loginModel->logLoginAttempt($associate['associate_login']);
				return $this->responseError($error);
			}

			$userExternalModel->updateExternalAuthAssociation('steam', $id, $userId);

			$session->changeUserId($userId);
			XenForo_Visitor::setup($userId);
			$this->updateUserStats($userId, $id);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}

		$this->_assertRegistrationActive();

		$data = $this->_input->filter(array(
			'username'	=> XenForo_Input::STRING,
			'timezone'	=> XenForo_Input::STRING,
			'email'		=> XenForo_Input::STRING,
			'gender'	=> XenForo_Input::STRING,
			'location'	=> XenForo_Input::STRING,
			'dob_day'	=> XenForo_Input::UINT,
			'dob_month'	=> XenForo_Input::UINT,
			'dob_year'	=> XenForo_Input::UINT
		));

		if(XenForo_Dependencies_Public::getTosUrl() && !$this->_input->filterSingle('agree', XenForo_Input::UINT)) {
			return $this->responseError(new XenForo_Phrase('you_must_agree_to_terms_of_service'));
		}

		$options = XenForo_Application::get('options');

		$writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
		if($options->registrationDefaults) {
			$writer->bulkSet($options->registrationDefaults, array('ignoreInvalidFields' => true));
		}
		$writer->bulkSet($data);

		$auth = XenForo_Authentication_Abstract::create('XenForo_Authentication_NoPassword');
		$writer->set('scheme_class', $auth->getClassName());
		$writer->set('data', $auth->generate(''), 'xf_user_authenticate');

		$writer->set('user_group_id', XenForo_Model_User::$defaultRegisteredGroupId);
		$writer->set('language_id', XenForo_Visitor::getInstance()->get('language_id'));

		$customFields = $this->_input->filterSingle('custom_fields', XenForo_Input::ARRAY_SIMPLE);
		$customFieldsShown = $this->_input->filterSingle('custom_fields_shown', XenForo_Input::STRING, array('array' => true));
		$writer->setCustomFields($customFields, $customFieldsShown);

		$writer->advanceRegistrationUserState(false);
		$writer->preSave();

		if($options->get('registrationSetup', 'requireDob')) {
			// dob required
			if(!$data['dob_day'] || !$data['dob_month'] || !$data['dob_year']) {
				$writer->error(new XenForo_Phrase('please_enter_valid_date_of_birth'), 'dob');
			} else {
				$userAge = $this->_getUserProfileModel()->getUserAge($writer->getMergedData(), true);
				if($userAge < 1) {

				} else if($userAge < intval($options->get('registrationSetup', 'minimumAge'))) {
					// TODO: set a cookie to prevent re-registration attempts
					// But I don't care
					$writer->error(new XenForo_Phrase('sorry_you_too_young_to_create_an_account'));
				}
			}
		}

		$writer->save();
		$user = $writer->getMergedData();

		if(!empty($avatar)) {
			$avatarFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');

			$httpClient = XenForo_Helper_Http::getClient(preg_replace('/\s+/', '%20', $avatar));
			$response = $httpClient->request('GET');
			if($response->isSuccessful()) {
				file_put_contents($avatarFile, $response->getBody());
			}
			// Apply Avatar
			try {  
				$user = array_merge($user, $this->getModelFromCache('XenForo_Model_Avatar')->applyAvatar($user['user_id'], $avatarFile));
			} catch (XenForo_Exception $e) {}

			@unlink($avatarFile);
		}
		
		$userExternalModel->updateExternalAuthAssociation('steam', $id, $user['user_id']);

		XenForo_Model_Ip::log($user['user_id'], 'user', $user['user_id'], 'register');
		$session->changeUserId($user['user_id']);
		XenForo_Visitor::setup($user['user_id']);
		$this->updateUserStats($user['user_id'], $id);

		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);

		$viewParams = array(
			'user'		=> $user,
			'redirect'	=> ($redirect ? XenForo_Link::convertUriToAbsoluteUri($redirect) : ''),
			'steam' => true
		);
		
		return $this->responseView(
			'XenForo_ViewPublic_Register_Process',
			'register_process',
			$viewParams,
			$this->_getRegistrationContainerParams()
		);
	}

	/**
	 * Generates URI to be used
	 */
	private function _genUrl() {
        $callbackUri = XenForo_Link::buildPublicLink('full:register/steam', false, array(
            'redirect' => $this->getDynamicRedirect()
        ));

		$params = array(
			'openid.ns'			=> 'http://specs.openid.net/auth/2.0',
			'openid.mode'		=> 'checkid_setup',
			'openid.return_to'	=> $callbackUri,
			'openid.realm'		=> (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
			'openid.identity'	=> 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.claimed_id'	=> 'http://specs.openid.net/auth/2.0/identifier_select'
		);

		return self::STEAM_LOGIN . '?' . http_build_query($params, '', '&');
	}

	/**
	 * Validates OpenId Returned Data
	 */
	protected function _validate(){
		if(empty($_GET['openid_assoc_handle'])) {
			return false;
		}

		// Start off with some basic params
		$params = array(
			'openid.assoc_handle'    => $_GET['openid_assoc_handle'],
			'openid.signed'          => $_GET['openid_signed'],
			'openid.sig'             => $_GET['openid_sig'],
			'openid.ns'              => 'http://specs.openid.net/auth/2.0',
		);

		// Get all the params that were sent back and resend them for validation
		$signed = explode(',', $_GET['openid_signed']);

		foreach($signed as $item) {
			$val = $_GET['openid_' . str_replace('.', '_', $item)];
			$params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
		}

		// Finally, add the all important mode.
		$params['openid.mode'] = 'check_authentication';

		// Stored to send a Content-Length header
		$data =  http_build_query($params);
		$context = stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  =>
					"Accept-language: en\r\n".
					"Content-type: application/x-www-form-urlencoded\r\n" .
					"Content-Length: " . strlen($data) . "\r\n",
				'content' => $data,
			),
		));

		$result = file_get_contents(self::STEAM_LOGIN, false, $context);

		// Validate wheather it's true and if we have a good ID
		preg_match("#^http://steamcommunity.com/openid/id/([0-9]{17,25})#", $_GET['openid_claimed_id'], $matches);
		$steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;

		// Return our final value
		return preg_match("#is_valid\s*:\s*true#i", $result) == 1 ? $steamID64 : '';
	}

	private function updateUserStats($userId, $steamId) {
        $options = XenForo_Application::get('options');
		$gamestatsreg = $options->steamGameStatsReg;
		if ($gamestatsreg > 0)
		{
		$db = XenForo_Application::get('db');
		$sHelper = new Steam_Helper_Steam();
        $games = $sHelper->getUserGames($steamId);
        foreach($games as $id => $data) {
			// game info
			$db->query("INSERT IGNORE INTO xf_steam_games(game_id, game_name, game_logo, game_link) VALUES($id, '{$data['name']}', '{$data['logo']}', '{$data['link']}');");

			// update
			$r = $db->fetchRow("SELECT * FROM xf_user_steam_games WHERE user_id = $userId AND game_id = $id;");
			if($r == NULL) {
				// Insert
				$db->insert("xf_user_steam_games", array('user_id'=>$userId, 'game_id'=>$id, 'game_hours'=>$data['hours']));
			} else {
				// Update
				$db->query("UPDATE xf_user_steam_games SET game_hours = {$data['hours']} WHERE user_id = $userId AND game_id = $id;");
			}
		}
		}
	}
}

?>