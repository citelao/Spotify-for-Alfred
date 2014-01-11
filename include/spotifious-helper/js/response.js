// TODO rewrite with v1.0 API

var spot   = getSpotifyApi(1);
var models = spot.require("sp://import/scripts/api/models");

models.application.observe(models.EVENT.ARGUMENTSCHANGED, handleArgs);
models.application.observe(models.EVENT.CHANGE, handleArgs);

function handleSocket(port) {
	try {
		socket = new ReconnectingWebSocket('ws://localhost:' + port);
		console.log('Created WebSocket - status '+ socket.readyState);

		socket.onopen    = function(msg){
			console.log("Connection opened; awaiting notification of what's needed"); 
		};

		socket.onmessage = function(msg){ 
			console.log("Received: " + msg.data);

			if(msg.data == "close") {
				console.log("Closing per server order!");
				socket.close();
			}

			if (msg.data !== "Got your message!\n") {
				console.log("Responding with requested data");
				socket.send("Here you go, just what you wanted.");
			}
		};

		socket.onclose   = function(msg){
			console.log("Disconnected - status "+this.readyState); 
		};
	}
	catch(ex) {
		console.log(ex);
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
		case "socket":
			console.log("Request to socket Spotify data (port " + args[1] + ") received.");
			handleSocket(args[1]);
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