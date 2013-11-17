<?php
mb_internal_encoding("UTF-8");
include_once('include/helper.php');

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
		$results[0]['title']        = "$currentTrack";
		$results[0]['subtitle']     = "$currentAlbum by $currentArtist";
		$results[0]['arg']          = "playpause";
		$results[0]['icon']         = $currentStatus;

		$results[1]['title']        = "$currentAlbum";
		$results[1]['subtitle']     = "More from this album...";
		$results[1]['autocomplete'] = "$currentAlbum"; // TODO change to albumdetail
		$results[1]['valid']        = "no";
		$results[1]['icon']         = 'include/images/alfred/album.png';

		$results[2]['title']        = "$currentArtist";
		$results[2]['subtitle']     = "More by this artist...";
		$results[2]['autocomplete'] = $currentArtist; // TODO change to artistdetail
		$results[2]['valid']        = "no";
		$results[2]['icon']         = 'include/images/alfred/artist.png';

		$results[3]['title']        = "Search for music...";
		$results[3]['subtitle']     = "Begin typing to search";
		$results[3]['valid']        = "no";
		$results[3]['icon']         = "include/images/alfred/search.png";

		return $results;
	}

	public function controlPanel()
	{
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

	public function search($query)
	{
		// Run the search using all three types of API queries
		foreach (array('artist','album','track') as $type) {
			/* Fetch and parse the search results. */
			$urlQuery = str_replace("%3A", ":", urlencode($query));
			$json = fetch("http://ws.spotify.com/search/1/$type.json?q=" . $urlQuery);

			if(empty($json))
				throw new AlfredableException("No JSON returned from Spotify web search");

			$json = json_decode($json);

			/* Output the results. */
			$currentResultNumber = 1;
			foreach ($json->{$type . "s"} as $key => $value) {
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
				$currentResult['arg']          = "play track \"$value->href\"";
				$currentResult['autocomplete'] = "$value->href ⟩ $query ⟩";
				$currentResult['icon'] = "include/images/alfred/$type.png";

				$results[] = $currentResult;
				$currentResultNumber++;
			}
		}

		/* Sort results by popularity. */
		if(!empty($results))
			usort($results, "popularitySort");

		/* Give the option to continue searching in Spotify because even I know my limits. */
		$results[] = [
			'title' => "Search for $query",
			'subtitle' => "Continue this search in Spotify...",
			'uid' => "bs-spotify-$query-more",
			'arg' => 'activate (open location "spotify:search:' . $query . '")',
			'icon' => 'include/images/alfred/search.png'
		];

		return $results;
	}

	public function detail($URIs, $args = null)
	{
		if (count($args) <= 1)
			return Spotifious::vanillaDetail($URIs);

		return Spotifious::filteredSearch($URIs, $args);
	}

	public function vanillaDetail($URIs)
	{
		/* Do additional query-parsing. */
		$explodedURI = explode(":", $URIs[0]);
		$type       = $explodedURI[1];
		$provided   = ($type == "artist") ? "album" : "track";

		/* Fetch and parse the details. */
		$json = fetch("http://ws.spotify.com/lookup/1/.json?uri=$URIs[0]&extras=$provided" . "detail");

		if(empty($json))
			throw new AlfredableException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		/* Output the details. */
		$results[0]['title']        = $json->$type->name;
		$results[0]['subtitle']     = "Play $type";
		$results[0]['arg']          = 'activate (open location "' . $URIs[0] . '")';
		$results[0]['icon']         = "include/images/alfred/$type.png";

		// TODO top tracks?

		if($provided == "album") {
			$currentResultNumber = 1;
			$albums = array();
			foreach ($json->$type->{$provided . "s"} as $key => $value) {
				$value = $value->$provided;

			if(in_array($value->name, $albums))
				continue;

				$currentResult['title'] = $value->name;
				$currentResult['subtitle'] = "Browse this $provided...";
				$currentResult['valid'] = "no";
				$currentResult['autocomplete'] = "$detailURL ⟩ $value->href ⟩ $query ⟩⟩";
				$currentResult['icon'] = "include/images/alfred/album.png";

				$results[] = $currentResult;
				$albums[] = "$value->name";
				$currentResultNumber++;
			}
		} else {
			$currentResultNumber = 1;
			foreach ($json->$type->{$provided . "s"} as $key => $value) {
				$starString = floatToBars($value->popularity);

				// TODO show artist if not all tracks from same artist

				$currentResult['title'] = "$currentResultNumber. $value->name";
				$currentResult['subtitle'] = "$starString "  . beautifyTime($value->length);
				$currentResult['arg'] = 'play track "' . $value->href . '" in context "' . $detailURL . '"';
				$currentResult['icon'] = "include/images/alfred/track.png";

				$results[] = $currentResult;
				$currentResultNumber++;
			}
		}

		return $results;
	}

	public function filteredSearch($URIs, $args)
	{
		throw new AlfredableException("Filtered search not implemented 😓", get_defined_vars());
		// TODO
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

		$json = fetch($URL);

		if(empty($json))
			throw new AlfredableException("No JSON returned from Spotify web lookup");

		$json = json_decode($json);

		/* Output the details. */
		switch ($type) { // This could SO be DRY-er TODO.
			case 'track':
				$results = [
					[
						'title' => $json->$type->name,
						'subtitle' => "todo", //TODO
						'arg' => '', // TODO
						'icon' => "include/images/alfred/$type.png"
					],
					[
						'title' => $json->$type->album->name,
						'subtitle' => "More from this album...",
						'valid' => "no",
						'autocomplete' => $json->$type->album->href . " ⟩ ⟩",
						'icon' => "include/images/alfred/album.png"
					],
					[
						'title' => $json->$type->artists[0]->name,
						'subtitle' => "More by this artist...",
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
						'subtitle' => "Browse this $type...",
						'valid' => "no",
						'autocomplete' => $URI . " ⟩ ⟩",
						'icon' => "include/images/alfred/$type.png"
					],
					[
						'title' => $json->$type->artist,
						'subtitle' => "More by this artist...",
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
						'subtitle' => "Browse this $type...",
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
			'arg' => 'activate (open location "' . $URI . '")',
			// TODO icon
		];

		return $results;
	}
}