function SteamProfile(){function y(t){return e+r+"?id="+escape(t)+"&lang="+escape(i)}function b(t){return e+r+"?steamids="+t+"&fullprofile=0"}function w(e){return f.find('vars > var[name="'+e+'"]').text()}function E(e){return w(e).toLowerCase()=="true"}function S(){c=E("slidermenu");l=E("gamebanner");h=E("tf2items");i=w("language");s=i;if(o[s]==null){s="english"}v=$(f.find("templates > profile").text());m=$(jQuery.parseHTML(f.find("templates > loading").text()));g=$(jQuery.parseHTML(f.find("templates > error").text()));v.find(".sp-joingame").attr("title",o[s].join_game);v.find(".sp-addfriend").attr("title",o[s].add_friend);v.find(".sp-viewitems").attr("title",o[s].view_tf2items);m.append(o[s].loading);a=true;SteamProfile.refresh()}function x(){var e=999;var t=[];var n=0,r="";var i=$(p).length;for(var s=0;s<$(p).length;s++){var o=false;if(typeof d[$(p[s]).data("profileID")]==="undefined"){if(n>0){r=r+","}r=r+$(p[s]).data("profileID");t[$(p[s]).data("profileID")]=true;o=true;n++;if(n==e||n==i){setTimeout(function(){jQuery.ajax({global:false,type:"GET",url:b(r),dataType:"json",cache:true,success:function(e,t,n){if(e){$(e.response.players).each(function(e){var t=$(this)[0].steamid;d[t]=T($(this));for(var n=0;n<$(p).length;n++){if($(p[n]).data("profileID")==t){$(p[n]).html(d[t].html())}}});N()}}})},10);n=0}}else{for(var a=0;a<$(p).length;a++){if($(p[a]).data("profileID")==steamID){$(p[a]).append(d[steamID])}}}}u=false}function T(e){var t;e=e[0];t=v.clone();var n=e.profilestate;t.find(".steamstats_avatar img").attr("src",e.avatar);t.find(".sp-info a").append(e.personaname);if(e.communityvisibilitystate!=3){t.find(".steamstats_avatar img").attr("class","steamstats_avatar offline");t.find(".sp-info").append("<div>"+o[s].private_profile+"</div>");t.find(".sp-badge").addClass("sp-"+o[s].profile_visibilities[0])}else if(typeof e.gameid!="undefined"){t.find(".steamstats_avatar img").attr("class","steamstats_avatar ingame");t.find(".sp-info").append("<div class='sp-ingame'>"+o[s].profile_visibilities[5]+"</div>");t.find(".sp-info").append("<div class='sp-ingame' style='overflow:hidden;text-overflow: ellipsis;white-space: nowrap;'>"+e.gameextrainfo+"</div>");t.find(".sp-badge").addClass("sp-"+o[s].profile_visibilities[5])}else{t.find(".sp-info").append("<div>"+o[s].profile_visibilities[e.personastate]+"</div>");switch(e.personastate){case 0:t.find(".steamstats_avatar img").attr("class","steamstats_avatar offline");t.find(".sp-badge").addClass("sp-"+o[s].profile_visibilities[0]);break;default:t.find(".steamstats_avatar img").attr("class","steamstats_avatar online");t.find(".sp-badge").addClass("sp-"+o[s].profile_visibilities[1])}}t.removeClass("sp-bg-game");t.find(".sp-bg-fade").removeClass("sp-bg-fade");if(c){if(typeof e.gameserverip!="undefined"){t.find(".sp-joingame").attr("href","steam://connect/"+e.gameserverip)}else{t.find(".sp-joingame").remove()}if(h){t.find(".sp-viewitems").attr("href","http://tf2items.com/profiles/"+e.steamid)}else{t.find(".sp-viewitems").remove()}t.find(".sp-addfriend").attr("href","steam://friends/add/"+e.steamid)}else{t.find(".sp-extra").remove()}t.find(".steamstats_avatar a, .sp-info a.sp-name").attr("href","http://steamcommunity.com/profiles/"+e.steamid);return t}function N(){$(".sp-handle").unbind("click").click(function(e){$(this).siblings(".sp-content").toggle(200);e.stopPropagation()})}function C(e){var t=g.clone();t.append(e);return t}var e="js/steam/";var t="ajax/steamprofilestats.js";var n="ajax/steamprofilestats.xml";var r="jsonproxy.php";var i="english";var s="english";var o={english:{loading:"Loading...",no_profile:"This user has not yet set up their Steam Community profile.",private_profile:"This profile is private.",invalid_data:"Invalid profile data.",join_game:"Join Game",add_friend:"Add to Friends",view_tf2items:"View TF2 Backpack",profile_visibilities:{0:"Offline",1:"Online",2:"Busy",3:"Away",4:"Snooze",5:"In-Game",7:"Looking to Trade",6:"Looking to Play"}},german:{loading:"Lade...",no_profile:"Dieser Benutzer hat bisher kein Steam Community Profil angelegt.",private_profile:"Dieses Profil ist privat.",invalid_data:"Ungültige Profildaten.",join_game:"Spiel beitreten",add_friend:"Als Freund hinzufügen",view_tf2items:"TF2-Items ansehen",profile_visibilities:{0:"Offline",1:"Online",2:"Beschäftigt",3:"Abwesend",4:"Untätig",5:"Im Spiel",7:"Möchte handeln",6:"Möchte spielen"}},portuguese:{loading:"Carregando...",no_profile:"This user has not yet set up their Steam Community profile.",private_profile:"This profile is private.",invalid_data:"Invalid profile data.",join_game:"Entrar",add_friend:"Adicionar à sua lista de amigos",view_tf2items:"Ver Itens do TF2",profile_visibilities:{0:"Offline",1:"Online",2:"Busy",3:"Away",4:"Snooze",5:"In-Game",7:"Looking to Trade",6:"Looking to Play"}}};var u=false;var a=false;var f;var l;var c;var h;var p=[];var d={};var v;var m;var g;this.init=function(){jQuery.ajax({type:"GET",global:false,url:e+n,dataType:"xml",success:function(e,t){f=$(e);S()}})};this.refresh=function(){if(!a||u){return}u=true;p=$(".steamprofile[title]");if(p.length===0){return}p.each(function(){var e=$(this);e.data("profileID",$.trim(e.attr("title")));e.removeAttr("title")});p.empty().append(m);x()};this.load=function(e){if(!a||u){return}profile=$('<span class="steamprofile"></span>');profile.append(m);jQuery.ajax({type:"GET",global:false,url:y(e),dataType:"xml",success:function(e,t){profile.empty().append(T($(e)))}});return profile};this.isLocked=function(){return u}}$(document).ready(function(){SteamProfile=new SteamProfile;SteamProfile.init()})