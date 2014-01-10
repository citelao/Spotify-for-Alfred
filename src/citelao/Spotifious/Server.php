<?php
namespace Spotifious;
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
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$this->console("{$from->resourceId}: $msg \n");
    	
        $from->send("Got your message!");
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->clients->detach($conn);

		$this->console("{$conn->resourceId} detached.");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    // Inspired by Sann-Remy Chea <http://srchea.com>
	protected function console($msg, $die = false) {
		$craftedMsg = date('[Y-m-d H:i:s]') . " $msg\r\n";

		if($die)
			die($craftedMsg);

		echo $craftedMsg;
	}
}