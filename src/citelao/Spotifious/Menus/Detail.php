<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\HTTP\JsonFetcher;

class Detail implements Menu {
	protected $title;
	protected $type;

	protected $raw;
	protected $rawType;

	protected $currentURI;
	// TODO availability


	public function __construct($options) {
		$this->currentURI    = $options['URIs'][$depth - 1];
		$explodedURI   = explode(":", $this->currentURI);
		$this->type    = $explodedURI[1];
		$this->rawType = ($this->type == "artist") ? "album" : "track";

		$fetcher = new JsonFetcher("http://ws.spotify.com/lookup/1/.json?uri=$currentURI&extras={$this->rawType}detail");
		$json = $fetcher->run();

		$this->title = $json->$type->name;
		$this->raw = array();

		if($this->rawType == "album") {
			$albums = array();
			$query = implode(" ⟩", $options['args']);

			foreach ($json->artist->albums as $key => $value) {
				$value = $value->album;

				if(in_array($value->name, $albums))
					continue;

				$currentResult['title'] = $value->name;
				$currentResult['type'] = 'album';
				$currentResult['href'] = $value->href;

				if($options['search'] != null && !mb_stristr($currentResult['title'], $options['search']))
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

				if($options['search'] != null && !mb_stristr($currentResult['title'], $options['search']))
					continue;

				$this->raw[] = $currentResult;
			}
		}
	}

	public function output() {
		$results = array();

		if(!empty($this->raw)) {
			foreach ($this->raw as $key => $current) {
				
			}
		}

		return $results;
	}
}