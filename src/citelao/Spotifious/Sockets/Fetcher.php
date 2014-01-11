<?php
namespace Spotifious\Sockets;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use OhAlfred\ApplicationApplescript;

class Fetcher {
	protected $server;
	protected $port;

	protected $desirata;

	public function __construct($desirata) {
		$this->desirata = $desirata;

		$this->server = IoServer::factory(
		    new HttpServer(
		    	new WsServer(
		    		new Server($this->desirata, array($this, 'handleMessage'), array($this, 'handleLog'))
		    	)
		    ),
		    $this->port()
		);
	}

	public function port() {
		if($this->port == null)
			$this->port = $this->openPort();

		return $this->port;
	}

	public function run() {
		$clientStarter = new ApplicationApplescript('Spotify', 'open location "spotify:app:spotifious:socket:' . $this->port() . '"');
		$clientStarter->run();
		$this->server->run();
	}

	public function handleLog($msg, $die) {
		// TODO? it already echos.
	}

	public function handleMessage($msg) {
		// TODO get data.
	}

	// https://github.com/vdesabou/alfred-spotify-mini-player/blob/master/functions.php
	protected function openPort() {
		$from = 10000;
		$to = 20000;
		$host = 'localhost';
         
        for($port = $from; $port <= $to ; $port++) {
			//avoid warnings like this PHP Warning:  fsockopen(): unable to connect to localhost (Connection refused) 
		    $fp = @fsockopen($host, $port);
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