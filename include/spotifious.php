<?php
mb_internal_encoding("UTF-8");
include_once('include/helper.php');
include_once('include/OhAlfred.php');

class Spotifious {
	public function mainMenu()
	{
		/* Get now-playing info. */
		$current = now();
		$currentTrack             = ($current[0] == null) ? "No Track"  : $current[0];
		$currentAlbum             = ($current[1] == null) ? "No Album"  : $current[1];
		$currentArtist            = ($current[2] == null) ? "No Artist" : $current[2];
		$currentURL               = $current[3];
		$currentStatus            = ($current[4] == 'playing') ?
			"include/images/alfred/paused.png" :
			"include/images/alfred/playing.png";

		/* Output now-playing info. */
		// TODO use sockets to get more album data.
		$results[0]['title']        = "$currentTrack";
		$results[0]['subtitle']     = "$currentAlbum by $currentArtist";
		$results[0]['arg']          = OhAlfred::actionify(array("discrete", "playpause"));
		$results[0]['icon']         = $currentStatus;

		$results[1]['title']        = "$currentAlbum";
		$results[1]['subtitle']     = "More from this album…";
		$results[1]['autocomplete'] = "$currentAlbum"; // TODO change to albumdetail
		$results[1]['valid']        = "no";
		$results[1]['icon']         = 'include/images/alfred/album.png';

		$results[2]['title']        = "$currentArtist";
		$results[2]['subtitle']     = "More by this artist…";
		$results[2]['autocomplete'] = $currentArtist; // TODO change to artistdetail
		$results[2]['valid']        = "no";
		$results[2]['icon']         = 'include/images/alfred/artist.png';

		$results[3]['title']        = "Search for music…";
		$results[3]['subtitle']     = "Begin typing to search";
		$results[3]['valid']        = "no";
		$results[3]['icon']         = "include/images/alfred/search.png";

		return $results;
	}

	public function controlPanel()
	{
		// TODO actionify
		// these are broken now, so throw an error.
		throw new AlfredableException("Control Panel not yet implemented");
		
		$results[] = [
			'title' => "play pause",
			'arg' => "playpause"];

		$results[] = [
			'title' => "previous",
			'arg' => "previous track"];

		$results[] = [
			'title' => "next",
			'arg' => "next track"];

		$results[] = [
			'title' => "star"];

		$results[] = [
			'title' => "shuffle",
			'arg' => "set shuffling to not shuffling"];

		$results[] = [
			'title' => "repeat"];

		$results[] = [
			'title' => "volup"];

		$results[] = [
			'title' => "voldown"];

		/* Do basic filtering on the query to sort the options */
		// TODO

		return $results;
	}

	public function settings()
	{
		// TODO implement hehe
	}

	public function search($query, $country = '')
	{
		// Run the search using all three types of API queries
		foreach (array('artist','album','track') as $type) {
			/* Fetch and parse the search results. */
			$urlQuery = str_replace("%3A", ":", urlencode($query));
			$json = OhAlfred::fetch("http://ws.spotify.com/search/1/$type.json?q=" . $urlQuery);

			if(empty($json))
				throw new AlfredableException("No JSON returned from Spotify web search");

			$json = json_decode($json);

			if($json == null)
				throw new AlfredableException("JSON error: " . json_last_error());

			/* Output the results. */
			foreach ($json->{$type . "s"} as $key => $value) {
				/* Make sure it's available */
				if($type == 'album') {
					if(!strstr($value->availability->territories, $country))
						continue;
					
				} elseif ($type == 'track') {
					if(!strstr($value->album->availability->territories, $country))
						continue;
				}

				/* Weight popularity. */
				$popularity = $value->popularity;

				if($type == 'artist')
					$popularity += .5;

				if($type == 'album')
					$popularity += .15;

				/* Convert popularity to stars. */
				$starString = floatToBars($popularity);

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
													"null",
													"null",
													"null",
													array("open", $value->album->href)) : '';
				$currentResult['autocomplete'] = "$value->href ⟩ $query ⟩";
				$currentResult['icon'] = "include/images/alfred/$type.png";

				$results[] = $currentResult;
			}
		}

		/* Sort results by popularity. */
		if(!empty($results))
			usort($results, "popularitySort");

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

	public function detail($URIs, $args, $depth, $search = null)
	{
		/* Parse the searched URI */
		$currentURI = $URIs[$depth - 1];
		$explodedURI = explode(":", $currentURI);
		$type       = $explodedURI[1];
		$detail   = ($type == "artist") ? "album" : "track";

		/* Fetch and parse the details. */
		$json = OhAlfred::fetch("http://ws.spotify.com/lookup/1/.json?uri=$currentURI&extras=$detail" . "detail");

		if(empty($json))
			throw new AlfredableException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new AlfredableException("JSON error: " . json_last_error());

		/* Output the details. */
		$results[0]['title']        = $json->$type->name;
		$results[0]['subtitle']     = "Play $type";
		$results[0]['arg']          = OhAlfred::actionify(
										array("play", $currentURI),
										"null",
										"null",
										"null",
										array("open", $currentURI));
		$results[0]['icon']         = "include/images/alfred/$type.png";

		// TODO top tracks?

		if($detail == "album") {
			$albums = array();
			$query = implode(" ⟩", $args);
			foreach ($json->$type->{$detail . "s"} as $key => $value) {
				$value = $value->$detail;

				if(in_array($value->name, $albums))
					continue;

				$currentResult['title'] = $value->name;
				$currentResult['subtitle'] = "Browse this $detail…";
				$currentResult['valid'] = "no";
				$currentResult['autocomplete'] = "$currentURI ⟩ $value->href ⟩ $query ⟩" . $search . "⟩";
				$currentResult['icon'] = "include/images/alfred/album.png";

				if($search != '' && !mb_stristr($currentResult['title'], $search))
					continue;

				$results[] = $currentResult;
				$albums[] = "$value->name";
			}
		} else {
			foreach ($json->$type->{$detail . "s"} as $key => $value) {
				$popularityString = floatToBars($value->popularity);

				// TODO show artist if not all tracks from same artist

				$currentResult['title'] = $value->{'track-number'} . ". $value->name";
				$currentResult['subtitle'] = "$popularityString "  . beautifyTime($value->length);
				$currentResult['arg'] = OhAlfred::actionify(
										array("play", $value->href, $currentURI),
										"null",
										"null",
										"null",
										array("open", $currentURI));
				$currentResult['icon'] = "include/images/alfred/track.png";

				if($search != '' && !mb_stristr($currentResult['title'], $search))
					continue;

				$results[] = $currentResult;
			}
		}

		return $results;
	}

	public function filteredSearch($URIs, $args, $depth, $search)
	{
		$results = Spotifious::detail($URIs, $args, $depth, $search);
		// TODO if artist search, allow searching for tracks by artist

		// Move scope to end to make searching work better with slow internet.
		$scope = $results[0];
		array_shift($results);
		$results[] = $scope;

		return $results;
	}

	public function convertableInfo($URI)
	{
		/* Do additional query-parsing. */
		$explodedURI = explode(":", $URI);
		$type       = $explodedURI[1];
		$detail   = ($type == "artist") ? "album" : "track";
		$detailNeeded = ($type != "track");

		if($type == "app")
			throw new AlfredableException("Spotifious cannot handle app URLs (yet)"); // TODO

		if(contains($URI,"playlist"))
			throw new AlfredableException("Spotifious cannot handle playlist URLs (yet)"); // TODO

		/* Fetch and parse the details. */
		$URL = "http://ws.spotify.com/lookup/1/.json?uri=$URI";
		if($detailNeeded)
			$URL .= "&extras=$detail" . "detail";

		$json = OhAlfred::fetch($URL);

		if(empty($json))
			throw new AlfredableException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new AlfredableException("JSON error: " . json_last_error());
			
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
				throw new AlfredableException("Unknown item type $type", 1);
				break;
		}

		$results[] = [
			'title' => $json->$type->name,
			'subtitle' => "Open this $type in Spotify",
			'arg' => 'activate (open location "' . $URI . '")', // TODO actionify
			// TODO icon
		];

		return $results;
	}

	public function configure($hotkeysConfigured, $helperAppConfigured, $countryCodeConfigured)
	{
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

	public function countries($search = '') 
	{
		// Fetch list of country codes
		$json = OhAlfred::fetch('https://raw.github.com/johannesl/Internationalization/master/countrycodes.json');

		if(empty($json))
			throw new AlfredableException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		if($json == null)
			throw new AlfredableException("JSON error: " . json_last_error());

		foreach ($json as $country => $code) {
			if (!mb_stristr($country . $code, $search) && $search != '')
				continue;

			$results[] = [
				'title' => $country,
				'subtitle' => $code,
				'arg' => OhAlfred::actionify(array("config", "country", "$code"))
			];
		}

		// Alphabetize w/ weighting.
		usort($results, 'countrySort');

		return $results;
	}
}