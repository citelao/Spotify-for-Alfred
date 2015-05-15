<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Settings implements Menu {

	protected $alfred;

	protected $trackNotificationsEnabled;
	protected $countryCode;

	public function __construct($query) {
		$this->alfred = new OhAlfred();

		$this->trackNotificationsEnabled = ($this->alfred->options('track_notifications') == 'true');
		$this->countryCode = $this->alfred->options('country');
	}

	public function output() {
		$results[] = array(
			'title' => 'Settings',
			'subtitle' => 'Here you can configure any options you want.',
			'icon' => 'include/images/configuration.png',
			'valid' => 'no'
		);

		$results[] = array(
			'title' => 'Track Notifications',
			'subtitle' => "Check this if you'd like to enable track change notifications.",
			'icon' => $this->trackNotificationsEnabled ? 'include/images/checked.png' : 'include/images/unchecked.png',
			'arg' => 'togglenotifications⟩'
		);

		$results[] = array(
			'title' => 'Configure country code',
			'subtitle' => 'Currently set to "' . $this->countryCode . '."',
			'autocomplete' => 'Country Code ⟩',
			'valid' => 'no',
			'icon' => 'include/images/dash.png'
		);

		$results[] = array(
			'title' => 'Change my linked Spotify application',
			'subtitle' => 'If you want to link a new Spotify app or update key information. You will need to login again.',
			'arg' => 'appsetup⟩',
			'icon' => 'include/images/dash.png'
		);

		$results[] = array(
			'title' => 'Login again to my Spotify application',
			'subtitle' => 'If you want to login again, do it!',
			'arg' => 'applink⟩',
			'icon' => 'include/images/dash.png'
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