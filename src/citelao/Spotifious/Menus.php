<?php
namespace Spotifious;
use OhAlfred\OhAlfred;
use OhAlfred\StatefulException;

class Menus {
	public function main() {
		/* Get now-playing info. */
		$current = Helper::now();
		$currentTrack             = ($current['track'] == null) ? "No Track"  : $current['track'];
		$currentAlbum             = ($current['album'] == null) ? "No Album"  : $current['album'];
		$currentArtist            = ($current['artist'] == null) ? "No Artist" : $current['artist'];
		$currentStatus            = ($current['playing'] == 'true') ? "include/images/alfred/paused.png" : "include/images/alfred/playing.png";

		/* Output now-playing info. */
		$results[0]['title']        = "$currentTrack";
		$results[0]['subtitle']     = "$currentAlbum by $currentArtist";
		$results[0]['arg']          = OhAlfred::actionify(
										array("discrete", "playpause"),
										array("queue", $current['trackuri']),
										array("star", $current['trackuri']),
										array("copy", $current['trackuri']),
										array("open", $current['albumuri']) // TODO select correct track
									  );
		$results[0]['icon']         = $currentStatus;

		$results[1]['title']        = "$currentAlbum";
		$results[1]['subtitle']     = "More from this album…";
		$results[1]['autocomplete'] = "{$current['albumuri']} ⟩ $currentAlbum ⟩";
		$results[1]['valid']        = "no";
		$results[1]['icon']         = 'include/images/alfred/album.png';

		$results[2]['title']        = "$currentArtist";
		$results[2]['subtitle']     = "More by this artist…";
		$results[2]['autocomplete'] = "{$current['artisturi']} ⟩ $currentArtist ⟩";
		$results[2]['valid']        = "no";
		$results[2]['icon']         = 'include/images/alfred/artist.png';

		$results[3]['title']        = "Search for music…";
		$results[3]['subtitle']     = "Begin typing to search";
		$results[3]['valid']        = "no";
		$results[3]['icon']         = "include/images/alfred/search.png";

		return $results;
	}

	public function controls() {
		// TODO
		throw new StatefulException("No controls implemented");
	}

	public function settings() {
		// TODO
		throw new StatefulException("Settings not implemented");
	}

	public function search($query, $country) {
		// Run the search using all three types of API queries
		foreach (array('artist','album','track') as $type) {
			/* Fetch and parse the search results. */
			$urlQuery = str_replace("%3A", ":", urlencode($query));
			$json = OhAlfred::fetch("http://ws.spotify.com/search/1/$type.json?q=" . $urlQuery);

			if(empty($json))
				throw new StatefulException("No JSON returned from Spotify web search");

			$json = json_decode($json);

			if($json == null)
				throw new StatefulException("JSON error: " . json_last_error());

			/* Output the results. */
			foreach ($json->{$type . "s"} as $key => $value) {

				/* Make sure it's available */
				if($type == 'album') {
					if(!strstr($value->availability->territories, $country) && $value->availability->territories != '')
						continue;
					
				} elseif ($type == 'track') {
					if(!strstr($value->album->availability->territories, $country) && $value->album->availability->territories != '')
						continue;
				}

				/* Weight popularity. */
				$popularity = $value->popularity;

				if($type == 'artist')
					$popularity += .5;

				if($type == 'album')
					$popularity += .15;

				/* Convert popularity to bars. */
				$starString = Helper::floatToBars($popularity);

				if($type == 'track') {
					$subtitle = "$starString " . $value->album->name . " by " . $value->artists[0]->name;
					$genericResultArtwork = "include/images/alfred/track.png";
				} elseif($type == 'album') {
					$subtitle = "$starString Album by " . $value->artists[0]->name;
					$genericResultArtwork = "include/images/alfred/album.png";
				} else {
					$subtitle = "$starString " . ucfirst($type);
					$genericResultArtwork = "include/images/alfred/artist.png";
				}

				$currentResult['title']        = $value->name;
				$currentResult['subtitle']     = $subtitle;

				$currentResult['uid']          = "bs-spotify-$query-$type";
				$currentResult['popularity']   = $popularity;

				// `arg` is only used if item is valid, likewise `autocomplete` is
				// only used if item is not valid. Tracks run an action, everything
				// else autocompletes.
				$currentResult['valid']        = ($type == 'track') ? 'yes' : 'no';
				$currentResult['arg']          = ($type == 'track') ? OhAlfred::actionify(
													array("play", $value->href),
													array("queue", $value->href),
													array("star", $value->href),
													array("copy", $value->href),
													array("open", $value->album->href)) : '';
				$currentResult['autocomplete'] = "$value->href ⟩ $query ⟩";
				$currentResult['icon'] = "include/images/alfred/$type.png";

				$results[] = $currentResult;
			}
		}

		/* Sort results by popularity. */
		if(!empty($results))
			usort($results, array('Spotifious\Helper', 'popularitySort'));

		/* Give the option to continue searching in Spotify because even I know my limits. */
		$results[] = [
			'title' => "Search for $query",
			'subtitle' => "Continue this search in Spotify…",
			'uid' => "bs-spotify-$query-more",
			'arg' => OhAlfred::actionify(array("search", $query)),
			'icon' => 'include/images/alfred/search.png'
		];

		return $results;
	}

	public function detail($URIs, $args, $depth, $search = null) {
		/* Parse the searched URI */
		$currentURI = $URIs[$depth - 1];
		$explodedURI = explode(":", $currentURI);
		$type       = $explodedURI[1];
		$detail   = ($type == "artist") ? "album" : "track";

		/* Fetch and parse the details. */
		$json = OhAlfred::fetch("http://ws.spotify.com/lookup/1/.json?uri=$currentURI&extras=$detail" . "detail");

		if(empty($json))
			throw new StatefulException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new StatefulException("JSON error: " . json_last_error());

		/* Output the details. */
		$scope['title']        = $json->$type->name;
		$scope['subtitle']     = "Play $type";
		$scope['arg']          = OhAlfred::actionify(
										array("play", $currentURI),
										array("queue", $currentURI),
										array("star", $currentURI),
										array("copy", $currentURI),
										array("open", $currentURI));
		$scope['icon']         = "include/images/alfred/$type.png";

		// TODO top tracks?

		if(!strstr($json->$type->availability->territories, $country) && $json->$type->availability->territories != '')
			$scope['subtitle'] .= "; not available where you live.";

		if($detail == "album") {
			$albums = array();
			$query = implode(" ⟩", $args);
			foreach ($json->$type->{$detail . "s"} as $key => $value) {
				$value = $value->$detail;

				if(in_array($value->name, $albums))
					continue;

				/* Make sure it's available */
				if(!strstr($value->availability->territories, $country) && $value->availability->territories != '')
					continue;

				$currentResult['title'] = $value->name;
				$currentResult['subtitle'] = "Browse this $detail…";
				$currentResult['valid'] = "no";
				$currentResult['autocomplete'] = "$currentURI ⟩ $value->href ⟩ $query ⟩" . $search . "⟩";
				$currentResult['icon'] = "include/images/alfred/album.png";

				if($search != null && !mb_stristr($currentResult['title'], $search))
					continue;

				$results[] = $currentResult;
				$albums[] = "$value->name";
			}
		} else {
			foreach ($json->$type->{$detail . "s"} as $key => $value) {
				$popularityString = Helper::floatToBars($value->popularity);

				// TODO show artist if not all tracks from same artist

				$currentResult['title'] = $value->{'track-number'} . ". $value->name";
				$currentResult['subtitle'] = "$popularityString "  . Helper::beautifyTime($value->length);
				$currentResult['arg'] = OhAlfred::actionify(
										array("play", $value->href, $currentURI),
										array("queue", $value->href),
										array("star", $value->href),
										array("copy", $value->href),
										array("open", $currentURI));
				$currentResult['icon'] = "include/images/alfred/track.png";

				if($search != null && !mb_stristr($currentResult['title'], $search))
					continue;

				$results[] = $currentResult;
			}
		}

		if ($search == null) {
			array_unshift($results, $scope);
		} else {
			array_push($results, $scope);
		}

		return $results;
	}

	public function convertable($URI) {
		/* Do additional query-parsing. */
		$explodedURI = explode(":", $URI);
		$type       = $explodedURI[1];
		$detail   = ($type == "artist") ? "album" : "track";
		$detailNeeded = ($type != "track");

		if($type == "app")
			throw new StatefulException("Spotifious cannot handle app URLs (yet)"); // TODO

		if(contains($URI,"playlist"))
			throw new StatefulException("Spotifious cannot handle playlist URLs (yet)"); // TODO

		/* Fetch and parse the details. */
		$URL = "http://ws.spotify.com/lookup/1/.json?uri=$URI";
		if($detailNeeded)
			$URL .= "&extras=$detail" . "detail";

		$json = OhAlfred::fetch($URL);

		if(empty($json))
			throw new StatefulException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new StatefulException("JSON error: " . json_last_error());
			
		/* Output the details. */
		switch ($type) { // This could SO be DRY-er TODO.
			case 'track':
				$results = [
					[
						'title' => $json->$type->name,
						'subtitle' => "Play this song",
						'arg' => OhAlfred::actionify(
										array("play", $URI, $json->$type->album->href),
										"null",
										"null",
										"null",
										array("open", $currentURI)),
						'icon' => "include/images/alfred/$type.png"
					],
					[
						'title' => $json->$type->album->name,
						'subtitle' => "More from this album…",
						'valid' => "no",
						'autocomplete' => $json->$type->album->href . " ⟩ ⟩",
						'icon' => "include/images/alfred/album.png"
					],
					[
						'title' => $json->$type->artists[0]->name,
						'subtitle' => "More by this artist…",
						'valid' => "no",
						'autocomplete' => $json->$type->artists[0]->href . " ⟩ ⟩",
						'icon' => "include/images/alfred/artist.png"
					]
				];
				break;
			case 'album':
				$results = [
					[
						'title' => $json->$type->name,
						'subtitle' => "Browse this $type…",
						'valid' => "no",
						'autocomplete' => $URI . " ⟩ ⟩",
						'icon' => "include/images/alfred/$type.png"
					],
					[
						'title' => $json->$type->artist,
						'subtitle' => "More by this artist…",
						'valid' => "no",
						'autocomplete' => $json->$type->{'artist-id'} . " ⟩ ⟩",
						'icon' => "include/images/alfred/artist.png"
					]
				];
				break;
			case 'artist':
				$results = [
					[
						'title' => $json->$type->name,
						'subtitle' => "Browse this $type…",
						'valid' => "no",
						'autocomplete' => $URI . " ⟩ ⟩",
						'icon' => "include/images/alfred/$type.png"
					]
				];
				break;
			default:
				throw new StatefulException("Unknown item type $type", 1);
				break;
		}

		$results[] = [
			'title' => $json->$type->name,
			'subtitle' => "Open this $type in Spotify",
			'arg' => OhAlfred::actionify(array('open', $URI))
			// TODO icon
		];

		return $results;
	}

	public function configure() {
		$hotkeysConfigured = Helper::hotkeysConfigured();
		$helperAppConfigured = Helper::helperAppConfigured();
		$countryCodeConfigured = Helper::countryCodeConfigured();

		$results[] = [
			'title' => 'Welcome to Spotifious!',
			'subtitle' => 'You need to configure a few more things before you can use Spotifious.',
			'icon' => 'include/images/alfred/configuration.png',
			'valid' => 'no'
		];

		$results[] = [
			'title' => '1. Bind your hotkeys',
			'subtitle' => 'Action this to bind automatically, or set them yourself in Alfred preferences.',
			'icon' => $hotkeysConfigured ? 'include/images/alfred/checked.png' : 'include/images/alfred/unchecked.png',
			'valid' => $hotkeysConfigured ? 'no' : 'yes'
		];

		$results[] =[
			'title' => '2. Install the helper app in Spotify',
			'subtitle' => 'This will open your web browser so you can activate Spotify developer mode.',
			'icon' => $helperAppConfigured ? 'include/images/alfred/checked.png' : 'include/images/alfred/unchecked.png',
			'arg' => OhAlfred::actionify(array("config", "helperapp")),
			'valid' => $helperAppConfigured ? 'no' : 'yes'
		];

		$results[] = [
			'title' => '3. Find your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => $countryCodeConfigured ? 'include/images/alfred/checked.png' : 'include/images/alfred/unchecked.png',
			'autocomplete' => $countryCodeConfigured ? '' : 'Country Code ⟩',
			'valid' => 'no'
		];

		$results[] = [
			'title' => 'You can access settings easily',
			'subtitle' => 'Type `s` from the main menu', // TODO implement settings
			'icon' => 'include/images/alfred/info.png'
		];

		return $results;
	}

	public function countries($search = null) {
		// Fetch list of country codes
		$json = OhAlfred::fetch('https://raw.github.com/johannesl/Internationalization/master/countrycodes.json');

		if(empty($json))
			throw new StatefulException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new StatefulException("JSON error: " . json_last_error());

		foreach ($json as $country => $code) {
			if ($search != null && !@mb_stristr($country . $code, $search))
				continue;

			$results[] = [
				'title' => $country,
				'subtitle' => $code,
				'arg' => OhAlfred::actionify(array("config", "country", "$code"))
			];
		}

		// Alphabetize w/ weighting.
		usort($results, array('Spotifious\Helper','countrySort'));

		return $results;
	}
}