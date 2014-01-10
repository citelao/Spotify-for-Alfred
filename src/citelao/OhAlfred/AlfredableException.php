<?php
namespace OhAlfred;

// Stack return!
// http://stackoverflow.com/questions/1809404/get-exception-context-in-php
class AlfredableException extends \Exception implements IStatefullException  {
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

interface  IStatefullException { 
	function getState(); 
    function setState(array $state);
}