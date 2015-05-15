<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
require 'vendor/autoload.php';

$alfred = new OhAlfred();
$session = new SpotifyWebAPI\Session($alfred->options('spotify_client_id'), $alfred->options('spotify_secret'), 'http://localhost:11114/callback.php');

// Request a access token using the code from Spotify
$session->requestToken($_GET['code']);

// Save the tokens
$alfred->options("spotify_access_token", $session->getAccessToken());
$alfred->options("spotify_refresh_token", $session->getRefreshToken());
$alfred->options("spotify_access_token_expires", time() + $session->getExpires());

?>
<html>
<head>
	<title>Spotifious Setup</title>

	<link rel="stylesheet" href="include/setup/style/normalize.css" />
	<link rel="stylesheet" href="include/setup/style/style.css">
</head>

<body>
	<div id="wrapper" class="wrapper">
		<section>
			<h1>Spotifious should be setup :)</h1>
			<p>
				You should be able to start using Spotifious now!
			</p>

			<p>(enjoy)</p>
		</section>
	</div>
</body>