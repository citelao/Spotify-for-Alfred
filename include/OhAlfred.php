<?php
mb_internal_encoding("UTF-8");
// include_once('include/helper.php');

/* see: https://github.com/jdfwarrior/Workflows/blob/master/workflows.php */
final class OhAlfred {
	private $results; // TODO implement

	private $home;
	private $cache;
	private $storage;

	// TODO Instantiate the class or this will never be called
	public function __construct() {
		$this->home = exec('printf "$HOME"');
		$this->cache = $this->home . '/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/com.citelao.spotifious/';
		$this->storage = $this->home . '/Library/Application Support/Alfred 2/Workflow Data/com.citelao.spotifious/';

		if (!file_exists($this->cache)) {
			mkdir($this->cache);
		}

		if (!file_exists($this->storage)) {
			mkdir($this->storage);
		}
	}

	public function alfredify($r = null) {
		if($r == null)
			$r = $results;

		print "<?xml version='1.0'?>\r\n<items>";
		
		foreach($r as $result) {
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

			if(!isset($result['subtitle']))
				$result['subtitle'] = '';
				
			print "\r\n\r\n";
			print "	<item uid='" . OhAlfred::escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . OhAlfred::escapeQuery($result['valid']) . "' autocomplete='" . OhAlfred::escapeQuery($result['autocomplete']) . "'>\r\n";
			print "		<title>" . OhAlfred::escapeQuery($result['title']) . "</title>\r\n";
			print "		<subtitle>" . OhAlfred::escapeQuery($result['subtitle']) . "</subtitle>\r\n";
			print "		<icon>" . OhAlfred::escapeQuery($result['icon']) . "</icon>\r\n";
			print "	</item>\r\n";
		}
		
		print "</items>";
	}

	public function errorify($error) {
		$titles = ['Aw, jeez!', 'Dagnabit!', 'Crud!', 'Whoops!', 'Oh, snap!', 'Aw, fiddlesticks!', 'Goram it!'];

		// Make a .log file
		$errordir = $this->cache;
		$fname = date('Y-m-d h-m-s') . " Spotifious.log";
		$fdir = $errordir . $fname;
		$fcontents  = "# Error Log # \n";

		$fcontents .= "## Error Info ## \n";
		$fcontents .= $error->getMessage() . "\n";
		$fcontents .= "Line " . $error->getLine() . ", " . $error->getFile() . "\n\n";

		$fcontents .= "## Symbols ## \n";
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

		OhAlfred::alfredify($results);
		exit();
	}

	public function normalize($text) {
		return exec('./include/normalize "' . $text . '"');
	}

	public function debug($text) {
		$results[0]['title'] = $text;

		OhAlfred::alfredify($results);
		exit();
	}

	// Thanks Jeff Johns <http://phpfunk.me/> and Robin Enhorn <https://github.com/enhorn/>
	public function fetch($url)
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

	public function escapeQuery($text) {
		$text = str_replace("'", "’", $text);
		$text = str_replace('"', '\"', $text);
		$text = str_replace("&", "&amp;", $text);
		
		return $text;
	}

}

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