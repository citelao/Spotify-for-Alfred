<?php
namespace OhAlfred;

class AppleScript {
	protected $script;

	public function __construct() {
		$args = func_get_args();

		$script = "osascript ";

		for ($i = 0; $i < func_num_args(); $i++) {
			$script .= " -e '" . $args[$i] . "'";
		}

		$this->script = $script;
	}

	public function run() {
		return exec($this->script);
	}

	// public function normalize($text) {
	// 	return exec('./include/normalize "' . $text . '"');
	// }
}