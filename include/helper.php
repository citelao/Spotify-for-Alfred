<?php

function trim_value(&$value) 
{ 
	$value = trim($value); 
}

function contains($stack, $needle) {
	return (strpos($stack, $needle) !== false);
}

function preg_contains($stack, $regex) {
	$matches = array();

	preg_match($regex, $stack, $matches);

	return (count($matches) == 0) ? false : $matches;
}

function alfredify($results) {
	print "<?xml version='1.0'?>\r\n<items>";
	
	foreach($results as $result) {
		if(!isset($result['arg']))
			$result['arg'] = 'null';
		
		if(!isset($result['title']))
			$result['title'] = 'null';
			
		if(!isset($result['icon']))
			$result['icon'] = 'icon.png';
		
		if(!isset($result['valid']))
			$result['valid'] = 'yes';

		if(!isset($result['uid']))
			$result['uid'] = time() . "-" . $result['title'];

		if(!isset($result['autocomplete']))
			$result['autocomplete'] = '';
			
		print "\r\n\r\n";
		print "	<item uid='" . escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . escapeQuery($result['valid']) . "' autocomplete='" . escapeQuery($result['autocomplete']) . "'>\r\n";
		print "		<title>" . escapeQuery($result['title']) . "</title>\r\n";
		print "		<subtitle>" . escapeQuery($result['subtitle']) . "</subtitle>\r\n";
		print "		<icon>" . escapeQuery($result['icon']) . "</icon>\r\n";
		print "	</item>\r\n";
	}
	
	print "</items>";
}

function errorify($error) {
	$titles = ['Aw, jeez!', 'Dagnabit!', 'Crud!', 'Whoops!', 'Oh, snap!', 'Aw, fiddlesticks!', 'Goram it!'];

	// Make a .log file
	$errordir = "/Users/citelao/Desktop/"; // TODO genericize
	$fname = date('Y-m-d h-m-s') . " Spotifious.log";
	$fdir = $errordir . $fname;
	$fcontents  = "# Error Log # \n";

	$fcontents .= "## Error Info ## \n";
	$fcontents .= $error->getMessage() . "\n";
	$fcontents .= "Line " . $error->getLine() . ", " . $error->getFile() . "\n\n";

	$fcontents .= "## Symbols ## \n";
	// TODO
	if(!is_a($error, "AlfredableException")) {
		$fcontents .= "This is not an Alfred-parsable exception.";
	} else {
		$fcontents .= print_r($error->getState(), true) . "\n";
	}
	$fcontents .= "\n\n";
	
	$fcontents .= "## Stack Trace ## \n";
	$fcontents .= print_r($error->getTrace(), true) . "\n";

	$log = fopen($fdir, "w");
	fwrite($log, $fcontents);
	fclose($log);

	$results = [
		[
			'title' => $titles[array_rand($titles)],
			'subtitle' => "Something went haywire. You can continue using Spotifious.",
			'valid' => "no",
			'icon' => 'include/images/alfred/error.png'
		],

		[
			'title' => $error->getMessage(),
			'subtitle' => "Line " . $error->getLine() . ", " . $error->getFile(),
			'valid' => "no",
			'icon' => 'include/images/alfred/info.png'
		],

		[
			'title' => "View log",
			'subtitle' => "Open new Finder window with .log file.",
			'icon' => 'include/images/alfred/folder.png',
			'arg' => $fdir
		]
	];

	alfredify($results);
	exit();
}

set_exception_handler('errorify');

// Stack return!
// http://stackoverflow.com/questions/1809404/get-exception-context-in-php
class AlfredableException extends Exception implements IStatefullException  {
    protected $throwState;

    private $forbidden = array("_POST", "_SERVER", "_GET", "_FILES", "_COOKIE");

    function __construct($message, $vars = '')   {
    	if($vars == '') {
	        $this->throwState = get_defined_vars();
	    } else {
	    	$vars = array_diff_key($vars, array_flip($this->forbidden));
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

function debug($text) {
	$results[0]['title'] = $text;

	alfredify($results);
}

function normalize($text) {
	return exec('./include/normalize "' . $text . '"');
}

function escapeQuery($text) {
	$text = str_replace("'", "’", $text);
	$text = str_replace('"', '\"', $text);
	$text = str_replace("&", "&amp;", $text);
	
	return $text;
}

function spotifyQuery() {
	$args = func_get_args();
	
	$script = "osascript -e 'tell application \"Spotify\"'";
	
	for ($i = 0; $i < func_num_args(); $i++) {
		$script .= " -e '" . $args[$i] . "'";
	}
	
	$script .= " -e 'end tell'";
	
	return normalize(exec($script));
}

function now() {
	$data = spotifyQuery('return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state');
	
	return split("✂", $data);
}

function popularitySort($a, $b) {
	if($a[popularity] == $b[popularity])
		return 0;
		
	return ($a[popularity] < $b[popularity]) ? 1 : -1;
}

// Thanks Jeff Johns <http://phpfunk.me/> and Robin Enhorn <https://github.com/enhorn/>
function fetch($url)
{
	 $ch = curl_init($url);
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	 curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	 $page    = curl_exec($ch);
	 $info    = curl_getinfo($ch);
	 curl_close($ch);

	 if($info['http_code'] != '200')
	 	throw new AlfredableException("fetch() failed; error code: " . $info['http_code']);
	 	

	 return ($info['http_code'] == '200') ? $page : null;
}

function floatToBars($decimal) {
	$line = ($decimal < 1) ? floor($decimal * 12) : 12;
	return str_repeat("𝗹", $line) . str_repeat("𝗅", 12 - $line);
}

function beautifyTime($seconds) {
	$m = floor($seconds / 60);
	$s = $seconds % 60;
	$s = ($s < 10) ? "0$s" : "$s";
	return  "$m:$s";
}