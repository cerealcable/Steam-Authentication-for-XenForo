/**
 *	Written by Nico Bergemann <barracuda415@yahoo.de>
 *	Copyright 2011 Nico Bergemann
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function SteamProfile() {
	// path/file config
	var basePath = 'js/steam/';
	var scriptFile = 'ajax/steamprofile.js';
	var configFile = 'ajax/steamprofile.xml';
	var proxyFile = 'jsonproxy.php';
	// var themePath;
	
	// language config
	var lang = "english";
	var langLocal = "english";
	var langData = {
		english : {
			loading : "Loading...",
			no_profile : "This user has not yet set up their Steam Community profile.",
			private_profile : "This profile is private.",
			invalid_data : "Invalid profile data.",
			join_game : "Join Game",
			add_friend : "Add to Friends",
			view_tf2items : "View TF2 Backpack",
            view_games : "View games",
            view_friends : "View friends",
            view_groups : "View groups",
            view_inventory : "View inventory",
            view_wishlist : "View wishlist",
            view_videos : "View videos",
            view_screenshots : "View screenshots",
			profile_visibilities : {
				0 : "Offline",
				1 : "Online",
				2 : "Busy",
				3 : "Away",
				4 : "Snooze",
				5 : "In-Game",
				7 : "Looking to Trade",
				6 : "Looking to Play"
			}
		},
		german : {
			loading : "Lade...",
			no_profile : "Dieser Benutzer hat bisher kein Steam Community Profil angelegt.",
			private_profile : "Dieses Profil ist privat.",
			invalid_data : "Ungültige Profildaten.",
			join_game : "Spiel beitreten",
			add_friend : "Als Freund hinzufügen",
			view_tf2items : "TF2-Items ansehen",
            view_games : "View games",
            view_friends : "View friends",
            view_groups : "View groups",
            view_inventory : "View inventory",
            view_wishlist : "View wishlist",
            view_videos : "View videos",
            view_screenshots : "View screenshots",
			profile_visibilities : {
				0 : "Offline",
				1 : "Online",
				2 : "Beschäftigt",
				3 : "Abwesend",
				4 : "Untätig",
				5 : "Im Spiel",
				7 : "Möchte handeln",
				6 : "Möchte spielen"
			}
		},
		portuguese : {
			loading : "Carregando...",
			no_profile : "This user has not yet set up their Steam Community profile.",
			private_profile : "This profile is private.",
			invalid_data : "Invalid profile data.",
			join_game : "Entrar",
			add_friend : "Adicionar à sua lista de amigos",
			view_tf2items : "Ver Itens do TF2",
            view_games : "View games",
            view_friends : "View friends",
            view_groups : "View groups",
            view_inventory : "View inventory",
            view_wishlist : "View wishlist",
            view_videos : "View videos",
            view_screenshots : "View screenshots",
			profile_visibilities : {
				0 : "Offline",
				1 : "Online",
				2 : "Busy",
				3 : "Away",
				4 : "Snooze",
				5 : "In-Game",
				7 : "Looking to Trade",
				6 : "Looking to Play"
			}
		}
	};
	
	// misc config
	var loadLock = false;
	var configLoaded = false;
	var configData;
	var showGameBanner;
	var showSliderMenu;
	var showTF2ItemsIcon;

	// profile data
	var profiles = [];
	var profileCache = {};
	
	// template data
	var profileTpl;
	var loadingTpl;
	var errorTpl;

	this.init = function() {		
		
		/*
		if (typeof spBasePath == "string") {
			basePath = spBasePath;
		} else {
			// extract the path from the src attribute

			// get our <script>-tag
			var scriptElement = $('script[src$=\'' + scriptFile + '\']');
			
			// in rare cases, this script could be included without <script>
			if (scriptElement.length === 0) {
				return;
			}
			
			basePath = scriptElement.attr('src').replace(scriptFile, '');
		}
		*/
		
		// load xml config
		jQuery.ajax({
			type: 'GET',
			global: false,
			url: basePath + configFile,
			dataType: 'xml',
			success: function(request, status) {
				configData = $(request);
				loadConfig();
			}
		});
	};
	
	this.refresh = function() {
		// make sure we already got a loaded config
		// and no pending profile loads
		if (!configLoaded || loadLock) {
			return;
		}
		
		// lock loading
		loadLock = true;
		
		// select profile placeholders
		profiles = $('.steamprofile[title]');
		
		// are there any profiles to build?
		if (profiles.length === 0) {
			return;
		}

		// store profile id for later usage
		profiles.each(function() {
			var profile = $(this);
			profile.data('profileID', $.trim(profile.attr('title')));
			profile.removeAttr('title');
		});

		// replace placeholders with loading template and make them visible
		profiles.empty().append(loadingTpl);
		
		// load profiles
		buildProfiles();
	};
	
	this.load = function(profileID) {
		// make sure we already got a loaded config
		// and no pending profile loads
		if (!configLoaded || loadLock) {
			return;
		}
		
		// create profile base
		profile = $('<div class="steamprofile"></div>');
		
		// add loading template
		profile.append(loadingTpl);
		
		// load xml data
		jQuery.ajax({
			type: 'GET',
			global: false,
			url: getXMLProxyURL(profileID),
			dataType: 'xml',
			success: function(request, status) {
				// build profile and replace placeholder with profile
				profile.empty().append(createProfile($(request)));
			}
		});
		
		return profile;
	};
	
	this.isLocked = function() {
		return loadLock;
	};
	
	function getXMLProxyURL(profileID) {
		return basePath + proxyFile + '?id=' + escape(profileID) + '&lang=' + escape(lang);
	}
	
	function getJSONProxyURL(friendQueryString) {
		return basePath + proxyFile + '?steamids=' + friendQueryString + '&fullprofile=1';
	}
	
	function getConfigString(name) {
		return configData.find('vars > var[name="' + name + '"]').text();
	}
	
	function getConfigBool(name) {
		return getConfigString(name).toLowerCase() == 'true';
	}
	
	function loadConfig() {
		showSliderMenu = getConfigBool('slidermenu');
		showGameBanner = getConfigBool('gamebanner');
		showTF2ItemsIcon = getConfigBool('tf2items');
		lang = getConfigString('language');
		langLocal = lang;
		
		// fall back to english if no translation is available for the selected language in SteamProfile
		if (langData[langLocal] == null) {
			langLocal = "english";
		}
		/*
	
		// set theme stylesheet
		themePath = basePath + 'themes/' + getConfigString('theme') + '/';
		if (document.createStyleSheet)
        {
            document.createStyleSheet(themePath + "style.css");
        }
        else
        {
            $('head').append('<link rel="stylesheet" type="text/css" href="' + themePath + 'style.css">');
		}
        */
		
		// load templates
		profileTpl = $(configData.find('templates > profile').text());
		loadingTpl = $(jQuery.parseHTML(configData.find('templates > loading').text()));
		errorTpl   = $(jQuery.parseHTML(configData.find('templates > error').text()));
		
		// add theme path to image src
		// profileTpl.find('img').attrAppend('src', themePath);
		// loadingTpl.find('img').attrAppend('src', themePath);
		// errorTpl.find('img').attrAppend('src', themePath);
		
		// set localization strings
		profileTpl.find('.sp-joingame').attr('title', langData[langLocal].join_game);
		profileTpl.find('.sp-addfriend').attr('title', langData[langLocal].add_friend);
		profileTpl.find('.sp-viewitems').attr('title', langData[langLocal].view_tf2items);
		loadingTpl.append(langData[langLocal].loading);
		
		// we can now unlock the refreshing function
		configLoaded = true;
		
		// start loading profiles
		SteamProfile.refresh();
	}

	function buildProfiles() {
		var steamMaxProfiles = 999;
		var finishedSteamIDs = [];
		var j = 0, friendQueryString = "";
		
		var uniqueProfiles = $(profiles).length;
		for(var i = 0; i < $(profiles).length; i++)
		{
			var hasAddedSteamID = false;
			if (typeof profileCache[$(profiles[i]).data('profileID')] === "undefined") 
			{
				/*if(finishedSteamIDs[$(profiles[i]).data('profileID')] === true)
				{
					uniqueProfiles = uniqueProfiles - 1;
				}
				else
				{*/
					if(j > 0)
					{
						friendQueryString = friendQueryString + ',';
					}
					friendQueryString = friendQueryString + $(profiles[i]).data('profileID');
					finishedSteamIDs[$(profiles[i]).data('profileID')] = true;
					hasAddedSteamID = true;
					j++;
						
					if (j == steamMaxProfiles || j == uniqueProfiles)
					{
						setTimeout(function(){
						jQuery.ajax({
							global: false,
							type: 'GET',
							url: getJSONProxyURL(friendQueryString),
							dataType: 'json',
							cache: true,
							success: function(data, status, request) {
								if(data){
									$(data.response.players).each( function (index){
										var steamID = $(this)[0].steamid;
										profileCache[steamID] = createProfile($(this));
										for(var k = 0; k < $(profiles).length; k++)
										{
											if($(profiles[k]).data('profileID') == steamID)
											{
												$(profiles[k]).html(profileCache[steamID].html());
												//$(profiles[k]).addClass('state-'+$(profiles[k]).find('.sp-wizard').attr("state"));
											}
										}
									});
									createEvents();
								}
							}
						});
						}, 10);
						j = 0;
					}
				//}
			}
			else
			{
				for(var k = 0; k < $(profiles).length; k++)
				{
					if($(profiles[k]).data('profileID') == steamID)
					{
						$(profiles[k]).append(profileCache[steamID]);
					}
				}
			}
		}
		
		loadLock = false;
		//Need to parse non-existence errors here.
		//return createError(langData[langLocal].no_profile);
	}

	function createProfile(profileData) {
		// var profile;
		profileData = profileData[0];
		// profile data looks good
		var profile = profileTpl.clone();
		var onlineState = profileData.profilestate;
		
		// set state class, avatar image and name
		// profile.find('.sp-badge').addClass('sp-' + onlineState);
		profile.find('.sp-avatar img').attr('src', profileData.avatar);
		profile.find('.sp-info a').append(profileData.personaname);
		
		// set state message
		
		if(profileData.communityvisibilitystate != 3)
		{
			profile.find('.sp-info').append("<div>" + langData[langLocal].private_profile + "</div>");
			//profile.find('.sp-wizard').attr("state",  langData[langLocal].profile_visibilities[0]);
			profile.find('.sp-badge').addClass('sp-' + langData[langLocal].profile_visibilities[0]);
			
		}
		else if(typeof profileData.gameid != "undefined")
		{
			profile.find('.sp-info').append(langData[langLocal].profile_visibilities[5]);
			profile.find('.sp-info').append("<br>" + profileData.gameextrainfo);
		
			//profile.find('.sp-wizard').attr("state",  langData[langLocal].profile_visibilities[5]);
			profile.find('.sp-badge').addClass('sp-' + langData[langLocal].profile_visibilities[5]);
			
		}
		else
		{
			profile.find('.sp-info').append("<div>" + langData[langLocal].profile_visibilities[profileData.personastate] + "</div>");
			//profile.find('.sp-wizard').attr("state", langData[langLocal].profile_visibilities[profileData.personastate]);
			
			switch (profileData.personastate)
			{
				case 0: profile.find('.sp-badge').addClass('sp-' + langData[langLocal].profile_visibilities[0]);
						break;
				default: profile.find('.sp-badge').addClass('sp-' + langData[langLocal].profile_visibilities[1]);
			}
		}
		
		// profile.removeClass('sp-bg-game');
		//profile.find('.sp-bg-fade').removeClass('sp-bg-fade');
		// FIXME
		// set game background
		if (showGameBanner && typeof profileData.gameLogoSmall != "undefined") {
			profile.find('.sp-bg-game-img').css('background-image', 'url(' + profileData.gameLogoSmall + ')');
            //profile.find('background-image', 'url(' + profileData.gameLogoSmall + ')');
		} else {
            profile.removeClass('sp-bg-game-img');
            profile.find('.sp-bg-fade').attr('class', 'sp-bg-no-fade');
            //profile.find('.sp-bg-fade').removeClass('sp-bg-fade');
        }
		

		
		if (showSliderMenu) {
			if (typeof profileData.gameserverip != "undefined") {
				// add 'Join Game' link href
				profile.find('.sp-joingame').attr('href', 'steam://connect/' + profileData.gameserverip);
			} else {
				// the user is not in a multiplayer game, remove 'Join Game' link
				profile.find('.sp-joingame').remove();
			}
			
			/*
		
			if (showTF2ItemsIcon) {
				// add 'View Items' link href
				profile.find('.sp-viewitems')
					.attr('href', 'http://tf2items.com/profiles/' + profileData.steamid);
			} else {
				profile.find('.sp-viewitems').remove();
			}
			
			*/
			
			// add 'Add Friend' link href
			//profile.find('.sp-addfriend').attr('href', 'steam://friends/add/' + profileData.steamid);
			
			profile.find('.sp-addfriend').attr('href', 'steam://friends/add/' +  profileData.steamid);
			profile.find('.sp-addfriend').attr('title', langData[langLocal].add_friend);
                
			profile.find('.sp-viewgames').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/games/');
			profile.find('.sp-viewgames').attr('title', langData[langLocal].view_games);
                
			profile.find('.sp-viewfriends').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/friends/');
			profile.find('.sp-viewfriends').attr('title', langData[langLocal].view_friends);
                
			profile.find('.sp-viewgroups').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/groups/');
			profile.find('.sp-viewgroups').attr('title', langData[langLocal].view_groups);
                
			profile.find('.sp-viewinventory').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/inventory/');
			profile.find('.sp-viewinventory').attr('title', langData[langLocal].view_inventory);
                
			profile.find('.sp-viewwishlist').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/wishlist/');
			profile.find('.sp-viewwishlist').attr('title', langData[langLocal].view_wishlist);
                
			profile.find('.sp-viewvideos').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/videos/');
			profile.find('.sp-viewvideos').attr('title', langData[langLocal].view_videos);
                
			profile.find('.sp-viewscreenshots').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid + '/screenshots/');
			profile.find('.sp-viewscreenshots').attr('title', langData[langLocal].view_screenshots);
			
		} else {
			profile.find('.sp-extra').remove();
		}
		
		// add other link hrefs
		profile.find('.sp-avatar a, .sp-info a').attr('href', 'http://steamcommunity.com/profiles/' + profileData.steamid);
		
		return profile;
	}
	
	function createEvents() {
		// add events for menu
		$('.sp-handle').unbind('click').click(function(e) {
			$(this).siblings('.sp-content').animate({'width': 'toggle'}, 200, 'linear');
			e.stopPropagation();
		});
	}

	function createError(message) {
		var errorTmp = errorTpl.clone();
		errorTmp.append(message);	
		return errorTmp;
	}
}

$(document).ready(function() {
	SteamProfile = new SteamProfile();
	SteamProfile.init();
});