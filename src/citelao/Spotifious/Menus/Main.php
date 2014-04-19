<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\Applescript\ApplicationApplescript;

class Main implements Menu {

	protected $currentTrack;
	protected $currentAlbum;
	protected $currentArtist;
	protected $currentURL;
	protected $currentStatus;

	public function __construct($query) {
		$current = $this->now();

		$this->currentTrack             = $current[0];
		$this->currentAlbum             = $current[1];
		$this->currentArtist            = $current[2];
		$this->currentURL               = $current[3];
		$this->currentStatus            = ($current[4] == 'playing') ? "include/images/paused.png" : "include/images/playing.png";
	}

	public function output() {
		$results[0]['title']        = "$this->currentTrack";
		$results[0]['subtitle']     = "$this->currentAlbum by $this->currentArtist";
		$results[0]['arg']          = "playpause";
		$results[0]['icon']         = $this->currentStatus;
		
		$results[1]['title']        = "$this->currentAlbum";
		$results[1]['subtitle']     = "More from this album...";
		$results[1]['autocomplete'] = "$this->currentAlbum"; // TODO change to albumdetail
		$results[1]['valid']        = "no";
		$results[1]['icon']         = 'include/images/album.png';
		
		$results[2]['title']        = "$this->currentArtist";
		$results[2]['subtitle']     = "More by this artist...";
		$results[2]['autocomplete'] = $this->currentArtist; // TODO change to artistdetail
		$results[2]['valid']        = "no";
		$results[2]['icon']         = 'include/images/artist.png';
		
		$results[3]['title']        = "Search for music...";
		$results[3]['subtitle']     = "Begin typing to search";
		$results[3]['valid']        = "no";
		$results[3]['icon']         = "include/images/search.png";

		// Overrides for no track
		if($this->currentTrack == "No track playing") {
			$results[0]['subtitle']     = "";

			$results[1]['subtitle']     = "";
			$results[1]['autocomplete'] = "";

			$results[2]['subtitle']     = "";
			$results[2]['autocomplete'] = "";
		}

		return $results;
	}

	protected function now() {
		$spotQuery = new ApplicationApplescript('Spotify', 'return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state');

		$data = $spotQuery->run();

		$array = explode("✂", $data);

		if($array[0] == "") {
			$array[0] = "No track playing";
			$array[1] = "No album";
			$array[2] = "No artist";
			$array[3] = "";
			$array[4] = "paused";
		}

		return $array;
	}
}