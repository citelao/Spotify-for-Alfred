<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');
ini_set("display_errors", "stderr");

use OhAlfred\OhAlfred;
use Spotifious\Spotifious;
require 'vendor/autoload.php';

/**
 * Spotifious
 * 	a natural Spotify controller for Alfred <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

$alfred = new OhAlfred();

set_exception_handler(array($alfred, 'exceptionify'));
try {
	$spotifious = new Spotifious($alfred);

	$query = $argv[1];
	$results = $spotifious->run($query);

	$alfred->alfredify($results);
} catch(Exception $e) {
	$alfred->exceptionify($e);
}