<?php
namespace OhAlfred\HTTP;

use OhAlfred\Exceptions\StatefulException;

// Thanks Jeff Johns <http://phpfunk.me/> and Robin Enhorn <https://github.com/enhorn/>
class Fetcher {
	protected $url;

	public function __construct($url) {
		$this->url = $url;
	}

	public function run() {
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$page    = curl_exec($ch);
		$info    = curl_getinfo($ch);
		curl_close($ch);


		if($info['http_code'] != '200') {
		 	if ($info['http_code'] == '0') {
		 		throw new StatefulException("Could not access Spotify API. Try searching again");
		 	}

	 		throw new StatefulException("fetch() failed; error code: " . $info['http_code']);
		 }

		return $page;
	}
}