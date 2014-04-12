<?php
namespace OhAlfred\HTTP;

use OhAlfred\HTTP\Fetcher;
use OhAlfred\Exceptions\StatefulException;

class JsonFetcher {
	protected $fetcher;

	public function __construct($url) {
		$this->fetcher = new Fetcher($url);
	}

	public function run() {
		$json = $this->fetcher->run();

		if(empty($json))
				throw new StatefulException("No JSON returned from Spotify web search");

		$json = json_decode($json);

		if($json == null)
			throw new StatefulException("JSON error: " . json_last_error());

		return $json;
	}
}