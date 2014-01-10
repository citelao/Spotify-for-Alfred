<?php
date_default_timezone_set('America/New_York');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Spotifious\Server;
require './vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
    	new WsServer(
    		new Server()
    	)
    ),
    33334
);

$server->run();