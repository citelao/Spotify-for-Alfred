<?php

require 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session('8c988382603f43d982cb679d3c7bfe00', '7ebb55b36eae42199721981d6a60062e', 'http://localhost:11111/testresponse.php');

$scopes = array(
    'playlist-read-private',
    'user-read-private'
);

$authorizeUrl = $session->getAuthorizeUrl(array(
    'scope' => $scopes
));

header('Location: ' . $authorizeUrl);
die();