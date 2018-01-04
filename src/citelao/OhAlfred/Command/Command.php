<?php
namespace OhAlfred\Command;

class Command {
	protected $script;
	protected $return_status = 0;

	public function __construct($command) {
		$this->script = $command;
	}

	public function run() {
		$unused;
		return exec($this->script, $unused, $this->return_status);
	}

	public function status() {
		return $this->return_status;
	}
}
