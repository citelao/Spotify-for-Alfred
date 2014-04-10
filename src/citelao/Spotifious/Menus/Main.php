<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;

class Main implements Menu {

	protected $currentTrack;
	protected $currentAlbum;
	protected $currentArtist;
	protected $currentURL;
	protected $currentStatus;

	public function __construct($query) {
		$this->currentTrack             = $current[0];
		$this->currentAlbum             = $current[1];
		$this->currentArtist            = $current[2];
		$this->currentURL               = $current[3];
		$this->currentStatus            = ($current[4] == 'playing') ? "include/images/paused.png" : "include/images/playing.png";
	}

	public function output() {
		$results[0][title]        = "$this->currentTrack";
		$results[0][subtitle]     = "$this->currentAlbum by $this->currentArtist";
		$results[0][arg]          = "playpause";
		$results[0][icon]         = $this->currentStatus;
		
		$results[1][title]        = "$currentAlbum";
		$results[1][subtitle]     = "More from this album...";
		$results[1][autocomplete] = "$currentAlbum"; // TODO change to albumdetail
		$results[1][valid]        = "no";
		$results[1][icon]         = 'include/images/album.png';
		
		$results[2][title]        = "$currentArtist";
		$results[2][subtitle]     = "More by this artist...";
		$results[2][autocomplete] = $currentArtist; // TODO change to artistdetail
		$results[2][valid]        = "no";
		$results[2][icon]         = 'include/images/artist.png';
		
		$results[3][title]        = "Search for music...";
		$results[3][subtitle]     = "Begin typing to search";
		$results[3][valid]        = "no";
		$results[3][icon]         = "include/images/search.png";

		return $results;
	}

	protected function now() {

	}
}