<?php
namespace Spotifious\Menus;

use OhAlfred\Exceptions\StatefulException;
use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\OhAlfred;

class DetailPlaylist {
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

		$explodedURI = explode(":", $this->currentURI);
		$user = $explodedURI[count($explodedURI) - 3];

		if(!$api) {
			throw new StatefulException("You should have an API at this point");
		} else {
			$json = $api->getUserPlaylist($user, $options['id']);
		}

		$this->name = $json->name;
		$this->type = $json->type;

		$this->tracks = array();
		foreach ($json->tracks->items as $key => $value) {
			$this->tracks[] = array(
				'uri' => $value->track->uri,
				'name' => $value->track->name,
				'type' => $value->track->type,
				'number' => $key + 1,
				'duration' => $value->track->duration_ms,
				'explicit' => ($value->track->explicit == 'true')
			);
		}

		$this->overflow = false;
		if($json->tracks->total > $json->tracks->limit) {
			$this->overflow = true;
			$this->overflow_count = $json->tracks->total - $json->tracks->limit;
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

		if($this->overflow) {
			$overflow = array(
				'title' => 'This is a large playlist',
				'subtitle' => ($this->overflow_count === 1) 
					? "1 song could not be displayed; you can view it in Spotify."
					: "$this->overflow_count songs could not be displayed; you can view them in Spotify.",
				'arg' => "spotify⟩activate (open location \"{$this->currentURI}\")",
				'autocomplete' => $this->originalQuery,
				'copy' => $this->currentURI,
				'icon' => array('path' => "include/images/info.png")
			);
		}

		$scope['title'] = $this->name;
		$scope['subtitle'] = "Browse this {$this->type} in Spotify";
		$scope['arg'] = "spotify⟩activate (open location \"{$this->currentURI}\")";
		$scope['autocomplete'] = $this->originalQuery;
		$scope['copy'] = $this->currentURI;
		$scope['icon'] = array('path' => "include/images/{$this->type}.png");

		if ($this->search == null) {
			if($this->overflow) {
				array_unshift($results, $overflow);
			}
			array_unshift($results, $scope);
		} else {
			array_push($results, $scope);
			if($this->overflow) {
				array_push($results, $overflow);
			}
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