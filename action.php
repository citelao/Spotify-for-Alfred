<?php
mb_internal_encoding("UTF-8");
include('include/helper.php');
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

		php -f action.php -- key default_action cmd_action shift_action alt_action ctrl_action
		php -f action.php -- "discrete" "action"
							"play" "track" "context (optional)"
							"queue" "track/album/artist"
							"preview" "track"
							"open" "url" 
							"search" "text"
							"star" "track"
							...: growl error!
*/

// exec('open include/Notifier.app --args "{query}song title✂album by artist✂stars✂"');

$args = array_map('normalize', $argv);

print_r($args);

// TODO
switch ($args[1]) {
	case "ctrl":
		$action = 'playpause';
		break;
	
	case 'alt':
		$action = $args[5];
		break;

	case 'shift':
		$action = $args[4];
		break;

	case 'cmd':
		$action = $args[3];
		break;

	default:
		$action = $args[2];
		break;
}

exec("osascript -e 'tell application \"Spotify\"' -e 'run script $action' -e 'end tell'");
