<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');
ini_set("display_errors", "stderr");

use OhAlfred\OhAlfred;
use Spotifious\Spotifious;
require 'vendor/autoload.php';

$alfred = new OhAlfred();
try{
	$spotifious = new Spotifious($alfred);

	$action = $argv[1];
	$results = $spotifious->process($action);

	// For debugging
	// print_r($action);
	// print("\n");

	print_r($results);
} catch(Exception $e) {
	$alfred->exceptionify($e, false);
	throw $e;
}