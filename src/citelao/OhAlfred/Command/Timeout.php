<?php
namespace OhAlfred\Command;

class Timeout {
	protected $script;

	public function __construct($timeout, $command) {
		$script = $command;

		$script .= " & sleep $timeout; kill -9 \$!";

		$this->script = $script;
	}

	public function run() {
		return exec($this->script);
	}
}