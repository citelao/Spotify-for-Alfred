<?php

function trim_value(&$value) 
{ 
	$value = trim($value); 
}

function contains($stack, $needle) {
	return (strpos($stack, $needle) !== false);
}

function preg_contains($stack, $regex) {
	$matches = array();

	preg_match($regex, $stack, $matches);

	return (count($matches) == 0) ? false : $matches;
}

function alfredify($results) {
	print "<?xml version='1.0'?>\r\n<items>";
	
	foreach($results as $result) {
		if(!isset($result['arg']))
			$result['arg'] = 'null';
		
		if(!isset($result['title']))
			$result['title'] = 'null';
			
		if(!isset($result['icon']))
			$result['icon'] = 'icon.png';
		
		if(!isset($result['valid']))
			$result['valid'] = 'yes';

		if(!isset($result['uid']))
			$result['uid'] = time() . "-" . $result['title'];

		if(!isset($result['autocomplete']))
			$result['autocomplete'] = '';
			
		print "\r\n\r\n";
		print "	<item uid='" . escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . escapeQuery($result['valid']) . "' autocomplete='" . escapeQuery($result['autocomplete']) . "'>\r\n";
		print "		<title>" . escapeQuery($result['title']) . "</title>\r\n";
		print "		<subtitle>" . escapeQuery($result['subtitle']) . "</subtitle>\r\n";
		print "		<icon>" . escapeQuery($result['icon']) . "</icon>\r\n";
		print "	</item>\r\n";
	}
	
	print "</items>";
}

function errorify($error) {
	$titles = ['Aw, jeez!', 'Dagnabit!', 'Crud!', 'Whoops!', 'Oh, snap!', 'Aw, fiddlesticks!', 'Goram it!'];

	$results = [
		[
			'title' => $titles[array_rand($titles)],
			'subtitle' => "Something went haywire. You can continue using Spotifious.",
			'valid' => "no",
			'icon' => 'include/images/alfred/error.png'
		],

		[
			'title' => $error->getMessage(),
			'subtitle' => "Line " . $error->getLine() . ", " . $error->getFile(),
			'valid' => "no",
			'icon' => 'include/images/alfred/info.png'
		],

		[
			'title' => "Send output to file",
			'subtitle' => "Not implemented", // TODO
			'icon' => 'include/images/alfred/folder.png'
		]
	];

	alfredify($results);
	exit();
}

set_exception_handler('errorify');

function debug($text) {
	$results[0]['title'] = $text;

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

	 if($info['http_code'] != '200')
	 	throw new Exception("fetch() failed; error code: " . $info['http_code']);
	 	

	 return ($info['http_code'] == '200') ? $page : null;
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