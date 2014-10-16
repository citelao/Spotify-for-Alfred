<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use Spotifious\Menus\Helper;
use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\OhAlfred;

class Detail implements Menu {
	protected $alfred;

	protected $title;
	protected $type;

	protected $raw;
	protected $rawType;

	protected $currentURI;
	protected $query;
	protected $originalQuery;
	protected $search;

	protected $locale;
	protected $availability;

	public function __construct($options) {
		$this->search = $options['search'];

		$this->currentURI = $options['URIs'][$options['depth'] - 1];
		$explodedURI = explode(":", $this->currentURI);
		$this->type = $explodedURI[1];
		$this->rawType = ($this->type == "artist") ? "album" : "track";

		$fetcher = new JsonFetcher("http://ws.spotify.com/lookup/1/.json?uri={$this->currentURI}&extras={$this->rawType}detail");
		$json = $fetcher->run();

		$this->title = $json->{$this->type}->name;
		$this->raw = array();

		$this->alfred = new OhAlfred();
		$this->locale = $this->alfred->options('country');

		$this->originalQuery = $options['query'];

		if($this->rawType == "album") {
			$albums = array();
			$this->query = implode(" ⟩", $options['args']);

			// Invalidate this if anything is unavailable.
			$this->availability = true;

			foreach ($json->artist->albums as $key => $value) {
				$value = $value->album;

				// Prevent duplicates.
				if(in_array($value->name, $albums))
					continue;

				// Determine region availability
				if(isset($value->availability)) {
					$regions = $value->availability->territories;
				} else {
					$regions = "";
				}

				if(mb_strlen($regions) > 0 && !$this->contains($regions, $this->locale)) {
					$this->availability = false;
					continue;
				}

				$currentResult['title'] = $value->name;
				$currentResult['type'] = 'album';
				$currentResult['href'] = $value->href;

				if($this->search != '' && !mb_stristr($currentResult['title'], $this->search))
					continue;

				$this->raw[] = $currentResult;
				$albums[] = $value->name;
			}
		} else {
			$this->availability = (!isset($json->album->availability) || 
				mb_strlen($json->album->availability->territories) == 0 ||
				!$this->contains($json->album->availability->territories, $this->locale));

			foreach ($json->album->tracks as $key => $value) {
				$currentResult['title'] = $value->name;
				$currentResult['type'] = 'track';
				$currentResult['href'] = $value->href;

				$currentResult['number'] = $value->{'track-number'};
				$currentResult['popularity'] = $value->popularity;
				$currentResult['length'] = $value->length;

				if($this->search != '' && !mb_stristr($currentResult['title'], $this->search))
					continue;

				$this->raw[] = $currentResult;
			}
		}
	}

	public function output() {
		$results = array();

		if(!empty($this->raw)) {
			foreach ($this->raw as $key => $current) {
				$currentResult = array();
				if ($current['type'] == 'track') {
					$currentResult['title'] = "{$current['number']}. {$current['title']}";
					$currentResult['subtitle'] = Helper::floatToBars($current['popularity'], 12);
					$currentResult['arg'] = "spotify⟩play track \"{$current['href']}\" in context \"{$this->currentURI}\""; 
					$currentResult['copy'] = $current['href'];
					$currentResult['valid'] = "yes";
					$currentResult['icon'] = "include/images/track.png";
				} else {
					$currentResult['title'] = $current['title'];
					$currentResult['subtitle'] = "Browse this {$current['type']}";
					$currentResult['valid'] = "no";
					$currentResult['autocomplete'] = "{$this->currentURI} ⟩ {$current['href']} ⟩ {$this->query} ⟩{$this->search}⟩";
					$currentResult['copy'] = $current['href'];
					$currentResult['icon'] = "include/images/album.png";
				}

				$results[] = $currentResult;
			}
		}

		$scope['title'] = $this->title;
		$scope['subtitle'] = "Browse this {$this->type} in Spotify";
		$scope['arg'] = "spotify⟩activate (open location \"{$this->currentURI}\")";
		$scope['autocomplete'] = $this->originalQuery;
		$scope['copy'] = $this->currentURI;
		$scope['icon'] = "include/images/{$this->type}.png";

		$available = array();
		if(!$this->availability) {
			$available['title'] = "Some music unavailable.";
			$available['subtitle'] = "Some results were hidden due to your locality (“{$this->locale}”).";
			$available['valid'] = "no";
			$available['autocomplete'] = $this->originalQuery;
			$available['icon'] = "include/images/error.png";
		}

		if ($this->search == null) {
			if(sizeof($available) > 0) {
				array_unshift($results, $available);
			}
			array_unshift($results, $scope);
		} else {
			array_push($results, $scope);
			if(sizeof($available) > 0) {
				array_push($results, $available);
			}
		}

		return $results;
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}
}