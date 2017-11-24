<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Setup implements Menu {

	protected $alfred;
	protected $countryCodeConfigured;
	protected $applicationCreated;
	protected $applicationLinked;
	protected $applicationPreviouslyLinked;
	protected $hasInstalledSpotify;

	public function __construct($query, $alfred, $api=null) {
		$this->alfred = $alfred;

		$this->wasOptOut = $this->alfred->options('spotify_app_opt_out') == 'true';
		$this->hasInstalledSpotify = $this->alfred->options('has_installed_spotify') == 'true';

		$this->countryCodeConfigured = !($this->alfred->options('country') == '');
		$this->applicationCreated = !($this->alfred->options('spotify_client_id') == '' || $this->alfred->options('spotify_secret') == '');
		$this->applicationLinked = !($this->alfred->options('spotify_access_token') == '' || 
								$this->alfred->options('spotify_refresh_token') == '' || 
								$this->alfred->options('spotify_access_token_expires') == '' ||
								$this->alfred->options('desired_scopes') != $this->alfred->options('registered_scopes'));

		$this->applicationPreviouslyLinked = !($this->alfred->options('spotify_access_token') == '');
	}

	public function output() {
		if($this->wasOptOut) {
			$results[] = array(
				'title' => 'Sorry, but the Spotify API changed.',
				'subtitle' => 'You must now create a Spotify application to use Spotifious.',
				'icon' => array('path' => 'include/images/configuration.png'),
				'valid' => false
			);
		} else {
			$results[] = array(
				'title' => 'Welcome to Spotifious!',
				'subtitle' => 'You need to configure a few more things before you can use Spotifious.',
				'icon' => array('path' => 'include/images/configuration.png'),
				'valid' => false
			);
		}

		$results[] = array(
			'title' => '1. Download & install Spotify',
			'subtitle' => 'Spotifious only works with the Spotify desktop app.',
			'icon' => array('path' => $this->hasInstalledSpotify ? 'include/images/checked.png' : 'include/images/unchecked.png'),
			'arg' => '{"action":"command", "options": {"command": "open https://www.spotify.com/us/download/mac/"}}',
		);

		$results[] = array(
			'title' => '2. Set your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => array('path' => $this->countryCodeConfigured ? 'include/images/checked.png' : 'include/images/unchecked.png'),
			'autocomplete' => 'Country Code ⟩',
			'valid' => false
		);

		$results[] = array(
			'title' => '3. Create a Spotify application',
			'subtitle' => 'Set up a Spotify application so you can search playlists!',
			'icon' => array('path' => $this->applicationCreated ? 'include/images/checked.png' : 'include/images/unchecked.png'),
			'arg' => 'appsetup⟩'
		);

		if($this->applicationPreviouslyLinked) {
			$results[] = array(
				'title' => '4. Relink your Spotify application',
				'subtitle' => "We've added new features to Spotifious, but you need to login again to use them.",
				'icon' => array('path' => $this->applicationCreated ? $this->applicationLinked ? 'include/images/checked.png' : 'include/images/unchecked.png' : 'include/images/disabled.png'),
				'arg' => 'applink⟩',
				'valid' => $this->applicationCreated ? true : false
			);

		} else {
			$results[] = array(
				'title' => '4. Link your Spotify application',
				'subtitle' => 'Connect your Spotify application to Spotifious to search your playlists.',
				'icon' => array('path' => $this->applicationCreated ? $this->applicationLinked ? 'include/images/checked.png' : 'include/images/unchecked.png' : 'include/images/disabled.png'),
				'arg' => 'applink⟩',
				'valid' => $this->applicationCreated ? true : false
			);
		}

		$results[] = array(
			'title' => 'You can access settings easily.',
			'subtitle' => 'Type `s` from the main menu.',
			'icon' => array('path' => 'include/images/info.png'),
			'valid' => false
		);

		return $results;
	}
}
