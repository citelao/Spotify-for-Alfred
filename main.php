<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result/?p=10197
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
use OhAlfred\StatefulException;
use Spotifious\Menus;
use Spotifious\Helper as MenuHelper;
require 'src/citelao/Spotifious/helper_functions.php'; // TODO be prettier
require 'vendor/autoload.php';

/**
 * Spotifious (v0.7)
 * 	a natural Spotify controller for Alfred 
 *  <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 *
 * 'main.php'
 *  For sanity's sake, here is in plain English what this file does.
 *
 *  This file determines the correct menu to show and passes pure *data* to the
 *  Spotifious menus class. If I used MVC (which is unwarranted for such a tiny
 *  project), this would be the controller passing output to the view.
 *
 *  The idea is to do all the query parsing code here and have the spotifious
 *  file do the display work. Therefore this isn't legit MVC because the display
 *  code does a lot of work; it doesn't just display strings handed to it from 
 *  this file.
 *
 *  So here's what we do here:
 *
 *  First, if we haven't fully configured Spotifious, show configuration.
 *  If we need to load the country code submenu of configuration, do that, too.
 *
 *  Now if we are configured and the query is tiny, we have three options:
 *   1. First letter is 'c': show the control panel
 *   2. First letter is 's': show settings
 *   3. Otherwise:           show the main menu
 *
 *  Handle searches containing Spotifious-gen'd content (anything with `⟩`):
 * 	 Deal with detail menus (last char is `⟩`)
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ Transatlanticism ⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩ Transatlanticism ⟩
 *
 *   Also our filter searches, narrowing a detail menu (# of ⟩ > # of URIs)
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ Transatlanticism ⟩ Lightness
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩⟩ Expo '86
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩ Transatlanticism
 *
 *   Then regular searches including gen'd content (0 < # of ⟩ <= # of URIs)
 *    spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ The Shins
 *    spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Tally Hall
 *
 *  Now handle URLS & URIs
 *   URL: http://open.spotify.com/artist/5lsC3H1vh9YSRQckyGv0Up
 *   URI: spotify:artist:7lqaPghwYv2mE9baz5XQmL
 *
 *  Everything else is a search.
 */

/* Instantiate OhAlfred output class */
$alfred = new OhAlfred();

/* Parse the query. */
$results = array();
$query = $argv[1];
// $query   = $alfred->normalize($argv[1]);
$query = str_replace("►", "⟩", $query);

/* If Spotifious isn't configured yet, show the checklist. */
if(!MenuHelper::configured()) {

	if(mb_strstr($query, 'Country Code ⟩')) {
		$search = mb_substr($query, 14);
		$results = Menus::countries($search);
	} else {
		$results = Menus::configure();
	}
	
	$alfred->alfredify($results);
	return;
}

if (mb_strlen($query) <= 3) {
	switch (substr($query, 0, 1)) {
		case 'c':
			$results = Menus::controls();
			break;

		case 's':
			$results = Menus::settings();
			break;

		default:
			$results = Menus::main();
			break;
	}
} elseif(contains($query, '⟩')) {
	// if the query contains any machine-generated text 
	// (the unicode `⟩` is untypeable so we check for it)
	// we need to parse the query and extract the URLs.
	
	// So split based on the delimeter `⟩` and excise the delimeter and blanks.
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
		$results = Menus::detail($URIs, $args, $depth);
	} elseif($depth > 0) {
		$search = array_pop($args);
		$results = Menus::detail($URIs, $args, $depth, $search);
	} else {
		$results = Menus::search(end($args), $alfred->options('country'));
	}
} elseif (contains($query, 'http://')) {
	// Explode the URL and arguments into bits for harvesting.

	// Regex for URLs: http://open\.spotify\.com/(album|track|user/[^/]+/playlist)/([a-zA-Z0-9]+)
	// <https://github.com/felixtriller/spotify-embed/blob/master/spotify-embed.php>
	$trimmedQuery = preg_replace('/http:\/\/[^\/]+\/|\//', ' ', $query);
	$splitQuery = explode(' ', $trimmedQuery); // TODO replace with preg_match

	// Craft a URI from the URL.
	// TODO make work for apps and playlists
	$URI = 'spotify:' . $splitQuery[1] . ':' . $splitQuery[2];

	$results = Menus::convertable($URI);

} elseif(contains($query, 'spotify:')) {
	// Based off https://github.com/felixtriller/spotify-embed/blob/master/spotify-embed.php
	// Does not use `is_spotify_uri` because it also checks for random text at the end (which it strips).
	// TODO: "app:" URLS
	$parts = preg_contains($query, '/^(spotify:(?:album|artist|app|track|user:[^:]+:playlist):[a-zA-Z0-9]+)(?: )+([^\n]*)$/x');

	if($parts === false) 
		throw new StatefulException("Invalid Spotify URI", get_defined_vars());

	$URI = $parts[1];

	$results = Menus::convertable($URI);

} else {
	$results = Menus::search($query, $alfred->options('country'));
}

$alfred->alfredify($results);