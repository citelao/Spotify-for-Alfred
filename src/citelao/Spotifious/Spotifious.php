<?php
namespace Spotifious;

use Spotifious\Menus\Main;
use Spotifious\Menus\Search;
use Spotifious\Menus\Detail;

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
			$splitQuery  = array_filter(str_replace("⟩", "", explode("⟩", $query)));
			               array_walk($splitQuery, array($this, 'trim_value'));

			$URIs = array_filter($splitQuery, array($this, 'is_spotify_uri'));
			$args = array_diff($splitQuery, $URIs);

			// Find which URI to use (by count, not by array index).
			// Arrows should be twice the number of URIs for the last URI.
			// For every one arrow fewer, traverse one URI backwards. 
			$arrows = mb_substr_count($query, "⟩");
			$depth = count($URIs) - (2 * count($URIs) - $arrows); // equiv to $arrows - count($URIs).

			$options = array(
				'depth'  => $depth,
				'URIs'   => $URIs,
				'args'   => $args,
				'search' => ''
			);

			if (mb_substr($query, -1) == "⟩") { // Machine-generated
				$menu = new Detail($options);
				return $menu->output();

			} elseif($depth > 0) {
				$search = array_pop($args);
				$options['search'] = $search;
				$options['args'] = $args;

				$menu = new Detail($options);
				return $menu->output();

			} else {
				$menu = new Search(end($args));
				return $menu->output();
			}

		} else {
			$menu = new Search($query);
			return $menu->output();

		}
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}

	protected function trim_value(&$value) { 
		$value = trim($value);
	}

	// TODO cite
	protected function is_spotify_uri($item) {
			$regex = '/^(spotify:(?:album|artist|track|user:[^:]+:playlist):[a-zA-Z0-9]+)$/x';

			return preg_match($regex, $item);
		}
}