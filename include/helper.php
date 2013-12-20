<?php
mb_internal_encoding("UTF-8");
include_once('include/OhAlfred.php');

function trim_value(&$value) 
{ 
	$value = trim($value);
}

function contains($stack, $needle) {
	return (strpos($stack, $needle) !== false);
}

function preg_contains($stack, $regex) {
	$matches = array();

	preg_match($regex, $stack, $matches);

	return (count($matches) == 0) ? false : $matches;
}

function is_spotify_uri($item) {
	$regex = '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x';

	return preg_match($regex, $item);
}

function applescriptQuery() {
	$args = func_get_args();

	$script = "osascript ";

	for ($i = 0; $i < func_num_args(); $i++) {
		$script .= " -e '" . $args[$i] . "'";
	}

	return OhAlfred::normalize(exec($script));
}

function spotifyQuery() {
	$args = func_get_args();
	
	array_unshift($args, 'tell application "Spotify"');
	array_push($args, 'end tell');

	return call_user_func_array('applescriptQuery', $args);

	// $script = "osascript -e 'tell application \"Spotify\"'";
	
	// for ($i = 0; $i < func_num_args(); $i++) {
	// 	$script .= " -e '" . $args[$i] . "'";
	// }
	
	// $script .= " -e 'end tell'";
	
	// return OhAlfred::normalize(exec($script));
}

function now() {
	$data = spotifyQuery('return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state');
	
	return split("✂", $data);
}

function popularitySort($a, $b) {
	if($a[popularity] == $b[popularity])
		return 0;
		
	return ($a[popularity] < $b[popularity]) ? 1 : -1;
}

function floatToBars($decimal) {
	$line = ($decimal < 1) ? floor($decimal * 12) : 12;
	return str_repeat("𝗹", $line) . str_repeat("𝗅", 12 - $line);
}

function beautifyTime($seconds) {
	$m = floor($seconds / 60);
	$s = $seconds % 60;
	$s = ($s < 10) ? "0$s" : "$s";
	return  "$m:$s";
}

function hotkeysConfigured() {
	// Check .plist for binds on `spot`
	// TODO
	return true;
}

function helperAppConfigured() {
	global $alfred;

	// Temp
	return true;

	if(is_link($alfred->home() . "/Spotify/spotifious-helper") || file_exists($alfred->home() . "/Spotify/spotifious-helper"))
		return true;

	return false;
}

function countryCodeConfigured() {
	// Check file storage location for country code.
	global $alfred;

	return $alfred->options('country');
}

function countrySort($a, $b) {
	// Give priority to common countries.
	// Arbitrarily decided by me.
	$common = array(
		"United States", // America first hehehe
		"United Kingdom",
		"Canada",
		"Australia"
	);

	if(in_array($a['title'], $common)) {
		if (in_array($b['title'], $common)) {
			return  array_search($a['title'], $common) - array_search($b['title'], $common);
		} else {
			return -1;
		}
	}

	if(in_array($b['title'], $common))
		return 1;

	// If neither is common, do a simple alphabetical order.
	return strcmp($a['title'], $b['title']);
}
