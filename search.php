<?php 
include_once('include/functions.php');

$query = $argv[2];
$show_images = true; //($argv[1] == 'yes') ? true : false;
$thumbs_path = "artwork";
$maxResults = ($show_images) ? 6 : 15;
$results = array();

if (strlen($query) < 3)
	exit(1);

foreach (array('track','artist','album') as $type) {
	$json = fetch("http://ws.spotify.com/search/1/$type.json?q=" . rawurlencode($query));
	
	if(empty($json))
		continue;
	
	$json = json_decode($json);
	
	$currentResultNumber = 1;
	foreach ($json->{$type . "s"} as $key => $value) {
		if($currentResultNumber > $maxResults / 3)
			continue;
		
		if($type == 'track') {
			$subtitle = $value->album->name . " by " . $value->artists[0]->name;
		} elseif($type == 'album') {
			$subtitle = $value->artist->name;
		} else {
			$subtitle = ucfirst($type);
		}
		
		// Thanks Jeff Johns <http://phpfunk.me/> and Robin Enhorn <https://github.com/enhorn/>
		if ($show_images) {
			$hrefs = explode(':', $value->href);
			$track_id = $hrefs[2];
			$thumb_path = "$thumbs_path/$track_id.png";
		
			if (! file_exists($thumb_path)) {
				$artwork = getTrackArtwork($type, $track_id);
				if (! empty($artwork)) {
					shell_exec('curl -s ' . $artwork . ' -o ' . $thumb_path);
				}
			}
		
			$icon = (!file_exists($thumb_path)) ? 'icon.png' : $thumb_path;
		}
		
		$currentResult[type] = $type;
		$currentResult[href] = $value->href;
		$currentResult[title] = $value->name;
		$currentResult[subtitle] = $subtitle;
		$currentResult[icon] = ($show_images) ? $icon : 'icon.png';
		
		$results[] = $currentResult;
		
		$currentResultNumber++;
	}
}

if(empty($results))
	exit(1);

print "<?xml version='1.0'?><items>";

foreach($results as $result) {
	print "<item uid='$result[type]' arg='$result[href]'>
	<title>$result[title]</title>
	<subtitle>$result[subtitle]</subtitle>
	<icon>$result[icon]</icon>
	</item>
	 ";
}
	
print "</items>";

?>