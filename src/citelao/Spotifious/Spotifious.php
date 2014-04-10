<?php
namespace Spotifious;

use Spotifious\Menus\Main;
use Spotifious\Menus\Search;

class Spotifious {
	public function run($query) {
		// Correct for old Spotifious queries
		$q = str_replace("►", "⟩", $query);

		if (mb_strlen($query) <= 3) {
			$menu = new Main($query);
			return $menu->output();
			
		} elseif ($this->contains($query, '⟩')) {
			// if the query contains any machine-generated text 
			// (the unicode `⟩` is untypeable so we check for it)
			// we need to parse the query and extract the URLs.

			// So split based on the delimeter `⟩` and excise the delimeter and blanks.
			$URIs = array_filter($splitQuery, 'is_spotify_uri');
			$args = array_diff($splitQuery, $URIs);

			// Find which URI to use (by count, not by array index).
			// Arrows should be twice the number of URIs for the last URI.
			// For every one arrow fewer, traverse one URI backwards. 
			$arrows = mb_substr_count($query, "⟩");
			$depth = count($URIs) - (2 * count($URIs) - $arrows); // equiv to $arrows - count($URIs).

			if (mb_substr($query, -1) == "⟩") { // Machine-generated
				// $results = Menus::detail($URIs, $args, $depth, $alfred->options('country'));
			} elseif($depth > 0) {
				// $search = array_pop($args);
				// $results = Menus::detail($URIs, $args, $depth, $alfred->options('country'), $search);
			} else {
				// $results = Menus::search(end($args), $alfred->options('country'));
			}

		} else {
			$menu = new Search($query);
			return $menu->output();
		}
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}
}