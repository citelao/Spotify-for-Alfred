<?php
namespace Spotifious\Sockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface {
	protected $clients;
    protected $desired;

    protected $messageCallback;
    protected $logCallback;
    protected $doneCallback;

	public function __construct($desirata, $messageCallback = null, $doneCallback = null, $logCallback = null) {
        $this->messageCallback = $messageCallback;
        $this->doneCallback = $doneCallback;
        $this->logCallback = $logCallback;

		$this->clients = new \SplObjectStorage;  

        $desirata[] = 'close';
        $this->desired = $desirata;
	}

    public function onOpen(ConnectionInterface $conn) {
    	$this->clients->attach($conn);

    	$this->log("New Connection! {$conn->resourceId}");

        if ($this->desired != null) {
            $this->log("Telling connection we need {$this->desired[0]}");
            $conn->send($this->desired[0]);
        } else {   
            $this->log("No data to fetch. Detaching connection.");

            if($this->doneCallback != null)
                call_user_func($this->doneCallback);

            $conn->close();
            $this->stop();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	$this->log("Client {$from->resourceId}: $msg");

        if($this->messageCallback != null)
            call_user_func($this->messageCallback, $msg);

        array_shift($this->desired);

        if ($this->desired != null) {
            $this->log("Telling connection we also need {$this->desired[0]}");
            $from->send($this->desired[0]);
        } else {
            $this->log("All data fetched. Detaching connection.");

            if($this->doneCallback != null)
                call_user_func($this->doneCallback, false);

            $from->close();
        }
    }

    public function onClose(ConnectionInterface $conn) {
    	$this->clients->detach($conn);

        if($this->doneCallback != null)
            call_user_func($this->doneCallback, false); 

		$this->log("{$conn->resourceId} detached. Shutting down.");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();

        // Calls doneCallback with argument (isError==true)
        if($this->doneCallback != null)
            call_user_func($this->doneCallback, true);
        
        // TODO log
        $this->log("Error!");
    }

    // Inspired by Sann-Remy Chea <http://srchea.com>
    // TODO actually log to file.
	protected function log($msg, $die = false) {
		$craftedMsg = date('[Y-m-d H:i:s]') . " $msg\r\n";

        if($this->logCallback != null)
            call_user_func($this->logCallback, $msg, $die);

		if($die)
			die($craftedMsg);

		echo $craftedMsg;
	}
}