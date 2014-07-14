<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use Spotifious\Menus\Helper;
use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\Exceptions\StatefulException;
use OhAlfred\OhAlfred;

class Search implements Menu {
	protected $alfred;	
	protected $search;
	protected $query;

	public function __construct($query) {
		$this->query = $query;
		$this->alfred = new OhAlfred();

		$locale = $this->alfred->options('country');

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
				// Determine region availability
				if($type == "track") {
					if(isset($value->album->availability)) {
						$regions = $value->album->availability->territories;
					} else {
						$regions = "";
					}
				} else if($type == "album") {
					if(isset($value->availability)) {
						$regions = $value->availability->territories;
					} else {
						$regions = "";
					}
				} else {
					$regions = "";
				}

				if(mb_strlen($regions) > 0 && $this->contains($regions, $locale))
					continue;

				// Weight popularity
				$popularity = $value->popularity;

				if($type == 'artist')
					$popularity += .5;

				if($type == 'album')
					$popularity += .15;

				if ($type == 'track') {
					$currentRaw['album'] = $value->album->name;
					$currentRaw['artist'] = $value->artists[0]->name;	
				} elseif ($type == 'album') {
					$currentRaw['artist'] = $value->artists[0]->name;
				}

				$currentRaw['type'] = $type;
				$currentRaw['title'] = $value->name;
				$currentRaw['popularity'] = $popularity;
				$currentRaw['href'] = $value->href;

				$this->search[] = $currentRaw;
			}
		}

		if(!empty($this->search))
			usort($this->search, array($this, 'popularitySort'));
	}
	
	public function output() {
		if(!empty($this->search)) {
			foreach ($this->search as $key => $current) {
				$popularity = Helper::floatToBars($current['popularity']);

				if ($current['type'] == 'track') {
					$subtitle = "$popularity {$current['album']} by {$current['artist']}";
				} elseif ($current['type'] == 'album') {
					$subtitle = "$popularity Album by {$current['artist']}";
				} else {
					$subtitle = "$popularity " . ucfirst($current['type']);
				}

				if ($current['type'] == 'track') {
					$valid = 'yes';
					$arg = "spotify⟩play track \"{$current['href']}\"";
					$autocomplete = '';
				} else {
					$valid = 'no';
					$arg = '';
					$autocomplete = "{$current['href']} ⟩ {$this->query} ⟩";
				}

				$currentResult['title']    = $current['title'];
				$currentResult['subtitle'] = $subtitle;
				$currentResult['uid'] = "bs-spotify-{$this->query}-{$current['type']}-{$current['title']}";
				$currentResult['valid'] = $valid;
				$currentResult['arg'] = $arg;
				$currentResult['autocomplete'] = $autocomplete;
				$currentResult['icon'] = "include/images/{$current['type']}.png";

				$results[] = $currentResult;
			}
		}

		/* Give the option to continue searching in Spotify because even I know my limits. */
		$results[] = array(
			'title' => "Search for {$this->query}",
			'subtitle' => "Continue this search in Spotify…",
			'uid' => "bs-spotify-{$this->query}-more",
			'arg' => "spotify⟩activate (open location \"spotify:search:{$this->query}\")",
			'icon' => 'include/images/search.png'
		);

		return $results;
	}

	protected function popularitySort($a, $b) {
		if($a['popularity'] == $b['popularity'])
			return 0;

		return ($a['popularity'] < $b['popularity']) ? 1 : -1;
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}
}