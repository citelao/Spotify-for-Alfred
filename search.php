<?php 

$query = $argv[1];
$safeQuery = urlencode($query);

$maxResults = 6;
$results = array();

if (strlen($query) < 3)
	exit(1);

foreach (array('track','artist','album') as $type) {
	$fetchUrl = "http://ws.spotify.com/search/1/$type.json?q=$safeQuery";
	
	// Thanks Jeff Johns <http://phpfunk.me/>
	$ch = curl_init($fetchUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	$page    = curl_exec($ch);
	$info    = curl_getinfo($ch);
	curl_close($ch);
	
	$json = ($info['http_code'] == '200') ? $page : null;
	
	if(empty($json))
		continue;
	
	$json = json_decode($json);
	
	$currentResultNumber = 1;
	foreach ($json->{$type . "s"} as $key => $value) {
		if($currentResultNumber > $maxResults / 3)
			continue;
		
		$currentResult[type] = $type;
		$currentResult[href] = $value->href;
		$currentResult[title] = $value->name;
		$currentResult[subtitle] = strtoupper($type);
		$currentResult[autocomplete] = $name;
		
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
	<icon>icon.png</icon>
	<autocomplete>$result[autocomplete]</autocomplete>
	 </item>
	 ";
}
	
print "</items>";

?>