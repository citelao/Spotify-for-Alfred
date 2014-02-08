<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result/?p=10197
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
use OhAlfred\StatefulException; // TODO write error handler
use OhAlfred\Applescript;
use OhAlfred\ApplicationApplescript;
use Spotifious\Sockets\Fetcher;
require 'src/citelao/Spotifious/helper_functions.php'; // TODO be prettier
require 'vendor/autoload.php';

/* 

	Action.php must:

	run
		discrete applescript actions
			playpause
			next
			previous
		play
			play track # in context #
		queue
			open location spotify:app:spotifious:queue:#
		preview
			open location spotify:app:spotifious:preview:#
		search/open in
			activate (open location #)
		star
			open location spotify:app:spotifious:star:#

	output
		notification center (or growl if requested)

		("cannot star artists" "cannot star albums")	

	Method:

		php -f action.php -- key ⧙ default_action ⧙ cmd_action ⧙ shift_action ⧙ alt_action ⧙ ctrl_action
		php -f action.php --action ⦔ arg
							discrete ⦔ action
							open ⦔ url
							play ⦔ track ⦔ context (optional)
							star ⦔ track/current
							search ⦔ text
							queue ⦔ track/album/artist
							preview ⦔ track ?
							null ⦔ null
							config ⦔ helperapp/hotkeys/country
							…: growl error!
*/

// TODO HUD notification; needs code signing. 

$alfred = new OhAlfred();

// I don't know if I actually need this. Left commented just in case.
// $args = array_map(array($alfred, 'normalize'), $argv);
$args = $argv;
array_shift($args);

$actions = explode(" ⧙ ", implode(" ", $args));

switch ($args[0]) {
	case 'none':
		$action = $actions[1];
		break;

	case 'cmd':
		$action = $actions[2];
		break;

	case 'shift':
		$action = $actions[3];
		break;

	case 'alt':
		$action = $actions[4];
		break;	

	case "ctrl":
		$action = $actions[5];
		break;

	default:
		throw new StatefulException("Unknown key '" . $args[0] . "'. 'none' is the code for no key.");
		break;
}

$command = explode(" ⦔ ", $action);

// For debugging.
print_r($argv);	
print_r($actions);	
print_r($command);

// TODO write last command to debug log.
switch ($command[0]) {
	case 'discrete':
		$as = new ApplicationApplescript('Spotify', $command[1]);
		$as->run();
		break;

	case 'open':
		$as = new ApplicationApplescript('Spotify', 'activate (open location "' . $command[1] . '")');
		$as->run();
		break; 
	
	case 'play':
		$query = 'play track "' . $command[1] . '"';

		// If there is a context to play the track in.
		if(isset($command[2]) && $command[2] != '')
			$query .= ' in context "' . $command[2] . '"';
		
		$as = new ApplicationApplescript('Spotify', $query);
		$as->run();
		break;

	case 'star':
		// TODO ensure is track, otherwise notify 'no I'm not starring an entire artist'
		// TODO notify, use sockets to get starredness and name.
		$as = new ApplicationApplescript('Spotify', 'open location "spotify:app:spotifious:star:' . $command[1]);
		$as->run();
		break;

	case 'search':
		//this way doesn't change the search bar, which is very annoying.
		$as = new ApplicationApplescript('Spotify', 'activate (open location "spotify:search:' . $command[1] . '")');
		$as->run();
		break;

	case 'queue':
		$as = new ApplicationApplescript('Spotify', 'open location "spotify:app:spotifious:queue:' . $command[1]);
		$as->run();
		break;

	case 'null':
		// Execute nothing without throwing an error.
		break;

	case 'config':
		// Initial config steps!
		switch ($command[1]) {
			case 'helperapp':
				// symlink files
				$spotifyDir = $alfred->home() . "/Spotify";
				if(!is_dir($spotifyDir))
					mkdir($spotifyDir);

				symlink($alfred->workflow() . "/include/spotifious-helper", $spotifyDir . "/spotifious-helper");
				print_r($alfred->workflow() . "/include/spotifious-helper/");

				// TODO make cleaer.
				$alfred->notify('Log In to Spotify', 'This will turn your account into a developer account, an important part of using Spotifious.');
				$as = new Applescript('open location "https://developer.spotify.com/login/"');
				$as->run();
				break;
			
			case 'hotkeys':
				// bind hotkeys
				// TODO I can read defaults for the specific bindable thingies. Sounds hard.
				break;

			case 'country':
				// write data
				$alfred->notify('Country code configured!', 'Your code is ' . $command[2] . '. You can change this at any time by typing "s" in Spotifious.');
				$alfred->options('country', $command[2]);
				break;

			default:
				throw new StatefulException("Unknown config step '" . $command[1] . "'");
				break;
		}
		break;

	default:
		throw new StatefulException("Unknown action '" . $command[0] . "'.", $command);
		break;
}