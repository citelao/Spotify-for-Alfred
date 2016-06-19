<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\HTTP\JsonFetcher;

class SetupCountryCode implements Menu {
	protected $countries;
	protected $search;

	public function __construct($query) {
		// Thanks, Lukes <https://github.com/lukes>
		$url = "https://raw.githubusercontent.com/lukes/ISO-3166-Countries-with-Regional-Codes/master/all/all.json";

		$fetcher = new JsonFetcher($url);
		$json = $fetcher->run();

		$this->search = mb_substr($query, mb_strlen('Country Code ⟩'));

		foreach ($json as $key => $value) {
			$currentCountry = array(
				'name' => $value->name,
				'code' => $value->{'alpha-2'}
			);

			$this->countries[] = $currentCountry;
		}

		$this->countries[] = array(
				'name' => "I'd rather not give a country!",
				'code' => 'not-given'
			);
	}

	public function output() {
		$results = array();

		foreach($this->countries as $country) {
			if($this->search != null &&
				!@mb_stristr($country['name'] . ' ' . $country['code'], $this->search))
				continue;

			$currentResult = array(
				'title' => $country['name'],
				'subtitle' => "Set your country to “{$country['code']}.”",
				'arg' => 'country⟩' . $country['code'],
				'autocomplete' => 'Country Code ⟩' . $country['name'],
				'icon' => 'include/images/dash.png'
			);

			$results[] = $currentResult;
		}

		if($results == null) {
			$results[] = array(
				'title' => 'Could not find country',
				'subtitle' => 'We are looking for a country called "' . $this->search . '"',
				'autocomplete' => 'Country Code ⟩',
				'valid' => 'no'
			);
		} else {
			usort($results, array($this,'countrySort'));
		}

		return $results;
	}

	protected function countrySort($a, $b) {
		// Give priority to common countries.
		// Arbitrarily decided by me.
		$common = array(
			"United States of America", // America first hehehe
			"United Kingdom",
			"Canada",
			"Australia",
			"I'd rather not give a country!"
		);

		if(in_array($a['title'], $common)) {
			if (in_array($b['title'], $common)) {
				return  array_search($a['title'], $common) - array_search($b['title'], $common);
			} else {
				return -1;
			}
		}

		if(in_array($b['title'], $common))
			return 1;

		// If neither is common, do a simple alphabetical order.
		return strcmp($a['title'], $b['title']);
	}
}