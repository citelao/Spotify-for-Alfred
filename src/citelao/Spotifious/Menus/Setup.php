<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Setup implements Menu {

	protected $alfred;
	protected $countryCodeConfigured;

	public function __construct($query) {
		$this->alfred = new OhAlfred();

		$this->countryCodeConfigured = !($this->alfred->options('country') == '');
		$this->applicationCreated = !($this->alfred->options('spotify_client_id') == '' || $this->alfred->options('spotify_secret') == '');
		$this->applicationLinked = !($this->alfred->options('spotify_access_token') == '' || $this->alfred->options('spotify_refresh_token') == '' || $this->alfred->options('spotify_access_token_expires') == '');
	}

	public function output() {
		$results[] = array(
			'title' => 'Welcome to Spotifious!',
			'subtitle' => 'You need to configure a few more things before you can use Spotifious.',
			'icon' => 'include/images/configuration.png',
			'valid' => 'no'
		);

		$results[] = array(
			'title' => '1. Set your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => $this->countryCodeConfigured ? 'include/images/checked.png' : 'include/images/unchecked.png',
			'autocomplete' => 'Country Code ⟩',
			'valid' => 'no'
		);

		$results[] = array(
			'title' => '2. Create a Spotify application',
			'subtitle' => 'Set up a Spotify application so you can search playlists!',
			'icon' => $this->applicationCreated ? 'include/images/checked.png' : 'include/images/unchecked.png',
			'arg' => 'appsetup⟩'
		);

		$results[] = array(
			'title' => '3. Link your Spotify application',
			'subtitle' => 'Connect your Spotify application to Spotifious to search your playlists.',
			'icon' => $this->applicationCreated ? $this->applicationLinked ? 'include/images/checked.png' : 'include/images/unchecked.png' : 'include/images/disabled.png',
			'arg' => 'applink⟩',
			'valid' => $this->applicationCreated ? 'yes' : 'no'
		);

		$results[] = array(
			'title' => 'You can access settings easily.',
			'subtitle' => 'Type `s` from the main menu.',
			'icon' => 'include/images/info.png',
			'valid' => 'no'
		);

		return $results;
	}
}