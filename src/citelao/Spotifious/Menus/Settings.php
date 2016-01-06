<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Settings implements Menu {
	protected $alfred;

	protected $trackNotificationsEnabled;
	protected $countryCode;
	protected $optedOut;

	public function __construct($query) {
		$this->alfred = new OhAlfred();

		$this->trackNotificationsEnabled = ($this->alfred->options('track_notifications') == 'true');
		$this->countryCode = $this->alfred->options('country');
		$this->optedOut = ($this->alfred->options('spotify_app_opt_out') == 'true');
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
			'title' => ($this->optedOut) ?
				'Link a Spotify app' :
				'Change my linked Spotify application',
			'subtitle' => ($this->optedOut) ?
				"Opt-in to using a Spotify app. The dark side's not so bad!" :
				'If you want to link a new Spotify app or update key information. You will need to login again.',
			'arg' => 'appsetup⟩',
			'icon' => 'include/images/dash.png'
		);

		if(!$this->optedOut) {
			$results[] = array(
				'title' => 'Login again to my Spotify application',
				'subtitle' => 'If you want to login again, do it!',
				'arg' => 'applink⟩',
				'icon' => 'include/images/dash.png'
			);
		}

		$results[] = array(
			'title' => 'You can access settings easily.',
			'subtitle' => 'Type `s` from the main menu.',
			'icon' => 'include/images/info.png',
			'valid' => 'no'
		);

		return $results;
	}
}