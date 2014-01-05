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
		case "now":
			break;

		default:
			console.error("Could not understand how to fetch " + arg);
			break;
	}
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
			break;
		case "star":
			console.log("Toggling starredness!");
			toggleStarred();
			break;
		case "preview":
			break;
		default:
			console.error("Could not understand arguments");
			break;
	}
	
}

console.log("Loaded!");

console.log(models.EVENT);
handleArgs();