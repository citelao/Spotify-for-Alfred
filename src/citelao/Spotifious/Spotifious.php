<?php
namespace Spotifious;

use Spotifious\Menus\Control;
use Spotifious\Menus\Main;
use Spotifious\Menus\Search;
use Spotifious\Menus\Setup;
use Spotifious\Menus\SetupCountryCode;
use Spotifious\Menus\Detail;
use OhAlfred\OhAlfred;
use OhAlfred\Applescript\ApplicationApplescript;

class Spotifious {
	protected $alfred;

	public function __construct() {
		$this->alfred = new OhAlfred();
	}

	public function run($query) {
		// Correct for old Spotifious queries
		$q = str_replace("►", "⟩", $query);

		// Display the setup menu if the app isn't setup.
		if($this->alfred->options('country') == '' ||
			$query == "s" || $query == "S" ||
			$this->contains($query, "Country Code ⟩")) {

			// If we are trying to configure country code
			if($this->contains($query, "Country Code ⟩")) {
				$menu = new SetupCountryCode($query);
				return $menu->output();
			}

			$menu = new Setup($query);
			return $menu->output();
		}

		if (mb_strlen($query) <= 3) {
			if(mb_strlen($query) > 0 && ($query[0] == "c" || $query[0] == "C")) {
				$menu = new Control($query);
				return $menu->output();
			} else {
				$menu = new Main($query);
				return $menu->output();
			}
			
		} elseif ($this->contains($query, '⟩')) {
			// if the query contains any machine-generated text 
			// (the unicode `⟩` is untypeable so we check for it)
			// we need to parse the query and extract the URLs.

			// So split based on the delimeter `⟩` and excise the delimeter and blanks.
			$splitQuery  = array_filter(str_replace("⟩", "", explode("⟩", $query)));
			               array_walk($splitQuery, array($this, 'trim_value'));

			$URIs = array_filter($splitQuery, array($this, 'is_spotify_uri'));
			$args = array_diff($splitQuery, $URIs);

			// Find which URI to use (by count, not by array index).
			// Arrows should be twice the number of URIs for the last URI.
			// For every one arrow fewer, traverse one URI backwards. 
			$arrows = mb_substr_count($query, "⟩");
			$depth = count($URIs) - (2 * count($URIs) - $arrows); // equiv to $arrows - count($URIs).

			$options = array(
				'depth'  => $depth,
				'URIs'   => $URIs,
				'args'   => $args,
				'search' => '',
				'query'  => $query
			);

			if (mb_substr($query, -1) == "⟩") { // Machine-generated
				$menu = new Detail($options);
				return $menu->output();

			} elseif($depth > 0) {
				$search = array_pop($args);
				$options['search'] = $search;
				$options['args'] = $args;

				$menu = new Detail($options);
				return $menu->output();

			} else {
				$menu = new Search(end($args));
				return $menu->output();
			}

		} else {
			$menu = new Search($query);
			$results = $menu->output();

			if(mb_strlen($query) > 0 && ($query[0] == "c" || $query[0] == "C")) {
				$controlMenu = new Control($query);
				$results = array_merge($controlMenu->output(), $results);
			}

			return $results;
		}
	}

	public function process($action) {
		if($this->contains($action, '⟩')) {
			$splitAction = explode('⟩', $action);
			$command = array_shift($splitAction);

			if($command == 'country') {
				$this->alfred->options('country', $splitAction[0]);
			} else if($command == 'next') {
				$song = $this->respondingSpotifyQuery('next track');

				if($splitAction[0] && $splitAction[0] == 'output') {
					$icon = ($song['state'] == "playing") ? "▶" : "‖";

					$this->alfred->notify(
						$song['album'] . " — " . $song['artist'], 
						$icon . " " . $song['title'], 
						// $song['url'],
						"",
						"",
						"",
						$song['url']);
				}

			} else if($command == 'previous') {
				$song = $this->respondingSpotifyQuery('previous track');

				if($splitAction[0] && $splitAction[0] == 'output') {
					$icon = ($song['state'] == "playing") ? "▶" : "‖";

					$this->alfred->notify(
						$song['album'] . " — " . $song['artist'], 
						$icon . " " . $song['title'], 
						// $song['url'],
						"",
						"",
						"",
						$song['url']);
				}

			} else if($command == 'playpause') {
				$song = $this->respondingSpotifyQuery('playpause');

				if($splitAction[0] && $splitAction[0] == 'output') {
					$icon = ($song['state'] == "playing") ? "▶" : "‖";

					$this->alfred->notify(
						$song['album'] . " — " . $song['artist'], 
						$icon . " " . $song['title'], 
						// $song['url'],
						"",
						"",
						"",
						$song['url']);
				}

			} else if($command == 'spotify') {
				$as = new ApplicationApplescript("Spotify", $splitAction[0]);
				$as->run();
			}
		} else {
			return "Could not process command!";
		}
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}

	protected function trim_value(&$value) { 
		$value = trim($value);
	}

	// TODO cite
	protected function is_spotify_uri($item) {
		$regex = '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x';

		return preg_match($regex, $item);
	}

	protected function respondingSpotifyQuery($query) {
		$as = new ApplicationApplescript("Spotify", $query . " \n return name of current track & \"✂\" & album of current track & \"✂\" & artist of current track & \"✂\" & spotify url of current track & \"✂\" & player state");
		$result = $as->run();

		$array = explode("✂", $result);
		if($array[0] == "") {
			$array[0] = "No track playing";
			$array[1] = "No album";
			$array[2] = "No artist";
			$array[3] = "";
			$array[4] = "paused";
		}

		$data = array(
			'title' => $array[0],
			'album' => $array[1],
			'artist' => $array[2],
			'url' => $array[3],
			'state' => $array[4]
		);

		return $data;
	}
}