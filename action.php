<?php
mb_internal_encoding("UTF-8");
include_once 'include/helper.php';
include_once 'include/OhAlfred.php';

$alfred = new OhAlfred();

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
							play ⦔ track ⦔ context (optional)
							queue ⦔ track/album/artist
							preview ⦔ track ?
							open ⦔ url
							search ⦔ text
							star ⦔ track
							null ⦔ null
							…: growl error!
*/
							
// TODO error handler. AlfredableExceptions are meant for alfred display.

$args = array_map(array($alfred, 'normalize'), $argv);
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
		throw new AlfredableException("Unknown key '" . $args[0] . "'. 'none' is the code for no key.");
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
		spotifyQuery($command[1]);
		break;

	case 'open':
		spotifyQuery('activate (open location "' . $command[1] . '")');
		break; 
	
	case 'play':
		$query = 'play track "' . $command[1] . '"';

		// If there is a context to play the track in.
		if(isset($command[2]) && $command[2] != '')
			$query .= ' in context "' . $command[2] . '"';
		
		spotifyQuery($query);
		break;

	case 'star':
		if ($command[1] == 'current') {
			// TODO tell app spotify to set starred of current track to "starred" etc,etc
			// TODO notify
		} else {
			// TODO open location spotify:app:spotifious:star:id
			// TODO notify
		}
		break;

	case 'search':
		//this way doesn't change the search bar, which is very annoying.
		spotifyQuery('activate (open location "spotify:search:' . $command[1] . '")');
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

				// TODO notify to login.
				applescriptQuery('open location "https://developer.spotify.com/login/"');
				break;
			
			case 'hotkeys':
				// bind hotkeys
				// TODO I can read defaults for the specific bindable thingies. Sounds hard.
				break;

			case 'country':
				// write data
				// TODO notify better
				exec('open include/Notifier.app --args "Using country code ' . $command[2] . '✂✂"');
				$alfred->options('country', $command[2]);
				break;

			default:
				throw new AlfredableException("Unknown config step '" . $command[1] . "'");
				break;
		}
		break;

	default:
		throw new AlfredableException("Unknown action '" . $command[0] . "'.", $command);
		break;
}