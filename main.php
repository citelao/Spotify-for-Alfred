<?php
include_once('include/helper.php');
mb_internal_encoding("UTF-8");

/**
 * Spotifious (v0.7)
 * 	a natural Spotify controller for Alfred <https://github.com/citelao/Spotify-for-Alfred/>
 * 	an Alfred extension by Ben Stolovitz <http://github.com/citelao/>
 **/

/* Parse the query. */
$showImages = ($argv[1] == 'yes') ? true : false;
$rawQuery   = $argv[2];
$queryBits  = str_replace("►", "", explode(" ► ", $rawQuery));

$maxResults = ($showImages) ? 6 : 15;

if(strlen($rawQuery) < 3) { 
	// if the query is tiny, show the main menu.
	
	// get now-playing info
	$currentTrack             = spotifyQuery("name of current track");
	$currentAlbum             = spotifyQuery("album of current track");
	$currentArtist            = spotifyQuery("artist of current track");
	$currentURL               = spotifyQuery("spotify url of current track");
	$currentStatus            = (spotifyQuery("player state") == 'playing') ? '►' : '❙❙';
	
	if($showImages) {
		$currentArtistArtwork = getArtistArtwork($currentArtist);
		$currentAlbumArtwork  = getTrackArtwork($currentURL);
	}
	
	// output now-playing info
	$results[0][title]        = "$currentStatus $currentTrack";
	$results[0][subtitle]     = "$currentAlbum by $currentArtist";
	$results[0][arg]          = "playpause";
	
	$results[1][title]        = "$currentAlbum";
	$results[1][subtitle]     = "More from this album...";
	$results[1][autocomplete] = "$currentAlbum"; // TODO change to show albumdetail
	$results[1][valid]        = "no";
	$results[1][icon]         = (!file_exists($currentAlbumArtwork)) ? 'icon.png' : $currentAlbumArtwork;
	
	$results[2][title]        = $currentArtist;
	$results[2][subtitle]     = "More by this artist...";
	$results[2][autocomplete] = "$currentArtist"; // TODO change to show albumdetail
	$results[2][valid]        = "no";
	$results[2][icon]         = (!file_exists($currentArtistArtwork)) ? 'icon.png' : $currentArtistArtwork;
	
	$results[3][title]        = "Search for music...";
	$results[3][subtitle]     = "Begin typing to search";
	$results[3][valid]        = 'no';
} elseif(mb_substr($rawQuery, -1, 1) == "►") { 
	// if the query is an unmodified machine-generated one, generate a detail menu.
	
	// if the query is two levels deep, generate the detail menu of the second
	// URL. Otherwise generate a detail menu based on the first (or only) URL.
	
	// $results[0][subtitle]        = mb_detect_encoding($rawQuery) . mb_detect_encoding("►") . "h"; //todo debug
	
	$detailURL  = ($rawQuery[strlen($rawQuery)-2] == "►") ? $queryBits[1] : $queryBits[0];
	$query      = $queryBits[count($queryBits)-1];
	$detailBits = explode(":", $detailURL);
	$type       = $detailBits[1];
	$provided   = ($detailBits[1] == "artist") ? "album" : "track";
	
	// fetch and parse the details
	$json = fetch("http://ws.spotify.com/lookup/1/.json?uri=$detailURL&extras=" . $provided);
	
	if(empty($json))
		return; // TODO find better error
	
	$json = json_decode($json);
	
	// organize the details
	$results[0][title]        = $json->$type->name;
	$results[0][subtitle]     = "View $type in Spotify";
	$results[0][arg]          = 'activate (open location "' . $detailURL . '")';
	
	$currentResultNumber = 6;
	foreach ($json->$type->{$provided . "s"} as $key => $value) {
		if($currentResultNumber > $maxResults)
			continue;
		
		$value = $value->$provided;
		
		$currentResult[title] = $value->name;
		$currentResult[subtitle] = "Open this album...";
		
		$results[] = $currentResult;
		$currentResultNumber++;
	}
	
} else { 
	// if the query is completely user-generated, or the user has modified it, show the search menu.
	foreach (array('track','artist','album') as $type) {
		$json = fetch("http://ws.spotify.com/search/1/$type.json?q=" . str_replace("%3A", ":", urlencode($rawQuery)));
		
		if(empty($json))
			continue;
		
		$json = json_decode($json);
		
		$currentResultNumber = 1;
		foreach ($json->{$type . "s"} as $key => $value) {
			if($currentResultNumber > $maxResults / 3)
				continue;
			
			// Figure out search rank
			$popularity = $value->popularity;
			
			if($type == 'artist') {
				$popularity+= .5;
			}
			
			// Convert popularity to stars
			$stars = floor($popularity * 5);
			$starString = str_repeat("★", $stars) . str_repeat("☆", 5 - $stars);
				
			if($type == 'track') {
				$subtitle = $value->album->name . " by " . $value->artists[0]->name;
			} elseif($type == 'album') {
				$subtitle = "Album by " . $value->artists[0]->name;
			} else {
				$subtitle = ucfirst($type);
			}
			
			$subtitle = "$starString $subtitle";
						
			$currentResult[uid] = "bs-spotify-$query-$type";
			$currentResult[arg] = ($type == 'track') ? 'open location "' . $value->href . '"' : 'activate (open location "' . $value->href . '")';
			$currentResult[valid] = ($type == 'track') ? 'yes' : 'no';
			$currentResult[autocomplete] = $value->href . " ► $rawQuery ►"; //todo replace `rawQuery`
			$currentResult[title] = $value->name;
			$currentResult[subtitle] = $subtitle;
			$currentResult[popularity] = $popularity;
			if($show_images)
				$currentResult[icon] = getTrackArtwork($value->href);
			
			$results[] = $currentResult;
			
			$currentResultNumber++;
		}
	}
	
	if(!empty($results))
		usort($results, "popularitySort");
		
		$results[0][subtitle]        = mb_detect_encoding($rawQuery) . mb_detect_encoding("►"); //todo debug
}

alfredify($results);

?>