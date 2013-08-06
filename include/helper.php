<?php

function trim_value(&$value) 
{ 
	$value = trim($value); 
}

function alfredify($results) {
	print "<?xml version='1.0'?>\r\n<items>";
	
	foreach($results as $result) {
		if(!$result[uid])
			$result[uid] = 'null';
		
		if(!$result[arg])
			$result[arg] = 'null';
		
		if(!$result[title])
			$result[title] = 'null';
			
		if(!$result[icon])
			$result[icon] = 'icon.png';
		
		if(!$result[valid])
			$result[valid] = 'yes';
			
		print "\r\n\r\n";
		print "	<item uid='" . escapeQuery($result[uid]) . "' arg='" . $result[arg] . "' valid='" . escapeQuery($result[valid]) . "' autocomplete='" . escapeQuery($result[autocomplete]) . "'>\r\n";
		print "		<title>" . escapeQuery($result[title]) . "</title>\r\n";
		print "		<subtitle>" . escapeQuery($result[subtitle]) . "</subtitle>\r\n";
		print "		<icon>" . escapeQuery($result[icon]) . "</icon>\r\n";
		print "	</item>\r\n";
	}
	
	print "</items>";
}

function debug($text) {
	$results[0][title] = $text;

	alfredify($results);
}

function normalize($text) {
	return exec('./include/normalize "' . $text . '"');
}

function escapeQuery($text) {
	$text = str_replace("'", "’", $text);
	$text = str_replace('"', '\"', $text);
	$text = str_replace("&", "&amp;", $text);
	
	return $text;
}

function spotifyQuery() {
	$args = func_get_args();
	
	$script = "osascript -e 'tell application \"Spotify\"'";
	
	for ($i = 0; $i < func_num_args(); $i++) {
		$script .= " -e '" . $args[$i] . "'";
	}
	
	$script .= " -e 'end tell'";
	
	return normalize(exec($script));
}

function now() {
	$data = spotifyQuery('return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state');
	
	return split("✂", $data);
}

function popularitySort($a, $b) {
	if($a[popularity] == $b[popularity])
		return 0;
		
	return ($a[popularity] < $b[popularity]) ? 1 : -1;
}

// Thanks Jeff Johns <http://phpfunk.me/> and Robin Enhorn <https://github.com/enhorn/>
function fetch($url)
{
	 $ch = curl_init($url);
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	 curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	 $page    = curl_exec($ch);
	 $info    = curl_getinfo($ch);
	 curl_close($ch);
	 return ($info['http_code'] == '200') ? $page : null;
}

function getTrackArtwork($spotifyURL) {
	$hrefs = explode(':', $spotifyURL);
	$currentArtwork = "artwork/$hrefs[2].png";
	
	if (!file_exists($currentArtwork)) {
		$artwork = getTrackArtworkURL($hrefs[1], $hrefs[2]);
		
		if (!empty($artwork)) {
			shell_exec('curl -s ' . $artwork . ' -o ' . $currentArtwork);
		}
	}
	
	return $currentArtwork;
}

function getArtistArtwork($artist) {
	$parsedArtist = urlencode($artist);
	$currentArtwork = "artwork/$parsedArtist.png";
	
	if (!file_exists($currentArtwork)) {
		$artwork = getArtistArtworkURL($artist);
		
		if (!empty($artwork)) {
			shell_exec('curl -s ' . $artwork . ' -o ' . $currentArtwork);
		}
	}
	
	return $currentArtwork;
}

function getTrackArtworkURL($type, $id)
{
	$html = fetch("http://open.spotify.com/$type/$id");
	
	if (!empty($html)) {
	 	preg_match_all('/.*?og:image.*?content="(.*?)">.*?/is', $html, $m);
	 	return (isset($m[1][0])) ? $m[1][0] : 0;
	}
	
	return 0;
}

function getArtistArtworkURL($artist) {
	$parsedArtist = urlencode($artist);
	
	$html = fetch("http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&api_key=49d58890a60114e8fdfc63cbcf75d6c5&artist=$parsedArtist&format=json");
	$json = json_decode($html, true);
	
	return $json[artist][image][1]['#text'];
}

function floatToStars($decimal) {
	$stars = ($decimal < 1) ? floor($decimal * 5) : 5;
	return str_repeat("★", $stars) . str_repeat("☆", 5 - $stars);
}

function beautifyTime($seconds) {
	$m = floor($seconds / 60);
	$s = $seconds % 60;
	$s = ($s < 10) ? "0$s" : "$s";
	return  "$m:$s";
}

?>