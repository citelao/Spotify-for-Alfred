<?php
namespace OhAlfred;

use OhAlfred\Exceptions\StatefulException;

/* see: https://github.com/jdfwarrior/Workflows/blob/master/workflows.php */
class OhAlfred {
	protected $results;

	protected $name;
	protected $version;
	protected $home;
	protected $workflow;
	protected $cache;
	protected $storage;

	protected $plists = array();

	// Set the exception handlers
	public function __construct($catch_errors = true) {
		if($catch_errors) {
			set_error_handler(array($this, 'errorify'), E_ALL);
		}
	}

	// Get the current workflow name.
	public function name()
	{
		if($this->name == null)
			$this->name = $this->defaults('bundleid');

		return $this->name; 
	}

	// Get the current Alfred version
	public function version()
	{
		if($this->version == null) {
			if(!getenv("alfred_version")) {
				$this->version = "2";
			} else {
				$this->version = getenv("alfred_version");
			}
		}

		return $this->version;
	}

	// Helper for Alfred 3
	public function isAlfred3()
	{
		return $this->version()[0] === "3";
	}

	// Get the user's home directory.
	public function home()
	{
		if($this->home == null)
			$this->home = exec('printf "$HOME"');

		return $this->home;
	}

	// Get the workflow directory.
	public function workflow()
	{
		if($this->workflow == null)
			$this->workflow = dirname(dirname(dirname(__DIR__))) . '/'; // Because I keep OhAlfred in the src/citelao/OhAlfred directory.
																  // TODO make portable

		return $this->workflow;
	}

	// Get the cache directory
	public function cache() {
		if($this->cache == null) {
			if(getenv('alfred_workflow_cache')) {
				$this->cache = getenv('alfred_workflow_cache') . "/";
			} else {
				$this->cache = $this->home() . "/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/" . $this->name() . "/";
			}
		}

		if (!file_exists($this->cache)) {
			mkdir($this->cache);
		}

		return $this->cache;
	}

	// Get the storage directory
	public function storage() {
		if($this->storage == null) {
			if(getenv('alfred_workflow_data')) {
				$this->storage = getenv('alfred_workflow_data') . "/";
			} else {
				$this->storage = $this->home() . "/Library/Application Support/Alfred 2/Workflow Data/" . $this->name() . "/";
			}
		}

		if (!file_exists($this->storage)) {
			mkdir($this->storage);
		}

		return $this->storage;
	}

	/**
	 * Both `defaults` and `options` are inspired by jdfwarrior's PHP workflow for Alfred.
	 * Though I cited him at the beginning of this class, the plist method of setting
	 * storage I pulled from his workflow.
	 **/

	// Read an arbitrary plist setting.
	public function plist($plist, $setting, $value = -1) {
		if ($value === -1) {
			if(!array_key_exists($plist, $this->plists)) {
				$json = exec("plutil -convert json -o - '{$plist}.plist'");
				$decoded = json_decode($json);

				if(json_last_error() !== JSON_ERROR_NONE) {
					return false;
				}

				$this->plists[$plist] = $decoded;	
			}

			if(!property_exists($this->plists[$plist], $setting)) {
				return false;
			}

			return $this->plists[$plist]->{$setting};
		}

		// Uncache plist on write
		unset($this->plists[$plist]);

		if(!is_string($value)) {
			$value = '"' . addslashes(json_encode($value)) . '"';
		}

		$escaped_plist = escapeshellarg($plist);
		$escaped_setting = escapeshellarg($setting);
		$escaped_value = escapeshellarg($value);
		// print("defaults write $escaped_plist $escaped_setting $escaped_value");
		return exec("defaults write $escaped_plist $escaped_setting $escaped_value");
	}

	// Read the workflow .plist file.
	public function defaults($setting, $value = -1) {
		return $this->plist($this->workflow() . "info", $setting, $value);
	}

	// Read a custom workflow options .plist file.
	public function options($setting, $value = -1) {
		$options = $this->storage() . "settings";
		$optionsFile = $options . ".plist";

		if(!file_exists($optionsFile))
			touch($optionsFile);

		return $this->plist($options, $setting, $value);		
	}

	// Create and return the XML output as a string.
	public function alfredify($r = null) {
		if($r == null)
			$r = $this->results;

		$output = "";
		if($this->isAlfred3()) {
			$output = $this->jsonify($r);
		} else {
			$output = $this->xmlify($r);
		}

		print $output;
	}

	protected function jsonify($results) {
		$output = array('items' => []);

		foreach ($results as $result) {
			$item = $result;

			// Meantime checks
			if(isset($item['icon']) && is_string($item['icon'])) {
				$icon = array('path' => $item['icon']);
				$item['icon'] = $icon;
				throw new StatefulException("Expected array for icon, not string");
			}

			if(isset($item['valid']) && is_string($item['valid'])) {
				$item['valid'] = $item['valid'] === 'yes';
				throw new StatefulException("Expected boolean for validity, not string");
			}

			if(isset($item['copy']) && is_string($item['copy'])) {
				$item['text'] = array('copy' => $item['copy']);
				// TODO purge the copy tag!
			}

			$output['items'][] = $item;
		}

		return json_encode($output);
	}

	protected function xmlify($results) {
		$output = "<?xml version='1.0'?>\r\n<items>";

		foreach($results as $result) {
			if(!isset($result['arg']))
				$result['arg'] = 'null';

			if(!isset($result['title']))
				$result['title'] = 'null';

			if(!isset($result['icon']))
				$result['icon'] = 'icon.png';

			if(!is_string($result['icon'])) {
				$icon = $result['icon']['path'];
				$result['icon'] = $icon;
			}

			if(!isset($result['valid']))
				$result['valid'] = 'yes';

			if(!is_string($result['valid'])) {
				$result['valid'] = ($result['valid']) ? 'yes' : 'no';
			}

			if(!isset($result['uid']))
				$result['uid'] = time() . "-" . $result['title'];

			if(!isset($result['autocomplete']))
				$result['autocomplete'] = '';

			if(!isset($result['subtitle']))
				$result['subtitle'] = '';

			$output .= "\r\n\r\n";
			$output .= "	<item uid='" . $this->escapeQuery($result['uid']) . "' valid='" . $this->escapeQuery($result['valid']) . "' autocomplete='" . $this->escapeQuery($result['autocomplete']) . "'>\r\n";
			$output .= "		<arg>" . $result['arg'] . "</arg>\r\n";
			$output .= "		<title>" . $this->escapeQuery($result['title']) . "</title>\r\n";
			$output .= "		<subtitle>" . $this->escapeQuery($result['subtitle']) . "</subtitle>\r\n";
			$output .= "		<icon>" . $this->escapeQuery($result['icon']) . "</icon>\r\n";

			if(isset($result['copy'])) {
				$output .= "		<text type='copy'>" . $this->escapeQuery($result['copy']) . "</text>\r\n";
			}

			$output .= "	</item>\r\n";
		}

		$output .= "</items>";
		return $output;
	}

	// Replace some symbols that confuse Alfred.
	public function escapeQuery($text) {
		$text = str_replace("&", "&amp;", $text);
		$text = str_replace("'", "&#39;", $text);

		return $text;
	}

	// Change an exception into Alfred-displayable XML.
	public function exceptionify($error, $should_die = true) {
		if(method_exists($error, 'getState')) {
			$state = array_merge($error->getState(), $error->getTrace());
		} else {
			$state = $error->getTrace();
		}

		$this->errorify(get_class($error), $error->getMessage(), $error->getFile(), $error->getLine(), $state, $should_die);
	}

	// Change an error into Alfred-displayable XML
	public function errorify($number, $message, $file, $line, $context, $should_die = true) {
		$titles = array('Aw, jeez!', 'Dagnabit!', 'Crud!', 'Whoops!', 'Oh, snap!', 'Aw, fiddlesticks!', 'Goram it!');

		$fdir = $this->loggifyError($number, $message, $file, $line, $context);

		$results = array(
			array(
				'title' => $titles[array_rand($titles)],
				'subtitle' => "Something went haywire. You can continue using Spotifious.",
				'valid' => false,
				'icon' => array('path' => 'include/images/error.png')
			),

			array(
				'title' => $message,
				'subtitle' => "Line " . $line . ", " . $file,
				'valid' => false,
				'icon' => array('path' => 'include/images/info.png')
			),

			array(
				'title' => "View log",
				'subtitle' => "Open new Finder window with .log file.",
				'icon' => array('path' => 'include/images/folder.png'),
				'arg' => $fdir
			)
		);

		$this->alfredify($results);

		if($should_die) {
			die();
		}
	}

	// Write a log file of an error.
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

	public function notify($message, $title = '', $subtitle = '', $appIcon = '', $contentImage = '', $open = '') {
		if($this->options('track_notifications') != 'true') {
			return;
		}

		$command = "include/terminal-notifier.app/Contents/MacOS/terminal-notifier ";

		$command .= "-message " . $this->escapeNotify($message) . " ";
		$command .= "-group ohAlfredNotifications ";

		if($title)
			$command .= "-title " . $this->escapeNotify($title) . " ";

		if($subtitle)
			$command .= "-subtitle " . $this->escapeNotify($subtitle) . " ";

		if($appIcon)
			$command .= "-appIcon " . $this->escapeNotify($appIcon) . " ";

		if($contentImage)
			$command .= "-contentImage " . $this->escapeNotify($contentImage) . " ";

		if($open)
			$command .= "-open " . $this->escapeNotify($open) . " ";

		exec($command);
	}

	protected function escapeNotify($string) {
		if(!preg_match('/[a-zA-Z0-9]/', $string[0])) {
			$string = '\\' . $string;
		}

		return escapeshellarg($string);
	}
}
