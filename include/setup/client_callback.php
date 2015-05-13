<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
require '../../vendor/autoload.php';

$response = Array();

if(!$_POST["id"] || !$_POST["secret"]) {
	$response["status"] = "error";
	$response["message"] = "You're missing some data!";
	echo json_encode($response);
	exit();
}

$alfred = new OhAlfred();

// Save data

// Test connection

$response["status"] = "error";
$response["message"] = "Have't implemented saving yet!";
echo json_encode($response);
exit();