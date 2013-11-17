<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
include_once('include/helper.php');
include_once('include/spotifious.php');

/**
 * Spotifious (v0.7)
 * 	a natural Spotify controller for Alfred 
 *  <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

/* If Spotifious isn't configured yet, show the checklist. */
if(!hotkeys_configured() || !helper_app_configured() || !country_code_configured()) { // todo
	$results[] = [
		'title' => 'Welcome to Spotifious!',
		'subtitle' => 'You need to configure a few more things before you can use Spotifious.',
		'icon' => 'include/images/alfred/configuration.png',
		'valid' => 'no'
	];

	if(hotkeys_configured()) {
		$results[] = [
			'title' => '1. Bind your hotkeys',
			'subtitle' => 'Action this to bind automatically, or set them yourself in Alfred preferences.',
			'icon' => 'include/images/alfred/checked.png',
			'valid' => 'no'
		];
	} else {
		$results[] = [
			'title' => '1. Bind your hotkeys',
			'subtitle' => 'Action this to bind automatically, or set them yourself in Alfred preferences.',
			'icon' => 'include/images/alfred/unchecked.png'
		];
	}

	if(helper_app_configured()) {
		$results[] =[
			'title' => '2. Install the helper app in Spotify',
			'subtitle' => 'This will open your web browser so you can activate Spotify developer mode.',
			'icon' => 'include/images/alfred/checked.png',
			'valid' => 'no'
		];
	} else {
		$results[] =[
			'title' => '2. Install the helper app in Spotify',
			'subtitle' => 'This will open your web browser so you can activate Spotify developer mode.',
			'icon' => 'include/images/alfred/unchecked.png'
		];
	}
	
	if(country_code_configured()) {
		$results[] = [
			'title' => '3. Find your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => 'include/images/alfred/checked.png',
			'valid' => 'no'
		];
	} else {
		$results[] = [
			'title' => '3. Find your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => 'include/images/alfred/unchecked.png'
		];
	}

	$results[] = [
		'title' => 'You can access settings easily',
		'subtitle' => 'Type `s` from the main menu',
		'icon' => 'include/images/alfred/info.png'
	];

	alfredify($results);
	return;
}

function hotkeys_configured()
{
	// Check .plist for binds on `spot`
	// TODO
	return true;
}

function helper_app_configured()
{
	// TODO genericize
	if(is_link("/Users/citelao/Spotify/spotifious-helper") || file_exists("/Users/citelao/Spotify/spotifious-helper"))
		return true;

	return false;
}

function country_code_configured()
{
	// Check file storage location for country code.
	// TODO
	return true;
}

/* Parse the query. */
$results = array();
$query   = normalize($argv[1]);

$URIregex = '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x'; // TODO use more

/**
 * Determine screen to show
 *  So I figure this could do with some outlining.
 *
 *  First, if the query is tiny, we have one of two options:
 *   1. First letter is 'c': show the control panel
 *   2. Otherwise:           show the main menu
 *
 *  Deal with our detail menus.
 *   # of ⟩ >= # of URIs
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ Transatlanticism ⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩ Transatlanticism ⟩
 *
 *  Also our filter searches:
 *   # of ⟩ > # of URIs
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ Transatlanticism ⟩ Lightness
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩⟩ Expo '86
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩ Transatlanticism
 *
 *  Then our searches containing Spotifious-generated information:
 *   0 < # of ⟩ <= # of URIs
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ The Shins
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Tally Hall
 *
 *  Now handle URLS & URIs
 *   URL: http://open.spotify.com/artist/5lsC3H1vh9YSRQckyGv0Up
 *   URI: spotify:artist:7lqaPghwYv2mE9baz5XQmL
 *
 *  Everything else is a search.
 */
if (mb_strlen($query) <= 3) {
	if(substr($query, 0, 1) == "c") {
		$results = Spotifious::controlPanel();
	} else {
		$results = Spotifious::mainMenu();
	}

} elseif(contains($query, '⟩')) {
	$splitQuery  = str_replace("⟩", "", explode("⟩", $query));
	               array_walk($splitQuery, 'trim_value');

	$URIs = array($splitQuery[0]);

	if(preg_contains($splitQuery[1], $URIregex))
		array_push($URIs, $splitQuery[1]);

	$args = array_filter(array_slice($splitQuery, count($URIs)));



	// TODO wut
	if (mb_substr_count($query, "⟩") == count($URIs) * 2) {
		// deepest
		$results = Spotifious::detail($URIs, $args);
	} elseif(mb_substr_count($query, "⟩") > count($URIs)) {
		// less deep
		$results = Spotifious::detail(array($URIs[0]), $args);
	} else {
		$results = Spotifious::search($splitQuery[count($splitQuery)-1]);
	}

} elseif (contains($query, 'http://')) {
	// Explode the URL and arguments into bits for harvesting.

	// Regex for URLs: http://open\.spotify\.com/(album|track|user/[^/]+/playlist)/([a-zA-Z0-9]+)
	// https://github.com/felixtriller/spotify-embed/blob/master/spotify-embed.php 
	$trimmedQuery = preg_replace('/http:\/\/[^\/]+\/|\//', ' ', $query);
	$splitQuery = explode(' ', $trimmedQuery); // TODO replace with preg_match

	// Craft a URI from the URL.
	// TODO make work for apps and playlists
	$URI = 'spotify:' . $splitQuery[1] . ':' . $splitQuery[2];

	$results = Spotifious::convertableInfo($URI);

} elseif(contains($query, 'spotify:')) {
	// Based off https://github.com/felixtriller/spotify-embed/blob/master/spotify-embed.php
	// TODO: "app:" URLS
	$parts = preg_contains($query, '/^(spotify:(?:album|artist|app|track|user:[^:]+:playlist):[a-zA-Z0-9]+)(?: )+([^\n]*)$/x');

	if($parts === false) throw new AlfredableException("Invalid Spotify URI", get_defined_vars());

	$URI = $parts[1];

	$results = Spotifious::convertableInfo($URI);

} else {
	$results = Spotifious::search($query);

}

alfredify($results);