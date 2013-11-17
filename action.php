<?php
mb_internal_encoding("UTF-8");
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

		php -f action.php -- "discrete" "action"
							"play" "track" "context (optional)"
							"queue" "track/album/artist"
							"preview" "track"
							"open" "url" 
							"search" "text"
							"star" "track"
							...: growl error!
*/

exec('open include/Notifier.app --args "{query}song title✂album by artist✂stars✂"');

print_r($argv);