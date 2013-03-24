<?php

function alfredify($results) {
	print "<?xml version='1.0'?>\r\n<items>";
	
	foreach($results as $result) {
		if(!$result[uid])
			$result[uid] = 'null';
		
		if(!$result[arg])
			$result[arg] = 'null';
		
		if(!$result[title])
			$result[title] = 'null';
			
		if(!$result[subtitle])
			$result[subtitle] = 'null';
			
		if(!$result[icon])
			$result[icon] = 'icon.png';
		
		print "\r\n\r\n";
		print "	<item uid='$result[uid]' arg='$result[arg]'>\r\n";
		print "		<title>$result[title]</title>\r\n";
		print "		<subtitle>$result[subtitle]</subtitle>\r\n";
		print "		<icon>$result[icon]</icon>\r\n";
		print "	</item>\r\n";
	}
	
	print "</items>";
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

function getTrackArtwork($type, $id)
{
	$html = fetch("http://open.spotify.com/$type/$id");
	
	if (!empty($html)) {
	 	preg_match_all('/.*?og:image.*?content="(.*?)">.*?/is', $html, $m);
	 	return (isset($m[1][0])) ? $m[1][0] : 0;
	}
	
	return 0;
}

?>