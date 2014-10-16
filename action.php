<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
use Spotifious\Spotifious;
require 'vendor/autoload.php';

$spotifious = new Spotifious();

$action = $argv[1];
$results = $spotifious->process($action);

// For debugging
// print_r($action);
// print("\n");

print_r($results);