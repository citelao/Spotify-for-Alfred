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

	public function __construct($query, $alfred=null, $api=null) {
		$current = $this->now();

		$this->currentTrack             = $current[0];
		$this->currentAlbum             = $current[1];
		$this->currentArtist            = $current[2];
		$this->currentURL               = $current[3];
		$this->currentStatus            = ($current[4] == 'playing') ? "include/images/paused.png" : "include/images/playing.png";
	}

	public function output() {
		$results[0] = array(
			'title' => "$this->currentTrack",
			'subtitle' => "$this->currentAlbum by $this->currentArtist",
			'arg' => "playpause⟩",
			'copy' => $this->currentURL,
			'icon' => array(
				'path' => $this->currentStatus
			),
			// 'mods' => array(
			// 	'alt' => array(
			// 		'subtitle' => "Browse to artist ($this->currentArtist)...",
			// 		'arg' => '{"action": "spotifious", "options": { "command": "artist:' . $this->currentArtist . '" }}'
			// 	),
			// 	'ctrl' => array(
			// 		'subtitle' => "Browse to album ($this->currentAlbum)..."
			// 	),
			// 	'cmd' => array(
			// 		'subtitle' => 'Queue this song'
			// 	),
			// )
		);
		
		$results[1] = array(
			'title' => "$this->currentAlbum",
			'subtitle' => "More from this album...",
			'autocomplete' => "artist:$this->currentArtist album:$this->currentAlbum", // TODO change to albumdetail
			'copy' => "$this->currentAlbum", // TODO change to albumdetail
			'valid' => false,
			'icon'  => array(
				'path' => 'include/images/album.png'
			),
			// 'mods' => array(
			// 	'alt' => array(
			// 		'subtitle' => "Browse to artist ($this->currentArtist)..."
			// 	),
			// 	'cmd' => array(
			// 		'subtitle' => 'Queue this album'
			// 	),
			// )
		);
		
		$results[2] = array(
			'title' => "$this->currentArtist",
			'subtitle' => "More by this artist...",
			'autocomplete' => "artist:$this->currentArtist", // TODO change to artistdetail
			'copy' => $this->currentArtist, // TODO change to artistdetail
			'valid' => false,
			'icon'  => array('path' => 'include/images/artist.png')
		);
		
		$results[3] = array(
			'title' => "Search for music...",
			'subtitle' => "Begin typing to search",
			'valid' => false,
			'icon' => array('path' => "include/images/search.png"),
			'mods' => array(
				'ctrl' => array(
					'valid' => true,
					'subtitle' => 'Open controls...',
					'arg' => '{"action": "spotifious", "options": { "command": "c" }}',
					'variables' => array(
						'keepalive' => 'true'
					)
				),
				'cmd' => array(
					'valid' => true,
					'subtitle' => 'Open settings...',
					'arg' => '{"action": "spotifious", "options": { "command": "s" }}',
					'icon' => array('path' => "include/images/configuration.png"),
					'variables' => array(
						'keepalive' => 'true'
					)
				),
				'shift' => array(
					'valid' => true,
					'subtitle' => 'Activate Spotify',
					'arg' => '{"action": "applescript", "options": { "application": "Spotify", "command": "activate" }}'
				)
			)
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
