<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\OhAlfred;

class Setup implements Menu {

	protected $alfred;
	protected $userTriggered;
	protected $countryCodeConfigured;

	public function __construct($query) {
		$this->alfred = new OhAlfred();

		$this->userTriggered = ($query == "s" || $query == "S");

		$this->countryCodeConfigured = !($this->alfred->options('country') == '');
	}

	public function output() {
		if($this->userTriggered) {
			$results[] = array(
				'title' => 'Settings',
				'subtitle' => 'Here you can configure any options you want.',
				'icon' => 'include/images/configuration.png',
				'valid' => 'no'
			);
		} else {
			$results[] = array(
				'title' => 'Welcome to Spotifious!',
				'subtitle' => 'You need to configure a few more things before you can use Spotifious.',
				'icon' => 'include/images/configuration.png',
				'valid' => 'no'
			);
		}

		$results[] = array(
			'title' => '1. Set your country code',
			'subtitle' => 'Choosing the correct country code makes sure you can play songs you select.',
			'icon' => $this->countryCodeConfigured ? 'include/images/checked.png' : 'include/images/unchecked.png',
			'autocomplete' => 'Country Code ⟩',
			'valid' => 'no'
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