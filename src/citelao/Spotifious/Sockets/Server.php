<?php
namespace Spotifious\Sockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface {
	protected $clients;
    protected $desired;
    protected $messageCallback;
    protected $logCallback;

	public function __construct($desirata, $logCallback = null, $messageCallback = null) {
        $this->logCallback = $logCallback;
        $this->messageCallback = $messageCallback;

		$this->clients = new \SplObjectStorage;  

        $desirata[] = 'close';
        $this->desired = $desirata;
	}

    public function onOpen(ConnectionInterface $conn) {
    	$this->clients->attach($conn);

    	$this->log("New Connection! {$conn->resourceId}");

        if ($this->desired != null) {
            $this->log("Telling connection what we need.");
            $conn->send($this->desired[0]);
        } else {   
            $this->log("No data to fetch. Detaching connection.");
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$this->log("Client {$from->resourceId}: $msg");

        if($this->messageCallback != null)
            call_user_func($this->messageCallback, $msg);

        array_shift($this->desired);

        if ($this->desired != null) {
            $this->log("Telling connection what else we need.");
            $from->send($this->desired[0]);
        } else {
            $this->log("All data fetched. Detaching connection.");
            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->clients->detach($conn);

		$this->log("{$conn->resourceId} detached. Shutting down.");
        exit(0);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log("Error!");
        // TODO log
        exit(0);
    }

    // Inspired by Sann-Remy Chea <http://srchea.com>
    // TODO actually log
	protected function log($msg, $die = false) {
		$craftedMsg = date('[Y-m-d H:i:s]') . " $msg\r\n";

        if($this->logCallback != null)
            call_user_func($this->logCallback, $msg, $die);

		if($die)
			die($craftedMsg);

		echo $craftedMsg;
	}
}