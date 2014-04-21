<?php

class Steam_Model_Steam extends XenForo_Model
{
	public function getAvailableGames($fetchOptions) {
		$rVal = array();
		$db = XenForo_Application::get('db');
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $results = $db->fetchAll($this->limitQueryResults('SELECT game_id, game_name, game_link, game_logo FROM xf_steam_games ORDER BY game_name ASC', $limitOptions['limit'], $limitOptions['offset']));
		foreach($results as $row) {
        	$logoProxy = $row['game_logo'];
            if (!empty(XenForo_Application::getOptions()->imageLinkProxy['images']))
            {
                $hash = hash_hmac('md5', $logoProxy,
                XenForo_Application::getConfig()->globalSalt . XenForo_Application::getOptions()->imageLinkProxyKey
                );
                $logoProxy = 'proxy.php?' . 'image' . '=' . urlencode($logoProxy) . '&hash=' . $hash;
            }
			$rVal[] = array(
				'id' => $row['game_id'],
				'name' => $row['game_name'],
				'link' => $row['game_link'],
				'logo' => $logoProxy
			);
		}
		return $rVal;
	}
}