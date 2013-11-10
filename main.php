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

/* Parse the query. */
$results = array();
$query   = normalize($argv[1]);

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

	if(preg_contains($splitQuery[1], '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x'))
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
	$parts = array_filter(preg_contains($query, '/^(spotify:(?:album|artist|app|track|user:[^:]+:playlist):[a-zA-Z0-9]+)(?: )+([^\n]*)$/x'));

	if($parts === false) throw new Exception("Invalid Spotify URI");

	$URI = $parts[1];

	$results = Spotifious::convertableInfo($URI);

} else {
	$results = Spotifious::search($query);

}

alfredify($results);