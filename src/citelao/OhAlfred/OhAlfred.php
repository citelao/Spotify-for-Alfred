<?php
namespace OhAlfred;

/* see: https://github.com/jdfwarrior/Workflows/blob/master/workflows.php */
class OhAlfred {
	protected $results;

	protected $name;
	protected $home;
	protected $workflow;
	protected $cache;
	protected $storage;

	public function __construct() {
		set_exception_handler(array($this, 'exceptionify'));
		set_error_handler(array($this, 'errorify'));
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
			$this->workflow = dirname(dirname(dirname(__DIR__))); // Because I keep OhAlfred in the src/citelao/OhAlfred directory.
																  // TODO make portable

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

	/**
	 * Both `defaults` and `options` are inspired by jdfwarrior's PHP workflow for Alfred.
	 * Though I cited him at the beginning of this class, the plist method of setting
	 * storage I pulled from his workflow.
	 **/

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
		$options = $this->storage() . "/settings";
		$optionsFile = $options . ".plist";

		if(!file_exists($optionsFile))
			touch($optionsFile);

		return $this->plist($options, $setting, $value);		
	}

	public function alfredify($r = null) {
		if($r == null)
			$r = $this->results;

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
			print "	<item uid='" . $this->escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . $this->escapeQuery($result['valid']) . "' autocomplete='" . $this->escapeQuery($result['autocomplete']) . "'>\r\n";
			print "		<title>" . $this->escapeQuery($result['title']) . "</title>\r\n";
			print "		<subtitle>" . $this->escapeQuery($result['subtitle']) . "</subtitle>\r\n";
			print "		<icon>" . $this->escapeQuery($result['icon']) . "</icon>\r\n";
			print "	</item>\r\n";
		}

		print "</items>";
	}

	public function escapeQuery($text) {
		$text = str_replace("&", "&amp;", $text);
		$text = str_replace("'", "&#39;", $text);

		return $text;
	}

	public function exceptionify($error) {
		if(method_exists($error, 'getState')) {
			$state = array_merge($error->getState(), $error->getTrace());
		} else {
			$state = $error->getTrace();
		}

		$this->errorify(get_class($error), $error->getMessage(), $error->getFile(), $error->getLine(), $state);
	}

	public function errorify($number, $message, $file, $line, $context) {
		$titles = array('Aw, jeez!', 'Dagnabit!', 'Crud!', 'Whoops!', 'Oh, snap!', 'Aw, fiddlesticks!', 'Goram it!');

		$fdir = $this->loggifyError($number, $message, $file, $line, $context);

		$results = array(
			array(
				'title' => $titles[array_rand($titles)],
				'subtitle' => "Something went haywire. You can continue using Spotifious.",
				'valid' => "no",
				'icon' => 'include/images/error.png'
			),

			array(
				'title' => $message,
				'subtitle' => "Line " . $line . ", " . $file,
				'valid' => "no",
				'icon' => 'include/images/info.png'
			),

			array(
				'title' => "View log",
				'subtitle' => "Open new Finder window with .log file.",
				'icon' => 'include/images/folder.png',
				'arg' => $fdir
			)
		);

		$this->alfredify($results);
		return true;
	}

	protected function loggifyError($number, $message, $file, $line, $context) {
		// Write contents of log file.
		$fcontents  = "# Error Log # \n";

		$fcontents .= "## Error Info ## \n";
		$fcontents .= $message . "\n";
		$fcontents .= "Line " . $line . ", " . $file . "\n\n";

		$fcontents .= "## Symbols ## \n";
		$fcontents .= print_r($context, true) . "\n";
		$fcontents .= "\n\n";

		// Delay storing of error 'till contents are fully generated.
		$errordir = $this->cache();
		$fname = date('Y-m-d h-m-s') . " Spotifious.log";
		$fdir = $errordir . $fname;

		$log = fopen($fdir, "w");
		fwrite($log, $fcontents);
		fclose($log);

		return $fdir;
	}
}