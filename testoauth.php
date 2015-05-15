<?php

require 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session('8c988382603f43d982cb679d3c7bfe00', '7ebb55b36eae42199721981d6a60062e', 'http://localhfost:11114/callbadck.php');

$scopes = array(
    'playlist-read-private',
    'user-read-private'
);

try {
	$session->requestCredentialsToken($scopes);
} catch(SpotifyWebAPI\SpotifyWebAPIException $e) {
	print 'hi' . $e->getMessage();
}

$authorizeUrl = $session->getAuthorizeUrl(array(
    'scope' => $scopes
));

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $authorizeUrl);
$contents = curl_exec ($ch);


echo '<a href="' . $authorizeUrl . '">here</a>';
die();