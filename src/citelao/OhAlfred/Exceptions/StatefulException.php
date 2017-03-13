<?php
namespace OhAlfred\Exceptions;

use OhAlfred\Exceptions\IStatefulException;

class StatefulException extends \Exception implements IStatefulException {
	protected $throwState;

	// List of things that should never be written to debug files.
	protected $forbidden = array("_POST", "_SERVER", "_GET", "_FILES", "_COOKIE", "api");

	function __construct($message, $vars = [])   {
	    $all_vars = array_merge(get_defined_vars(), $vars);
	    $this->throwState = array_diff_key($all_vars, array_flip($this->forbidden)); // Take out all private things.

	    parent::__construct($message);
	}

	function getState() {
	    return $this->throwState;
	}

	function setState(array $state) {
	    $this->throwState = $state;
	    return $this;
	}
}