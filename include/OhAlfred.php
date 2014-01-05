<?php
mb_internal_encoding("UTF-8");

/* see: https://github.com/jdfwarrior/Workflows/blob/master/workflows.php */
final class OhAlfred {
	private $results; // TODO implement

	private $name;
	private $home;
	private $workflow;
	private $cache;
	private $storage;

	private $alfredPrefs;
	private $notificationMethod;

	public function __construct() {
		set_exception_handler(array($this, 'errorify'));
	}

	public function name()
	{
		if($this->name == null)
			$this->name = $this->defaults('bundleid');

		return $this->name;
	}

	public function home()
	{
		if($this->home == null)
			$this->home = exec('printf "$HOME"');

		return $this->home;
	}

	public function workflow()
	{
		if($this->workflow == null)
			$this->workflow = dirname(dirname(__FILE__)); // Because I keep OhAlfred in the include/ directory.

		return $this->workflow;
	}

	public function cache() {
		if($this->cache == null)
			$this->cache = $this->home() . "/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/" . $this->name() . "/";

		if (!file_exists($this->cache))
			mkdir($this->cache);

		return $this->cache;
	}

	public function storage() {
		if($this->storage == null)
			$this->storage = $this->home() . "/Library/Application Support/Alfred 2/Workflow Data/" . $this->name() . "/";

		if (!file_exists($this->storage))
			mkdir($this->storage);

		return $this->storage;
	}

	public function alfredPrefs() {
		if ($this->alfredPrefs == null)
			$this->alfredPrefs = $this->plist('com.runningwithcrayons.Alfred-Preferences', 'syncfolder') . '/Alfred.alfredpreferences';

		return $this->alfredPrefs();
	}

	public function notificationMethod() {
		if ($this->notificationMethod == null) {
			$defaultOutput = $this->plist($this->alfredPrefs() . '/notifications/prefs', 'defaultoutput');
			$this->notificationMethod = ($defaultoutput) ? 'growl' : 'nc';
		}

		return $this->notificationMethod;
	}

	// Both `defaults` and `options` are inspired by jdfwarrior's PHP workflow for Alfred.
	// Though I cited him at the beginning of this class, the plist method of setting
	// storage I pulled from his workflow.

	// Read an arbitrary plist setting.
	public function plist($plist, $setting, $value = '') {
		if ($value == '') {
			return exec("defaults read '$plist' '$setting'");
		}

		return exec("defaults write '$plist' '$setting' '$value'");
	}

	// Read the workflow .plist file.
	public function defaults($setting, $value = '') {
		return $this->plist($this->workflow() . "/info", $setting, $value);
	}

	// Read a custom workflow options .plist file.
	public function options($setting, $value = '') {
		// basically like defaults but for user settings

		$options = $this->storage() . "/settings";
		$optionsFile = $options . ".plist";

		if(!file_exists($optionsFile))
			touch($optionsFile);
	
		return $this->plist($options, $setting, $value);		
	}

	public function actionify($default_action, $cmd_action = '', $shift_action = '', $alt_action = '', $ctrl_action = '') {
		if($cmd_action == '')
			$cmd_action = $default_action;

		if($shift_action == '')
			$shift_action = $default_action;

		if($alt_action == '')
			$alt_action = $default_action;

		if($ctrl_action == '')
			$ctrl_action = $default_action;

		// Fast is_array comparison, might as well use it.
		// http://www.php.net/is_array#98156
		if((array) $default_action === $default_action)
			$default_action = implode(" ⦔ ", $default_action);

		if((array) $cmd_action === $cmd_action)
			$cmd_action = implode(" ⦔ ", $cmd_action);

		if((array) $shift_action === $shift_action)
			$shift_action = implode(" ⦔ ", $shift_action);

		if((array) $alt_action === $alt_action)
			$alt_action = implode(" ⦔ ", $alt_action);

		if((array) $ctrl_action === $ctrl_action)
			$ctrl_action = implode(" ⦔ ", $ctrl_action);

		return "$default_action ⧙ $cmd_action ⧙ $shift_action ⧙ $alt_action ⧙ $ctrl_action";
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
			
			// TODO rewrite escapequery function	
			print "\r\n\r\n";
			print "	<item uid='" . $this->escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . $this->escapeQuery($result['valid']) . "' autocomplete='" . $this->escapeQuery($result['autocomplete']) . "'>\r\n";
			print "		<title>" . $this->escapeQuery($result['title']) . "</title>\r\n";
			print "		<subtitle>" . $this->escapeQuery($result['subtitle']) . "</subtitle>\r\n";
			print "		<icon>" . $this->escapeQuery($result['icon']) . "</icon>\r\n";
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

		$this->alfredify($results);
		exit();
	}

	public function notify($title, $subtitle = '') {
		// TODO add Growl support if requested... don't really want to.
		// http://growl.info/documentation/applescript-support.php

		exec("open include/Notifier.app --args '" . $title . "✂" . $subtitle . "✂✂'");
	}

	public function normalize($text) {
		return exec('./include/normalize "' . $text . '"');
	}

	public function debug($text) {
		$results[0]['title'] = $text;

		$this->alfredify($results);
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

		 if($info['http_code'] != '200') {
		 	if ($info['http_code'] == '0')
		 		throw new AlfredableException("Could not access Spotify API. Try searching again");

	 		throw new AlfredableException("fetch() failed; error code: " . $info['http_code']);
		 }
		 	

		 return ($info['http_code'] == '200') ? $page : null;
	}

	public function escapeQuery($text) {
		$text = str_replace("'", "’", $text); // TODO replace with escaping.
		$text = str_replace("&", "&amp;", $text);
		
		return $text;
	}

}

// Stack return!
// http://stackoverflow.com/questions/1809404/get-exception-context-in-php
class AlfredableException extends Exception implements IStatefullException  {
    protected $throwState;

    // List of things that should never be written to debug files.
    private $forbidden = array("_POST", "_SERVER", "_GET", "_FILES", "_COOKIE");

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