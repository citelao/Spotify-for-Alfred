<?php
namespace Spotifious\Menus;

class Helper {
	public static function floatToBars($float, $max = 10) {
		$line = ($float < 100) ? floor($float / 100 * $max) : $max;
		return str_repeat("[]", $line) . str_repeat("-", $max - $line);
	}
}