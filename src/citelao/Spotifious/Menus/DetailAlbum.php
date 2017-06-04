<?php
namespace Spotifious\Menus;

use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\OhAlfred;

class DetailAlbum {
	protected $alfred;

	protected $currentURI;
	protected $query;
	protected $originalQuery;
	protected $search;

	protected $name;
	protected $type;
	protected $tracks;

	public function __construct($options, $alfred, $api) {
		$this->alfred = $alfred;
		$locale = $this->alfred->options('country');

		$this->currentURI = $options['currentURI'];
		$this->query = $options['query'];
		$this->originalQuery = $options['originalQuery'];
		$this->search = $options['search'];

		if(!$api) {
			$artistFetcher = new JsonFetcher("https://api.spotify.com/v1/albums/{$options['id']}");
			$json = $artistFetcher->run();
		} else {
			$json = $api->getAlbum($options['id']);
		}
		
		$this->name = $json->name;
		$this->type = $json->type;

		$this->tracks = array();
		foreach ($json->tracks->items as $key => $value) {
			$this->tracks[] = array(
				'uri' => $value->uri,
				'name' => $value->name,
				'type' => $value->type,
				'number' => $value->track_number,
				'duration' => $value->duration_ms,
				'explicit' => ($value->explicit == 'true')
			);
		}
	}

	public function output() {
		$results = array();

		foreach ($this->tracks as $key => $current) {
			$explicit = $current['explicit'] ? " (explicit)" : "";

			$currentResult = array(
				'title' => "{$current['number']}. {$current['name']}",
				'subtitle' => $this->prettifyTime($current['duration']) . $explicit,
				'valid' => true,
				'arg' => "spotify⟩play track \"{$current['uri']}\" in context \"{$this->currentURI}\"",
				'copy' => $current['uri'],
				'icon' => array('path' => "include/images/track.png")
			);

			if($this->search != '' && !mb_stristr($currentResult['title'], $this->search))
				continue;

			$results[] = $currentResult;
		}

		$scope['title'] = $this->name;
		$scope['subtitle'] = "Browse this {$this->type} in Spotify";
		$scope['arg'] = "spotify⟩activate (open location \"{$this->currentURI}\")";
		$scope['autocomplete'] = $this->originalQuery;
		$scope['copy'] = $this->currentURI;
		$scope['icon'] = array('path' => "include/images/{$this->type}.png");

		if ($this->search == null) {
			array_unshift($results, $scope);
		} else {
			array_push($results, $scope);
		}

		return $results;
	}

	protected function prettifyTime($time_ms) {
		$secondsForm = $time_ms / 1000;

		$seconds = $secondsForm % 60;
		$minutes = floor($secondsForm / 60);

		$seconds_string = ($seconds < 10)
			? "0" . $seconds
			: $seconds;

		return $minutes . ":" . $seconds_string;
	}
}