<?php


/**
 * INCOMPLETE; DO NOT USE
 **/


mb_internal_encoding("UTF-8");
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

// Reimplimenting sockets because Ratchet looks to be such high overhead.
// Inspired by Sann-Remy Chea <http://srchea.com>
class SocketServer {

	protected $running = false;

	protected $sockets;
	protected $address = '127.0.0.1';
	protected $port = 17889;

	public function __construct() {
		$this->console("Constructing server");

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// socket_set_option($socket, level, optname, optval);

		if(!is_resource($socket))
			$this->console("socket_create() failed: ".socket_strerror(socket_last_error()), true);

		if(!socket_bind($socket, $this->address, $this->port))
			$this->console("socket_bind() failed: ".socket_strerror(socket_last_error()), true);

		if(!socket_listen($socket, 20))
			$this->console("socket_listen() failed: ".socket_strerror(socket_last_error()), true);

		$this->socket = $socket;
		$this->console("Server started");
	}

	public function run() {
		$this->console("Begin running.");

		$this->running = true;
		while ($this->running) {
			if (!socket_set_block($this->socket))
		        $this->console('Unable to set blocking mode for socket', true);

		    $buffer = '';
		    $from = '';
		    $port = 0;
		    $bytesReceived = socket_recvfrom($this->socket, $buffer, 65536, 0, $from, $port);

		    if($bytesReceived == false)
		    	$this->console('An error occured while receiving from socket.', true);

		    $this->console("Received $buffer from $from.");
		}
	}

	protected function console($msg, $die = false) {
		$craftedMsg = date('[Y-m-d H:i:s]') . " $msg\r\n";

		if($die)
			die($craftedMsg);

		echo $craftedMsg;
	}
}

$srv = new SocketServer();
$srv->run();