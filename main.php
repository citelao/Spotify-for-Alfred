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
$showImages  = ($argv[1] == 'yes') ? true : false;
// $rawQuery    = iconv("UTF-8-MAC", "UTF-8", $argv[2]);
$rawQuery    = normalize($argv[2]);
$imgdResults = 6; // TODO do I want to keep this?
$maxResults  = 15;

$queryBits   = str_replace("►", "", explode("►", $rawQuery));
               array_walk($queryBits, 'trim_value');
$query       = $queryBits[count($queryBits)-1];

if(mb_strlen($rawQuery) < 3) {
	// If the query is tiny, show the main menu.
	
	/* Get now-playing info. */
	$currentTrack             = spotifyQuery("name of current track");
	$currentAlbum             = spotifyQuery("album of current track");
	$currentArtist            = spotifyQuery("artist of current track");
	$currentURL               = spotifyQuery("spotify url of current track");
	$currentStatus            = (spotifyQuery("player state") == 'playing') ? '►' : '❙❙';
	
	if($showImages) {
		$currentArtistArtwork = getArtistArtwork($currentArtist); // TODO use API to query artist URL? or just use plaintext from now on?
		$currentAlbumArtwork  = getTrackArtwork($currentURL);
	}
	
	/* Output now-playing info. */
	$results[0][title]        = "$currentStatus $currentTrack";
	$results[0][subtitle]     = "$currentAlbum by $currentArtist";
	$results[0][arg]          = "playpause";
	
	$results[1][title]        = "$currentAlbum";
	$results[1][subtitle]     = "More from this album...";
	$results[1][autocomplete] = "$currentAlbum"; // TODO change to show albumdetail
	$results[1][valid]        = "no";
	$results[1][icon]         = (!file_exists($currentAlbumArtwork)) ? 'icon.png' : $currentAlbumArtwork;
	
	$results[2][title]        = "$currentArtist";
	$results[2][subtitle]     = "More by this artist...";
	$results[2][autocomplete] = $currentArtist; // TODO change to show artistdetail
	$results[2][valid]        = "no";
	$results[2][icon]         = (!file_exists($currentArtistArtwork)) ? 'icon.png' : $currentArtistArtwork;
	
	$results[3][title]        = "Search for music...";
	$results[3][subtitle]     = "Begin typing to search";
	$results[3][valid]        = 'no';
} elseif(mb_substr($rawQuery, -1, 1) == "►") { 
	// if the query is an unmodified machine-generated one, generate a detail menu.
	
	// if the query is two levels deep, generate the detail menu of the second
	// URL. Otherwise generate a detail menu based on the first (or only) URL.
	
	/* Do additional query-parsing. */
	$detailURL  = (mb_substr($rawQuery, -2, 1) == "►") ? $queryBits[1] : $queryBits[0];
	$detailBits = explode(":", $detailURL);
	$type       = $detailBits[1];
	$provided   = ($detailBits[1] == "artist") ? "album" : "track";
	$query      = $queryBits[count($queryBits)-2]; 
	
	/* Fetch and parse the details. */
	$json = fetch("http://ws.spotify.com/lookup/1/.json?uri=$detailURL&extras=$provided" . "detail");
	
	if(empty($json))
		alfredify(array(array('title' => 'Sorry, there was an error', 'subtitle' => 'Please try again'))); // TODO better thing
		
	$json = json_decode($json);
	
	/* Output the details. */
	$results[0][title]        = $json->$type->name;
	$results[0][subtitle]     = "View $type in Spotify";
	$results[0][arg]          = 'activate (open location "' . $detailURL . '")';
	
	if($showImages)
		$results[0][icon]     = getTrackArtwork($detailURL);
	
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
			$currentResult[subtitle] = "Open this $provided...";
			$currentResult[valid] = "no";
			$currentResult[autocomplete] = "$detailURL ► $value->href ► $query ►►";
			
			if($showImages && $currentResultNumber <= $imgdResults) {
				$currentResult[icon] = getTrackArtwork($value->href);
			} else {
				$currentResult[icon] = "";
			}
			
			$results[] = $currentResult;
			$albums[] = "$value->name";
			$currentResultNumber++;
		}	
	} else {
		$currentResultNumber = 1;
		foreach ($json->$type->{$provided . "s"} as $key => $value) {
			$starString = floatToStars($value->popularity);
			
			$currentResult[title] = "$currentResultNumber. $value->name";
			$currentResult[subtitle] = "$starString " . beautifyTime($value->length);
			$currentResult[arg] = 'open location "' . $value->href . '"';
			
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
			continue; // TODO output a better error.
		
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
			} elseif($type == 'album') {
				$subtitle = "$starString Album by " . $value->artists[0]->name;
			} else {
				$subtitle = "$starString " . ucfirst($type);
			}
			
			$currentResult[title]        = $value->name;
			$currentResult[subtitle]     = $subtitle;
			
			$currentResult[uid]          = "bs-spotify-$query-$type";
			$currentResult[popularity]   = $popularity;
			
			// `arg` is only used if item is valid, likewise `autocomplete` is
			// only used if item is not valid. Tracks run an action, everything
			// else autocompletes.
			$currentResult[valid]        = ($type == 'track') ? 'yes' : 'no';
			$currentResult[arg]          = 'open location "' . $value->href . '"';
			$currentResult[autocomplete] = $value->href . " ► " . $query . " ►";
			
			if($showImages && $currentResultNumber <= $imgdResults / 3) {
				$currentResult[icon] = getTrackArtwork($value->href);
			} else {
				$currentResult[icon] = "";
			}
			
			$results[] = $currentResult;
			$currentResultNumber++;
		}
	}
	
	/* Sort results by popularity. */
	if(!empty($results))
		usort($results, "popularitySort");
}

alfredify($results);

?>