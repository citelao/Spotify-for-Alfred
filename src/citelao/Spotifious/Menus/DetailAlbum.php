<?php
namespace Spotifious\Menus;

use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\OhAlfred;
use Spotifious\Menus\Menu;

class DetailAlbum implements Menu {
	protected $alfred;

	protected $currentURI;
	protected $query;
	protected $originalQuery;
	protected $search;

	protected $name;
	protected $type;
	protected $tracks;

	public function __construct($options) {
		$this->alfred = new OhAlfred();
		$locale = $this->alfred->options('country');

		$this->currentURI = $options['currentURI'];
		$this->query = $options['query'];
		$this->originalQuery = $options['originalQuery'];
		$this->search = $options['search'];

		$artistFetcher = new JsonFetcher("https://api.spotify.com/v1/albums/{$options['id']}");
		$artistJson = $artistFetcher->run();

		$this->name = $artistJson->name;
		$this->type = $artistJson->type;

		$url = "https://api.spotify.com/v1/albums/{$options['id']}/tracks";
		if($locale != 'not-given') {
			$url .= "?market=$locale";
		}
		$tracksFetcher = new JsonFetcher($url);
		$tracksJson = $tracksFetcher->run();

		$this->tracks = array();
		foreach ($tracksJson->items as $key => $value) {
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
				'valid' => 'yes',
				'arg' => "spotify⟩play track \"{$current['uri']}\" in context \"{$this->currentURI}\"",
				'copy' => $current['uri'],
				'icon' => "include/images/track.png"
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
		$scope['icon'] = "include/images/{$this->type}.png";

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

		return $minutes . ":" . $seconds;
	}
}