<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
require '../../vendor/autoload.php';

$alfred = new OhAlfred();
$session = new SpotifyWebAPI\Session($alfred->options('spotify_client_id'), $alfred->options('spotify_secret'), 'http://localhost:11114/callback.php');

$scopes = explode(', ', $alfred->options('desired_scopes'));

$authorizeUrl = $session->getAuthorizeUrl(array(
    'scope' => $scopes
));

header('Location: ' . $authorizeUrl);
die();