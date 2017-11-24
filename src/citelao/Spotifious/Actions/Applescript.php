<?php
namespace Spotifious\Actions;

use OhAlfred\Applescript\Applescript as ApplescriptHandler;
use OhAlfred\Applescript\ApplicationApplescript;
use OhAlfred\Exceptions\StatefulException;

use Spotifious\Actions\IAction;

class Applescript implements IAction {
	protected $action; 

	public function __construct($options, $alfred, $api) {
		if(!isset($options->command)) {
			throw new StatefulException("You have to have a command to run!");
		}

		if(isset($options->application)) {
			$this->action = new ApplicationApplescript($options->application, $options->command);
		} else {
			$this->action = new ApplescriptHandler($options->command);
		}
		
	}

	public function run() {
		$this->action->run();
	}
}
