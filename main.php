<?php
include_once('include/globals.php');
include_once('include/functions.php');

/*
 * How it should work:
 *
 * Ctrl-cmd-enter:
 *	|| Crooked Teeth
 		Plans by Death Cab for Cutie ⭑⭑⭑⭒⭒
 	Plans
 		More from this album
 	Death Cab for Cutie
 		More by this artist
 	Search for music
 		Search Spotify by artist, album, or track
 		
 * Ctrl-cmd-enter, some chars / "artist" "album" "track":
 	Search for artist '{query}'...
 		Narrow this search to artists
 	Search for album '{query}'...
 		Narrow this search to albums
 	Search for track '{query}'...
 		Narrow this search to tracks
 	Continue typing to search...
 		Search artists, albums, and tracks combined
 		
 * Ctrl-cmd-enter, type 3 letters:
 	Begin searching
 	
 	Action an artist: search for that artist
 	Action an album: search for that album
 	
 	Command action: open in Spotify
 	
 * spot artist ♩
 * spot album ♩
 * spot track ♩
 */

//if(strlen($query) == 0) { // TODO
if(strlen($query) < 3) {	

	$currentTrack = spotifyQuery("name of current track");
	$currentStatus = (spotifyQuery("player state") == 'playing') ? '►' : '❙❙';
	$currentAlbum = spotifyQuery("album of current track");
	$currentArtist = spotifyQuery("artist of current track");
	$currentArtistArtwork = getArtistArtwork($currentArtist);
	$currentURL = spotifyQuery("spotify url of current track");
	$currentArtwork = getTrackArtwork($currentURL);
	
	$results[0][title] = "$currentStatus $currentTrack";
	$results[0][subtitle] = "$currentAlbum by $currentArtist";
	$results[0][arg] = "playpause";
	
	$results[1][title] = "$currentAlbum";
	$results[1][subtitle] = "More from this album...";
	$results[1][autocomplete] = "$currentAlbum";
	$results[1][valid] = "no";
	$results[1][icon] = (!file_exists($currentArtwork)) ? 'icon.png' : $currentArtwork;
	
	$results[2][title] = $currentArtist;
	$results[2][subtitle] = "More by this artist...";
	$results[2][autocomplete] = "$currentArtist";
	$results[2][valid] = "no";
	$results[2][icon] = (!file_exists($currentArtistArtwork)) ? 'icon.png' : $currentArtistArtwork;
	
	$results[3][title] = "Search for music...";
	$results[3][subtitle] = "Begin typing to search";
	$results[3][valid] = 'no';
// TODO
// } elseif(strlen($query) <= 3 && strlen($query) >= 1) {
// 	$results[0][title] = "Search for artists...";
// 	$results[0][subtitle] = "Narrow this search to artists";
// 	$results[0][valid] = 'no';
// 	
// 	$results[1][title] = "Search for albums...";
// 	$results[1][subtitle] = "Narrow this search to albums";
// 	$results[1][valid] = 'no';
// 	
// 	$results[2][title] = "Search for tracks...";
// 	$results[2][subtitle] = "Narrow this search to tracks";
// 	$results[2][valid] = 'no';
// 	
// 	$results[3][title] = "Continue typing to search all...";
// 	$results[3][subtitle] = "Search artists, albums, and tracks combined";
// 	$results[3][valid] = 'no';
} else {
	foreach (array('track','artist','album') as $type) {
		$json = fetch("http://ws.spotify.com/search/1/$type.json?q=" . str_replace("%3A", ":", urlencode($query)));
		
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
			$starString = str_repeat("⭑", $stars) . str_repeat("⭒", 5 - $stars);
				
			if($type == 'track') {
				$subtitle = $value->album->name . " by " . $value->artists[0]->name;
			} elseif($type == 'album') {
				$subtitle = "Album by " . $value->artists[0]->name;
			} else {
				$subtitle = ucfirst($type);
			}
			
			$subtitle = "$starString $subtitle";
						
			$currentResult[uid] = "bs-spotify-$query-$type";
			$currentResult[arg] = 'activate (open location "' . $value->href . '")';
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
}

alfredify($results);

?>