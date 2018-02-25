<?php
// Check the version string to ensure we've updated it everywhere!
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');
ini_set("display_errors", "stderr");

use OhAlfred\OhAlfred;
require 'vendor/autoload.php';

$alfred = new OhAlfred(false /* catch_errors */);

// Check CHANGELOG
$changelog_filename = $alfred->workflow() . "CHANGELOG.md";
$changelog = file_get_contents($changelog_filename);
$count = preg_match('/##\W*v?([\d.]*)/', $changelog, $matches);
$changelog_version = $matches[1];

// Check `info.plist`
$plist_version = $alfred->plist($alfred->workflow() . "info", "version");

// Check README
$readme_filename = $alfred->workflow() . "README.md";
$readme = file_get_contents($readme_filename);
$count = preg_match_all('/(\d+(?:\.\d+){2})/', $readme, $matches);
$potential_readme_version = $matches[0];

// Compare results
if($changelog_version != $plist_version || !in_array($changelog_version, $potential_readme_version)) {
	fwrite(STDERR, "Version mismatch! One or more versions are not in sync:\n");
	fwrite(STDERR, "\t" . $changelog_version . " - CHANGELOG (expected version)\n");
	fwrite(STDERR, "\t" . $plist_version . " - plist latest version\n");
	foreach ($potential_readme_version as $ver) {
		fwrite(STDERR, "\t" . $ver . " - potential README version\n");
	}
	exit(1);
}
