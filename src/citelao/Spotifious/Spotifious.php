<?php
namespace Spotifious;

use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

use OhAlfred\OhAlfred;
use OhAlfred\Applescript\ApplicationApplescript;
use OhAlfred\Command\Timeout;
use OhAlfred\Command\Command;
use OhAlfred\Exceptions\StatefulException;
use OhAlfred\HTTP\JsonParser;

use Spotifious\Actions\Applescript;

use Spotifious\Menus\Control;
use Spotifious\Menus\Detail;
use Spotifious\Menus\Main;
use Spotifious\Menus\Search;
use Spotifious\Menus\Settings;
use Spotifious\Menus\Setup;
use Spotifious\Menus\SetupCountryCode;

class Spotifious {
	protected $alfred;

	public function __construct($alfred) {
		$this->alfred = $alfred;
	}

	public function run($query) {
		// Correct for old Spotifious queries
		$q = str_replace("►", "⟩", $query);

		// Set defaults if they've not been set before
		if($this->alfred->options('track_notifications') == '') {
			$this->alfred->options('track_notifications', 'true');
		}

		$scopes = 'playlist-read-private';
		if($this->alfred->options('desired_scopes') != $scopes) {
			$this->alfred->options('desired_scopes', $scopes);
		}

		if($this->alfred->options('lookup_current_song') == '') {
			$this->alfred->options('lookup_current_song', 'true');
		}

		if($this->alfred->options('has_installed_spotify') == '' || $this->alfred->options('has_installed_spotify') == 'false') {
			$detector = new Command('open -Ra "Spotify"');
			$detector->run();
			$spotify_installed = !$detector->status();
			$this->alfred->options('has_installed_spotify', $spotify_installed);
		}

		// Display the setup menu if the app isn't setup.
		// Or the "options" menu if the S key is pressed
		if($this->alfred->options('country') == '' ||
			$this->alfred->options('has_installed_spotify') == 'false' ||
			$this->alfred->options('spotify_client_id') == '' ||
			$this->alfred->options('spotify_secret') == '' ||
			$this->optedOut() ||
			$this->alfred->options('spotify_access_token') == '' ||
			$this->alfred->options('spotify_access_token_expires') == '' ||
			$this->alfred->options('spotify_refresh_token') == '' ||
			$this->alfred->options('desired_scopes') != $this->alfred->options('registered_scopes')  || 
			$this->contains($query, "Country Code ⟩")) {

			// Check version first
			if(version_compare(phpversion(), "5.4") < 0) {
				throw new StatefulException("Your PHP version is too low",
					array(
						"Your version" => phpversion(),
						"Required version" => "5.4"
					));
			}

			// If we are trying to configure country code
			if($this->contains($query, "Country Code ⟩")) {
				$menu = new SetupCountryCode($query);
				return $menu->output();
			}

			$menu = new Setup($query, $this->alfred);
			return $menu->output();
		}

		// Don't bother connecting if we've opted out :).
		$api = null;
		if(!$this->optedOut()) {
			$api = $this->getSpotifyApi();
		}
		// TODO
		// if expired
			// attempt refresh
			// if failed, prompt for relogin

		// Fetch playlists on a first run...
		if($api && $this->alfred->options('playlists') == '') {
			$this->update_playlists_cache($api);
		}

		if (mb_strlen($query) <= 3) {
			if(mb_strlen($query) > 0 && ($query[0] == "c" || $query[0] == "C")) {
				$menu = new Control($query);
				return $menu->output();
			} elseif(mb_strlen($query) > 0 && ($query[0] == "s" || $query[0] == "S")) {
				$menu = new Settings($query, $this->alfred, $api);
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
				$menu = new Detail($options, $this->alfred, $api);
				return $menu->output();

			} elseif($depth > 0) {
				$search = array_pop($args);
				$options['search'] = $search;
				$options['args'] = $args;

				$menu = new Detail($options, $this->alfred, $api);
				return $menu->output();

			} else {
				$menu = new Search(end($args), $this->alfred, $api);
				return $menu->output();
			}

		} else {
			$menu = new Search($query, $this->alfred, $api);
			$results = $menu->output();

			if(mb_strlen($query) > 0 && ($query[0] == "c" || $query[0] == "C")) {
				$controlMenu = new Control($query);
				$results = array_merge($controlMenu->output(), $results);
			}

			return $results;
		}
	}

	public function process($action) {
		$api = null;
		if($this->alfred->options('spotify_access_token') && !$this->optedOut()) {
			$api = $this->getSpotifyApi();
		}
		// TODO refresh token
		// this is identical code to run()
		// if expired
			// attempt refresh
			// if failed, prompt for relogin

		// Handle JSON if given

		// print_r("GGGG");
		if($action[0] == "{") {
			$json = JsonParser::parse($action);
			$options = (isset($json->options)) 
				? $json->options
				: new \stdClass();

			$action = null;
			if($json->action == "applescript") {
				$action = new Applescript($options, $this->alfred, $api);
			} else if($json->action == "command") {
				if(!isset($options->command)) {
					throw new StatefulException("You have to have a command to run!");
				}
				$action = new Command($options->command);
			} else if($json->action == "spotifious") {
				$v = $this->alfred->version()[0];
				$command = ($options->command)
					? $options->command
					: '';
				$passed_options = (object) array(
					'application' => "Alfred $v",
					'command' => 'run trigger "search" in workflow "com.citelao.spotifious" with argument "' . $command . '"'
				);
				$action = new Applescript($passed_options, $this->alfred, $api);
			} else {
				throw new StatefulException("Could not process command", array('json' => $json));
			}
			$action->run();
			return;
		}

		if($this->contains($action, '⟩')) {
			$splitAction = explode('⟩', $action);
			$command = array_shift($splitAction);

			if($command == 'country') {
				$this->alfred->options('country', $splitAction[0]);

				$v = $this->alfred->version()[0];
				$as = new ApplicationApplescript("Alfred $v", 'run trigger "search" in workflow "com.citelao.spotifious"');
				$as->run();

			} else if($command == 'appsetup') {
				// Autokill server in 10 minutes
				$server = new Timeout(10 * 60, "php -S localhost:11114 & open 'http://localhost:11114/include/setup/index.php'");
				$server->run();

			} else if($command == 'applink') {
				// Autokill server in 10 minutes
				$server = new Timeout(10 * 60, "php -S localhost:11114 & open 'http://localhost:11114/include/setup/link.php'");
				$server->run();

			} else if($command == 'togglenotifications') {
				$current = $this->alfred->options('track_notifications');

				if($current == 'true') {
					$this->alfred->options('track_notifications', 'false');
				} else {
					$this->alfred->options('track_notifications', 'true');
				}

			} else if($command == 'update_playlists_cache') {
				if($api) {
					$this->update_playlists_cache($api);
				}

			} else if($command == 'next') {
				$song = $this->respondingSpotifyQuery('next track');

				$this->alfred->notify(
					$song['album'] . " — " . $song['artist'], 
					$song['title'], 
					// $song['url'],
					"",
					"",
					"",
					$song['url']);

			} else if($command == 'previous') {
				$song = $this->respondingSpotifyQuery('previous track');

				$this->alfred->notify(
						$song['album'] . " — " . $song['artist'], 
						$song['title'], 
						// $song['url'],
						"",
						"",
						"",
						$song['url']);

			} else if($command == 'playpause') {
				$song = $this->respondingSpotifyQuery('playpause');

				$icon = ($song['state'] == "playing") ? "▶" : "‖";
				$this->alfred->notify(
					$song['album'] . " — " . $song['artist'], 
					$icon . " " . $song['title'], 
					// $song['url'],
					"",
					"",
					"",
					$song['url']);

			} else if($command == 'volup') {
				$as = new ApplicationApplescript("Spotify", "if sound volume < 90 then \n set sound volume to sound volume + 10 \n else \n set sound volume to 100 \n end if");
				$as->run();
				
			} else if($command == 'voldown') {
				$as = new ApplicationApplescript("Spotify", "if sound volume > 10 then \n set sound volume to sound volume - 10 \n else \n set sound volume to 0 \n end if");
				$as->run();

			} else if($command == 'spotify') {
				$as = new ApplicationApplescript("Spotify", $splitAction[0]);
				$as->run();
			} else if($command == 'respond') {
				$song = $this->splitSpotifyResponse($splitAction[0]);

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

			$v = $this->alfred->version()[0];
			if($splitAction[0] && $splitAction[0] == 'return') {
				$as = new ApplicationApplescript("Alfred $v", 'run trigger "search" in workflow "com.citelao.spotifious"');
				$as->run();
			} elseif($splitAction[0] && $splitAction[0] == 'returnControls') {
				$as = new ApplicationApplescript("Alfred $v", 'run trigger "search" in workflow "com.citelao.spotifious" with argument "c"');
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
		$regex = '/^(spotify:(?:album|artist|track|(?:user:[^:]+:)?playlist):[a-zA-Z0-9]+)$/x';

		return preg_match($regex, $item);
	}

	protected function respondingSpotifyQuery($query) {
		$as = new ApplicationApplescript("Spotify", $query . " \n return name of current track & \"✂\" & album of current track & \"✂\" & artist of current track & \"✂\" & spotify url of current track & \"✂\" & player state");
		$result = $as->run();

		return $this->splitSpotifyResponse($result);
	}

	protected function splitSpotifyResponse($resp) {
		$array = explode("✂", $resp);
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

	protected function getSpotifyApi() {
		if($this->optedOut()) {
			throw new StatefulException("Trying to get API for opted out user. My bad.");
		}

		$api = new SpotifyWebAPI();

		// If the access token has expired :(
		if ($this->alfred->options('spotify_access_token_expires') < time() + 60*60*3) {
			$session = new Session($this->alfred->options('spotify_client_id'), $this->alfred->options('spotify_secret'), 'http://localhost:11114/callback.php');
			$session->refreshAccessToken($this->alfred->options('spotify_refresh_token'));

			$this->alfred->options('spotify_access_token_expires', $session->getTokenExpiration());
			$this->alfred->options('spotify_access_token', $session->getAccessToken());
		}

		$api->setAccessToken($this->alfred->options('spotify_access_token'));

		return $api;
	}

	protected function optedOut() {
		return $this->alfred->options('spotify_app_opt_out') == 'true';
	}

	protected function update_playlists_cache($api) {
		$playlists = $api->getMyPlaylists();
		$search_data = array();
		foreach ($playlists->items as $playlist) {
			$search_data[] = array(
				'id' => $playlist->id,
				'name' => $playlist->name,
				'uri' => $playlist->uri,
				'owner' => (
					property_exists($playlist->owner, 'display_name')
						? $playlist->owner->display_name
						: property_exists($playlist->owner, 'id')
					) ? $playlist->owner->id : 'unknown'
			);
		}
		$datetime = new \DateTime("now");
		$this->alfred->options('playlists_cache_date', $datetime->format("@U"));
		$this->alfred->options('playlists', $search_data);
	}
}
