<?php
namespace Spotifious\Menus;

use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\OhAlfred;
use Spotifious\Menus\Menu;

class DetailArtist {
	protected $alfred;

	protected $currentURI;
	protected $query;
	protected $originalQuery;
	protected $search;

	protected $name;
	protected $type;
	protected $albums;

	public function __construct($options, $alfred, $api) {
		$this->alfred = $alfred;
		$locale = $this->alfred->options('country');

		$this->currentURI = $options['currentURI'];
		$this->query = $options['query'];
		$this->originalQuery = $options['originalQuery'];
		$this->search = $options['search'];

		$artistJson = $api->getArtist($options['id']);


		$this->name = $artistJson->name;
		$this->type = $artistJson->type;

		if($locale != 'not-given') {
			$albumsJson = $api->getArtistAlbums($options['id'], [$locale]);
		}
		else {
			$albumsJson = $api->getArtistAlbums($options['id'], []);
		}

		$this->albums = array();
		foreach ($albumsJson->items as $key => $value) {
			$this->albums[] = array(
				'uri' => $value->uri,
				'name' => $value->name,
				'type' => $value->album_type
			);
		}

		// TODO search for more!
	}

	public function output() {
		$results = array();

		foreach ($this->albums as $key => $current) {
			$currentResult = array(
				'title' => $current['name'],
				'subtitle' => "Browse this {$current['type']}",
				'valid' => false,
				'autocomplete' => "{$this->currentURI} ⟩ {$current['uri']} ⟩ {$this->query} ⟩{$this->search}⟩",
				'copy' => $this->currentURI,
				'icon' => array('path' => "include/images/album.png")
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
}