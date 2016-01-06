<?php
namespace OhAlfred\Command;

class Command {
	protected $script;

	public function __construct($command) {
		$this->script = $command;
	}

	public function run() {
		return exec($this->script);
	}
}