<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use Spotifious\Menus\Helper;
use OhAlfred\HTTP\JsonFetcher;

class Detail implements Menu {
	protected $title;
	protected $type;

	protected $raw;
	protected $rawType;

	protected $currentURI;
	protected $query;
	protected $search;
	// TODO availability

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

		if($this->rawType == "album") {
			$albums = array();
			$this->query = implode(" ⟩", $options['args']);

			foreach ($json->artist->albums as $key => $value) {
				$value = $value->album;

				if(in_array($value->name, $albums))
					continue;

				$currentResult['title'] = $value->name;
				$currentResult['type'] = 'album';
				$currentResult['href'] = $value->href;

				if($this->search != '' && !mb_stristr($currentResult['title'], $this->search))
					continue;

				$this->raw[] = $currentResult;
				$albums[] = $value->name;
			}
		} else {
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
					$currentResult['arg'] = "play track \"{$current['href']}\" in context \"{$this->currentURI}\""; 
					$currentResult['valid'] = "yes";
					$currentResult['icon'] = "include/images/track.png";
				} else {
					$currentResult['title'] = $current['title'];
					$currentResult['subtitle'] = "Browse this {$current['type']}";
					$currentResult['valid'] = "no";
					$currentResult['autocomplete'] = "{$this->currentURI} ⟩ {$current['href']} ⟩ {$this->query} ⟩{$this->search}⟩";
					$currentResult['icon'] = "include/images/album.png";
				}

				$results[] = $currentResult;
			}
		}

		$scope['title'] = $this->title;
		$scope['subtitle'] = "Browse this {$this->type} in Spotify";
		$scope['arg'] = "activate (open location \"{$this->currentURI}\")";
		$scope['icon'] = "include/images/{$this->type}.png";

		if ($this->search == null) {
			array_unshift($results, $scope);
		} else {
			array_push($results, $scope);
		}

		return $results;
	}
}