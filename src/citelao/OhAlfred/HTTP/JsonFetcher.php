<?php
namespace OhAlfred\HTTP;

use OhAlfred\HTTP\Fetcher;
use OhAlfred\HTTP\JsonParser;
use OhAlfred\Exceptions\StatefulException;

class JsonFetcher {
	protected $fetcher;
	protected $url;

	public function __construct($url) {
		$this->url = $url;
		$this->fetcher = new Fetcher($url);
	}

	public function run() {
		$json = $this->fetcher->run();

		if(empty($json))
				throw new StatefulException("No JSON returned from " . $this->url);

		return JsonParser::parse($json);
	}
}