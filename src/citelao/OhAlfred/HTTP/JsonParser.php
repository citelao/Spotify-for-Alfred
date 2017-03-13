<?php
namespace OhAlfred\HTTP;

use OhAlfred\HTTP\Fetcher;
use OhAlfred\Exceptions\StatefulException;

class JsonParser {
	static public function parse($json) {
		$json = json_decode($json);

		if($json == null)
			throw new StatefulException("JSON error: " . json_last_error(), array('json' => $json));

		return $json;
	}
}