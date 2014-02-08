<?php
namespace Spotifious\Sockets;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use OhAlfred\ApplicationApplescript;

// Creates a WebSocket server, then tells Spotifious' helper app to launch the client.
// Fetches the data from it, then returns.
class Fetcher {
	protected $server;
	protected $port;

	protected $desirata;
	protected $data;

	protected $debug;

	public function __construct($desirata, $debug = false) {
		$this->debug = $debug;

		$this->desirata = $desirata;

		$this->server = IoServer::factory(
		    new HttpServer(
		    	new WsServer(
		    		new Server($this->desirata, array($this, 'handleMessage'), array($this, 'handleDone'), array($this, 'handleLog'), $this->debug)
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

	public function data() {
		return $this->data;
	}

	public function handleMessage($msg) {
		// TODO parse out errors

		$this->data[] = $msg;
	}

	public function handleDone($isError) {
		$this->server->loop->stop();
	}

	public function handleLog($msg, $die) {
		// TODO? it already echos.
	}


	// https://github.com/vdesabou/alfred-spotify-mini-player/blob/master/functions.php
	protected function openPort() {
		$from = 10000;
		$to = 20000;
		$host = 'localhost';
         
        for($port = $from; $port <= $to ; $port++) {
			//avoid warnings like this "PHP Warning:  fsockopen(): unable to connect to localhost (Connection refused)"
		    $fp = @fsockopen($host, $port);
               	if (!$fp) {
                        //port is free
                        return $port;
               	} else {
                        // we opened a connection to a port, close it
                        fclose($fp);
               	}
        }

        // TODO return an error
        return 17693;
	}
}