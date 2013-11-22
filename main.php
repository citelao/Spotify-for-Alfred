<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
include_once('include/helper.php');
include_once('include/OhAlfred.php');
include_once('include/spotifious.php');

/**
 * Spotifious (v0.7)
 * 	a natural Spotify controller for Alfred 
 *  <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

/* Oh, Alfred! */
$alfred = new OhAlfred();
set_exception_handler(array($alfred, 'errorify'));

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
		'subtitle' => 'Type `s` from the main menu', // TODO implement settings
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
	// TODO $storage
	// https://raw.github.com/johannesl/Internationalization/master/countrycodes.json
	return true;
}

/* Parse the query. */
$results = array();
$query   = OhAlfred::normalize($argv[1]);

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
 *   Last char is ⟩
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
	// if the query contains any machine-generated text 
	// (the unicode ⟩ is untypeable so we check for it)
	// we need to parse the query and extract the URLs.
	
	// So split based on the delimeter "⟩" and excise the delimeter and blanks.
	$splitQuery  = array_filter(str_replace("⟩", "", explode("⟩", $query)));
	               array_walk($splitQuery, 'trim_value');

	$URIs = array_filter($splitQuery, 'is_spotify_uri');
	$args = array_diff($splitQuery, $URIs);

	// Find which URI to use (by count, not by array index).
	// Arrows should be twice the number of URIs for the last URI.
	// For every one arrow fewer, traverse one URI backwards. 
	$arrows = mb_substr_count($query, "⟩");
	$depth = count($URIs) - (2 * count($URIs) - $arrows); // equiv to $arrows - count($URIs).

	if (mb_substr($query, -1) == "⟩") { // Machine-generated
		$results = Spotifious::detail($URIs, $args, $depth);
	} elseif($depth > 0) {
		$search = array_pop($args);
		$results = Spotifious::filteredSearch($URIs, $args, $depth, $search);
	} else {
		$results = Spotifious::search(end($args));
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

OhAlfred::alfredify($results);