<?php
namespace OhAlfred\Exceptions;

use OhAlfred\Exceptions\IStatefulException;

class StatefulException extends \Exception implements IStatefulException {
	protected $throwState;

	// List of things that should never be written to debug files.
	protected $forbidden = array("_POST", "_SERVER", "_GET", "_FILES", "_COOKIE");

	function __construct($message, $vars = '')   {
		if($vars == '') {
	        $this->throwState = get_defined_vars();
	    } else {
	    	$vars = array_diff_key($vars, array_flip($this->forbidden)); // Take out all private things.
	    	$this->throwState = $vars;
	    }

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