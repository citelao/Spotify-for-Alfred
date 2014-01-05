// TODO rewrite with v1.0 API

var spot   = getSpotifyApi(1);
var models = spot.require("sp://import/scripts/api/models");

models.application.observe(models.EVENT.ARGUMENTSCHANGED, handleArgs);
models.application.observe(models.EVENT.CHANGE, handleArgs);

function handleFetch(arg) {
	console.log("Request for " + arg);

	// Since socket clients are weird in JS, treat this as the client connecting
	// to the host PHP.
	try {
		socket = new WebSocket('ws://localhost:33334');
		log('WebSocket - status '+socket.readyState);
		socket.onopen    = function(msg){ log("Welcome - status "+this.readyState); };
		socket.onmessage = function(msg){ log("Received: "+msg.data); };
		socket.onclose   = function(msg){ log("Disconnected - status "+this.readyState); };
	}
	catch(ex) { log(ex); }

	switch(arg) {
		case "now": // TODO
		default:
			console.error("Could not understand how to fetch " + arg);
			break;
	}
}

// http://stackoverflow.com/questions/8623693/add-a-song-to-the-current-play-queue-in-a-spotify-app
function queue(tracks, index) {
	 var pl = new models.Playlist('Alfred Playlist');
}

function toggleStarred() {
	var track = models.player.track;

	if (track == null) {
		return;
	}

	track.starred = !track.starred; 	

	console.log("Starred: " + track.starred);
}

function handleArgs() {
	var args = models.application.arguments;
	console.log(args);
	
	switch(args[0]) {
		case "fetch":
			console.log("Request to fetch Spotify data received.");
			handleFetch(args[1]);
			break;
		case "queue":
			console.log("Queueing!");
			queue(args[1]);
			break;
		case "star":
			// TODO accept 'current' or an id.
			console.log("Toggling starredness!");
			toggleStarred();
			break;
		case "preview":
			break;
		case "null":
			break;
		default:
			console.error("Could not understand arguments");
			break;
	}
	
	// TODO use this somehow
	// models.application.openURI('spotify:app:spotifious:null');
}

console.log("Loaded!");

console.log(models.EVENT);
handleArgs();