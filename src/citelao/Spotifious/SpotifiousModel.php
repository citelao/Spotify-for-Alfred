<?php
namespace Spotifious;

use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

use OhAlfred\OhAlfred;
use OhAlfred\Exceptions\StatefulException;

class SpotifiousModel {
	protected $alfred;

	public function __construct() {
		$this->alfred = new OhAlfred();
	}

	////
	// Helper functions

	public function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}

	// TODO cite
	public function isSpotifyUri($item) {
		$regex = '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x';

		return preg_match($regex, $item);
	}

	////
	// API

	public function isOptedOut() {
		return $this->alfred->options('spotify_app_opt_out') == 'true';
	}

	public function getSpotifyApi() {
		if($this->isOptedOut()) {
			throw new StatefulException("Trying to get API for opted out user. My bad.");
		}

		$api = new SpotifyWebAPI();

		// If the access token has expired :(
		if ($this->alfred->options('spotify_access_token_expires') < time()) {
			$session = new Session($this->alfred->options('spotify_client_id'), $this->alfred->options('spotify_secret'), 'http://localhost:11114/callback.php');
			$session->setRefreshToken($this->alfred->options('spotify_refresh_token'));
			$session->refreshToken();

			$this->alfred->options('spotify_access_token_expires', time() + $session->getExpires());
			$this->alfred->options('spotify_access_token', $session->getAccessToken());
		}

		$api->setAccessToken($this->alfred->options('spotify_access_token'));

		return $api;
	}
}