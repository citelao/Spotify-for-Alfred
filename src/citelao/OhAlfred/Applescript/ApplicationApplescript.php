<?php
namespace OhAlfred\Applescript;

use OhAlfred\Applescript\Applescript;

class ApplicationApplescript extends Applescript {
	public function __construct($app) {
		$args = func_get_args();
		array_shift($args);

		array_unshift($args, 'tell application "' . $app . '"');
		array_push($args, 'end tell');

		return call_user_func_array('parent::__construct', $args);
	}
}