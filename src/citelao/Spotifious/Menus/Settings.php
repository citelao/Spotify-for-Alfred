<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Helper;
use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Settings implements Menu {
	protected $alfred;
	protected $api;

	protected $trackNotificationsEnabled;
	protected $playlists_cache_date;
	protected $countryCode;
	protected $optedOut;

	public function __construct($query, $alfred, $api) {
		$this->alfred = $alfred;
		$this->api = $api;

		$this->trackNotificationsEnabled = ($this->alfred->options('track_notifications') == 'true');
		$this->playlists_cache_date = $this->alfred->options('playlists_cache_date');
		$this->countryCode = $this->alfred->options('country');
		$this->optedOut = ($this->alfred->options('spotify_app_opt_out') == 'true');
	}

	public function output() {
		$results[] = array(
			'title' => 'Settings',
			'subtitle' => 'Here you can configure any options you want.',
			'icon' => array('path' => 'include/images/configuration.png'),
			'valid' => false
		);

		$results[] = array(
			'title' => 'Track notifications',
			'subtitle' => "Check this if you'd like to enable track change notifications.",
			'icon' => array('path' => $this->trackNotificationsEnabled ? 'include/images/checked.png' : 'include/images/unchecked.png'),
			'arg' => 'togglenotifications⟩'
		);

		if($this->api) {
			$last_update = Helper::human_ago($this->playlists_cache_date);
			$results[] = array(
				'title' => 'Update playlists cache',
				'subtitle' => "Last updated $last_update. If your playlists are not appearing, try this.",
				'icon' => array('path' => 'include/images/dash.png'),
				'arg' => 'update_playlists_cache⟩'
			);
		}

		$results[] = array(
			'title' => 'Configure country code',
			'subtitle' => 'Currently set to "' . $this->countryCode . '."',
			'autocomplete' => 'Country Code ⟩',
			'valid' => false,
			'icon' => array('path' => 'include/images/dash.png')
		);

		$results[] = array(
			'title' => ($this->optedOut) ?
				'Link a Spotify app' :
				'Change my linked Spotify application',
			'subtitle' => ($this->optedOut) ?
				"Opt-in to using a Spotify app. The dark side's not so bad!" :
				'If you want to link a new Spotify app or update key information. You will need to login again.',
			'arg' => 'appsetup⟩',
			'icon' => array('path' => 'include/images/dash.png')
		);

		if(!$this->optedOut) {
			$results[] = array(
				'title' => 'Login again to my Spotify application',
				'subtitle' => 'If you want to login again, do it!',
				'arg' => 'applink⟩',
				'icon' => array('path' => 'include/images/dash.png')
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