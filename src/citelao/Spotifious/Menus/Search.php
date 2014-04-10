<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\Exceptions\StatefulException;

class Search implements Menu {
	public function __construct($query) {
		// Build the search results
		// for each query type
		foreach (array('artist', 'album', 'track') as $type) {
			/* Fetch and parse the search results. */
			$urlQuery = str_replace("%3A", ":", urlencode($query));
			$url = "http://ws.spotify.com/search/1/$type.json?q=$urlQuery";
			
			$fetcher = new JsonFetcher($url);
			$json = $fetcher->run();

			// Create the search results array
			foreach ($json->{$type . "s"} as $key => $value) {
				
			}
		}
	}
	
	public function output() {

	}
}