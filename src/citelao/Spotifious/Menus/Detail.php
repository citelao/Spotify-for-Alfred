<?php
namespace Spotifious\Menus;

use OhAlfred\Exceptions\StatefulException;
use Spotifious\Menus\Menu;
use Spotifious\Menus\DetailArtist;
use Spotifious\Menus\DetailAlbum;
use Spotifious\Menus\DetailPlaylist;

class Detail {
	protected $submenu;

	public function __construct($options, $alfred, $api) {
		$this->search = $options['search'];

		$this->currentURI = $options['URIs'][$options['depth'] - 1];
		$explodedURI = explode(":", $this->currentURI);
		$this->type = $explodedURI[count($explodedURI) - 2];

		$constructedOptions = array(
			'currentURI' => $this->currentURI,
			'search' => $options['search'],
			'id' => $explodedURI[count($explodedURI) - 1],
			'originalQuery' => $options['query'],
			'query' => implode(" ⟩", $options['args'])
		);

		if($this->type == "artist") {
			$this->submenu = new DetailArtist($constructedOptions, $alfred, $api);
		} else if($this->type == "album") {
			$this->submenu = new DetailAlbum($constructedOptions, $alfred, $api);
		} else if($this->type == "playlist") {
			$this->submenu = new DetailPlaylist($constructedOptions, $alfred, $api);
		} else {
			throw new StatefulException("Unknown detail type: '${$this->type}'");
			
		}
	}

	public function output() {
		return $this->submenu->output();
	}

	// protected function contains($stack, $needle) {
	// 	return (strpos($stack, $needle) !== false);
	// }
}