<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
include_once('include/helper.php');

/**
 * Spotifious (v0.7)
 * 	a natural Spotify controller for Alfred <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

/* Parse the query. */
$results     = array();
$rawQuery    = normalize($argv[2]);
$maxResults  = 15;

$queryBits   = str_replace("⟩", "", explode("⟩", $rawQuery));
               array_walk($queryBits, 'trim_value');
$query       = $queryBits[count($queryBits)-1];

if(mb_strlen($rawQuery) < 4) {
	/* If the query is tiny, show the main menu. */
	
	/* If we want the control panel, show the control panel */
	if(substr($query, 0, 1) == "c") {
		// TODO
		$results[] = [
			title => "play pause",
			arg => "playpause"];

		$results[] = [
			title => "previous",
			arg => "previous track"];

		$results[] = [
			title => "next",
			arg => "next track"];

		$results[] = [
			title => "star"];

		$results[] = [
			title => "shuffle",
			arg => "set shuffling to not shuffling"];

		$results[] = [
			title => "repeat"];

		$results[] = [
			title => "volup"];

		$results[] = [
			title => "voldown"];

		/* Do basic filtering on the query to sort the options */
		$rest = substr($query, 1);
		// TODO
	} else {
		/* Get now-playing info. */
		$current = now();
		$currentTrack             = $current[0];
		$currentAlbum             = $current[1];
		$currentArtist            = $current[2];
		$currentURL               = $current[3];
		$currentStatus            = ($current[4] == 'playing') ?
									"include/images/alfred/paused.png" :
									"include/images/alfred/playing.png";
		
		/* Output now-playing info. */
		$results[0][title]        = "$currentTrack";
		$results[0][subtitle]     = "$currentAlbum by $currentArtist";
		$results[0][arg]          = "playpause";
		$results[0][icon]         = $currentStatus;
		
		$results[1][title]        = "$currentAlbum";
		$results[1][subtitle]     = "More from this album...";
		$results[1][autocomplete] = "$currentAlbum"; // TODO change to albumdetail
		$results[1][valid]        = "no";
		$results[1][icon]         = 'include/images/alfred/album.png';
		
		$results[2][title]        = "$currentArtist";
		$results[2][subtitle]     = "More by this artist...";
		$results[2][autocomplete] = $currentArtist; // TODO change to artistdetail
		$results[2][valid]        = "no";
		$results[2][icon]         = 'include/images/alfred/artist.png';
		
		$results[3][title]        = "Search for music...";
		$results[3][subtitle]     = "Begin typing to search";
		$results[3][valid]        = "no";
		$results[3][icon]         = "include/images/alfred/search.png";
	}
// TODO if spotify URL/URI
} elseif(mb_substr($rawQuery, -1, 1) == "⟩") { 
	// If the query is an unmodified machine-generated one, generate a detail menu.
	
	// If the query is two levels deep, generate the detail menu of the second
	// URL. Otherwise generate a detail menu based on the first (or only) URL.
	
	// spotify:album:5XGQ4L4XsTI3uIZiAfeAum ⟩ Transatlanticism ⟩
	// spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ Death Cab for Cutie ⟩
	// spotify:artist:0YrtvWJMgSdVrk3SfNjTbx ⟩ spotify:album:0uzgpzN1ZsCNSwnsVUh4bQ ⟩ Death Cab for Cutie ⟩⟩

	/* Do additional query-parsing. */
	$detailURL  = (mb_substr($rawQuery, -2, 1) == "⟩") ? $queryBits[1] : $queryBits[0];
	$detailBits = explode(":", $detailURL);
	$type       = $detailBits[1];
	$provided   = ($detailBits[1] == "artist") ? "album" : "track";
	$query      = $queryBits[count($queryBits)-2]; 
	
	/* Fetch and parse the details. */
	$json = fetch("http://ws.spotify.com/lookup/1/.json?uri=$detailURL&extras=$provided" . "detail");
	
	if(empty($json))
		throw new Exception("No JSON returned from Spotify web lookup");
		
	$json = json_decode($json);
	
	/* Output the details. */
	$results[0][title]        = $json->$type->name;
	$results[0][subtitle]     = "Play $type";
	$results[0][arg]          = 'activate (open location "' . $detailURL . '")';
	$results[0][icon]         = "include/images/alfred/$type.png";
	
	if($provided == "album") {
		$currentResultNumber = 1;
		$albums = array();
		foreach ($json->$type->{$provided . "s"} as $key => $value) {
			if($currentResultNumber > $maxResults)
				continue;
				
			$value = $value->$provided;
			
			if(in_array($value->name, $albums))
				continue;
			
			$currentResult[title] = $value->name;
			$currentResult[subtitle] = "Browse this $provided...";
			$currentResult[valid] = "no";
			$currentResult[autocomplete] = "$detailURL ⟩ $value->href ⟩ $query ⟩⟩";
			$currentResult[icon] = "include/images/alfred/album.png";
			
			$results[] = $currentResult;
			$albums[] = "$value->name";
			$currentResultNumber++;
		}	
	} else {
		$currentResultNumber = 1;
		foreach ($json->$type->{$provided . "s"} as $key => $value) {
			$starString = floatToStars($value->popularity);
			
			$currentResult[title] = "$currentResultNumber. $value->name";
			$currentResult[subtitle] = "$starString "  . beautifyTime($value->length);
			$currentResult[arg] = 'play track "' . $value->href . '" in context "' . $detailURL . '"';
			$currentResult[icon] = "include/images/alfred/track.png";
			
			$results[] = $currentResult;
			$currentResultNumber++;
		}
	}
} else { 
	// If the query is completely user-generated, or the user has modified it, show the search menu.

	// Run the search using all three types of API queries
	foreach (array('artist','album','track') as $type) {
		/* Fetch and parse the search results. */
		$json = fetch("http://ws.spotify.com/search/1/$type.json?q=" . str_replace("%3A", ":", urlencode($queryBits[count($queryBits)-1])));

		if(empty($json))
			throw new Exception("No JSON returned from Spotify web search");
		
		$json = json_decode($json);
		
		/* Output the results. */
		$currentResultNumber = 1;
		foreach ($json->{$type . "s"} as $key => $value) {
			if($currentResultNumber > $maxResults / 3)
				continue;
			
			/* Weight popularity. */
			$popularity = $value->popularity;
			
			if($type == 'artist')
				$popularity += .5;
			if($type == 'album')
				$popularity += .15;
			
			/* Convert popularity to stars. */
			$starString = floatToStars($popularity);
			
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
			
			$currentResult[title]        = $value->name;
			$currentResult[subtitle]     = $subtitle;
			
			$currentResult[uid]          = "bs-spotify-$query-$type";
			$currentResult[popularity]   = $popularity;
			
			// `arg` is only used if item is valid, likewise `autocomplete` is
			// only used if item is not valid. Tracks run an action, everything
			// else autocompletes.
			$currentResult[valid]        = ($type == 'track') ? 'yes' : 'no';
			$currentResult[arg]          = "play track \"$value->href\"";
			$currentResult[autocomplete] = "$value->href ⟩ $query ⟩";
			$currentResult[icon] = "include/images/alfred/$type.png";
			
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
		'arg' => 'moo' // TODO, obviously
						// TODO icon too
	];
}

alfredify($results);

?>