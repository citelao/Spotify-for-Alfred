<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
use Spotifious\Spotifious;
require 'vendor/autoload.php';

/**
 * Spotifious (v0.9)
 * 	a natural Spotify controller for Alfred <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

$alfred = new OhAlfred();
$spotifious = new Spotifious();

set_exception_handler(array($alfred, 'exceptionify'));
set_error_handler(array($alfred, 'errorify'), E_ALL);

$query = $argv[1];
$results = $spotifious->run($query);

$alfred->alfredify($results);