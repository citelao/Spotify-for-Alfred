<?php
namespace Spotifious;

use Spotifious\Menus\Main;

class Spotifious {
	public function run($query) {
		// Correct for old Spotifious queries
		$q = str_replace("►", "⟩", $query);

		if (mb_strlen($query) <= 3) {
			$menu = new Main($query);
			return $menu->output();
			
		} elseif (contains($query, '⟩')) {
			// if the query contains any machine-generated text 
			// (the unicode `⟩` is untypeable so we check for it)
			// we need to parse the query and extract the URLs.

			// So split based on the delimeter `⟩` and excise the delimeter and blanks.
		} else {
			// Search for things
			return $this->search($query);
		}
	}

	protected function main() {

	}

	protected function search($query) {

	}
}