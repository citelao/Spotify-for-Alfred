<?php
namespace Spotifious\Sockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}

    public function onOpen(ConnectionInterface $conn) {
    	$this->clients->attach($conn);

    	$this->console("New Connection! {$conn->resourceId}");

        $this->console("Telling connection what we need.");
        $conn->send("Hello, new connection. Here's what you need to get: TODO \n");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$this->console("{$from->resourceId}: $msg");

        $this->console("Acking receipt");
        $from->send("Got your message!\n");
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->clients->detach($conn);

		$this->console("{$conn->resourceId} detached.");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->console("Error!");
        // TODO log
    }

    // Inspired by Sann-Remy Chea <http://srchea.com>
	protected function console($msg, $die = false) {
		$craftedMsg = date('[Y-m-d H:i:s]') . " $msg\r\n";

		if($die)
			die($craftedMsg);

		echo $craftedMsg;
	}
}