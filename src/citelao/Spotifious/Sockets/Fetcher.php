<?php
namespace Spotifious\Sockets;

class Fetcher {
	public $port;

	protected $server;

	public function __construct() {
		$port = $this->openPort();

		$server = IoServer::factory(
		    new HttpServer(
		    	new WsServer(
		    		new Server()
		    	)
		    ),
		    $port
		);
	}

	public function run() {
		$this->server->run();
	}

	// https://github.com/vdesabou/alfred-spotify-mini-player/blob/master/functions.php
	protected function openPort() {
		//avoid warnings like this PHP Warning:  fsockopen(): unable to connect to localhost (Connection refused) 
        error_reporting(~E_ALL);
        
		$from = 10000;
		$to = 20000;
		$host = 'localhost';
         
        for($port = $from; $port <= $to ; $port++) {
          $fp = fsockopen($host, $port);
                if (!$fp) {
                        //port is free
                        return $port;
                }
                else 
                {
                        // we opened a connection to a port, close it
                        fclose($fp);
                }
        }
        
        // TODO return an error
        return 17693;
	}
}