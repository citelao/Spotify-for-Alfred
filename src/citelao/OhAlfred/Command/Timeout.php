<?php
namespace OhAlfred\Command;

use OhAlfred\OhAlfred;

class Timeout {
	protected $script;
	protected $alfred;

	public function __construct($timeout, $command) {
		$script = "(";

		$script .= $command;

		$script .= " && sleep $timeout; kill -9 \$!) > /dev/null 2>/dev/null &";

		echo($script);

		$this->script = $script;
	}

	public function run() {
		return exec($this->script);
	}
}