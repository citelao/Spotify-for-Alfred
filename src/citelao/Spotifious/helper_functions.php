<?php

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