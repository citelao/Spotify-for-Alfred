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

function spotifyQuery() {
	$args = func_get_args();
	
	$script = "osascript -e 'tell application \"Spotify\"'";
	
	for ($i = 0; $i < func_num_args(); $i++) {
		$script .= " -e '" . $args[$i] . "'";
	}
	
	$script .= " -e 'end tell'";
	
	return OhAlfred::normalize(exec($script));
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