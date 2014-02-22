<?php
date_default_timezone_set('America/New_York');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Spotifious\Sockets\Fetcher;
require './vendor/autoload.php';

echo "begin \r\n";

$desirata = array(
	'current_track_id',
	'now',
	array('star', 'spotify:track:62jDF5iO6aoxbHJHrQyqG6')
	);

$fetcher = new Fetcher($desirata);
$fetcher->run();

print_r($fetcher->data());