<?php
date_default_timezone_set('America/New_York');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Spotifious\Sockets\Fetcher;
require './vendor/autoload.php';

$desirata = array(
	'current_track',
	'now_playing'
	);

$fetcher = new Fetcher($desirata);
$fetcher->run();
