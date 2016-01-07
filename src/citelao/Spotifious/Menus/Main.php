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
		$results[] = array(
			'title' => $this->currentTrack,
			'subtitle' => array(
				'default' => "$this->currentAlbum by $this->currentArtist",
				'cmd' => "Queue this track" 
				),
			'arg' => 'playpause⟩return',
			'copy' => $this->currentURL,
			'icon' => $this->currentStatus
		);

		$results[] = array(
			'title' => $this->currentAlbum,
			'subtitle' => array(
				'default' => "More from this album...",
				'cmd' => "Queue this album"
				),
			// 'arg' => 'playpause⟩',
			// temp, soon we can get autocomplete!
			'valid' => 'no',
			'autocomplete' => $this->currentAlbum,

			'copy' => $this->currentAlbum,
			'icon' => 'include/images/album.png'
		);

		$results[] = array(
			'title' => $this->currentArtist,
			'subtitle' => array(
				'default' => "More by this artist...",
				'cmd' => "Queue this artist"
				),
			// 'arg' => 'playpause⟩',
			// temp, soon we can get autocomplete!
			'valid' => 'no',
			'autocomplete' => $this->currentArtist,

			'copy' => $this->currentArtist,
			'icon' => 'include/images/artist.png'
		);

		$results[] = array(
			'title' => "Search for music...",
			'subtitle' => array(
				'default' => "Begin typing to search"
				),
			'valid' => 'no',
			'icon' => 'include/images/search.png'
		);

		// Overrides for no track
		if($this->currentTrack == "No track playing") {
			$results[0]['subtitle']     = "";

			$results[1]['subtitle']     = "";
			$results[1]['autocomplete'] = "";
			$results[1]['copy'] 		= "";

			$results[2]['subtitle']     = "";
			$results[2]['autocomplete'] = "";
			$results[2]['copy'] 		= "";
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