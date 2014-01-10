<?php
namespace Spotifious;
use OhAlfred\ApplicationApplescript;

class Helper {
	public function beautifyTime($seconds) {
		$m = floor($seconds / 60);
		$s = $seconds % 60;
		$s = ($s < 10) ? "0$s" : "$s";
		return  "$m:$s";
	}

	public function hotkeysConfigured() {
		// Check .plist for binds on `spot`
		// TODO
		return true;
	}

	public function helperAppConfigured() {
		global $alfred;

		if(is_link($alfred->home() . "/Spotify/spotifious-helper") || file_exists($alfred->home() . "/Spotify/spotifious-helper"))
			return true;

		return false;
	}

	public function countryCodeConfigured() {
		// Check file storage location for country code.
		global $alfred;

		return $alfred->options('country');
	}

	public function configured() {
		if (!Helper::hotkeysConfigured() || !Helper::helperAppConfigured() || !Helper::countryCodeConfigured())
			return false;

		return true;
	}

	public function countrySort($a, $b) {
		// Give priority to common countries.
		// Arbitrarily decided by me.
		$common = array(
			"United States", // America first hehehe
			"United Kingdom",
			"Canada",
			"Australia"
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

	public function floatToBars($decimal) {
		$line = ($decimal < 1) ? floor($decimal * 12) : 12;
		return str_repeat("𝗹", $line) . str_repeat("𝗅", 12 - $line);
	}

	public function now() {
		$spotQuery = new ApplicationApplescript('Spotify', 'return name of current track & "✂" & album of current track & "✂" & artist of current track & "✂" & spotify url of current track & "✂" & player state');

		$data = $spotQuery->run();
		
		return split("✂", $data);
	}

	public function popularitySort($a, $b) {
		if($a['popularity'] == $b['popularity'])
			return 0;
			
		return ($a['popularity'] < $b['popularity']) ? 1 : -1;
	}
}