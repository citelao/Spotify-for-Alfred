<?php
namespace Spotifious\Menus;

class Helper {
	public static function floatToBars($float, $max = 12) {
		$line = ($float < 1) ? floor($float * $max) : $max;
		return str_repeat("𝗹", $line) . str_repeat("𝗅", $max - $line);
	}
}