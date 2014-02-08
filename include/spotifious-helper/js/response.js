require([
        '$api/models',
        'js/reconnecting-websocket'
        ], function(models, sockets) {

    models.application.load('arguments').done(handleArgs);
    models.application.addEventListener('arguments', handleArgs);

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
		case "debug":
			// console.log(models);
			var test = models.player;
			console.log(test);
			break;
		case "null":
			break;
		default:
			console.warn("Could not understand arguments");
			break;
	}
	
	models.application.openURI('spotify:app:spotifious:null');
}

function handleSocket(port) {
	// Using Joe Walnes' ReconnectingWebSocket with a modification
	// to the close method. 
	// <https://github.com/joewalnes/reconnecting-websocket>
	// <https://github.com/joewalnes/reconnecting-websocket/pull/8>
	socket = new sockets.ReconnectingWebSocket('ws://localhost:' + port);
	console.log('Created WebSocket - status '+ socket.readyState);

	query = '';

	socket.onopen = function(msg){
		console.log("Connection opened; awaiting notification of what's needed"); 
	};

	socket.onmessage = function(msg){ 
		console.log("Received: " + msg.data);

		if(msg.data == "close") {
			console.log("Closing per server order!");
			socket.close();
		}

		query = msg.data;

		handleSocketQuery();
	};

	socket.onclose = function(msg){
		console.log("Disconnected - status " + this.readyState); 
	};

	this.handleSocketQuery = function() {
		switch(query) {
			case "current_track_id":
				models.player.load('track').done(function(p) {
						console.log(p.track);
						socketRespond(p.track.uri);
				}).fail(function() {
					console.warn("Load current track failed.");
					socketRespond("⚠ No track.");
				});
				break;

			case "now":
				models.player.load('track').done(function(p) {
						console.log(p);

						models.Album.fromURI(p.track.album.uri).load('name').done(function(album) {
							var response = p.track.name + "✂"
							+ album.name + "✂" 
							// + p.track.artist.name + "✂"
							+ p.track.uri + "✂"
							+ p.track.album.uri + "✂"
							+ p.track.starred;
							
							socketRespond(response);
						}).fail(function() {
							console.warn("Load current album failed.");
							socketRespond("⚠ No album.");
						});
				}).fail(function() {
					console.warn("Load current track failed.");
					socketRespond("⚠ No track.");
				});
				break;

			default:
				console.warn("Could not understand socket request.");
				socketRespond("⚠ Could not understand socket request '" + query + "'");
		}
	};

	this.socketRespond = function(response) {
		console.log("Responding to requested data (" + query + ") with '" + response + "'");
		socket.send(response);
	};
}

// http://stackoverflow.com/questions/8623693/add-a-song-to-the-current-play-queue-in-a-spotify-app
function queue(tracks, index) {
	 // var pl = new models.Playlist('Alfred Playlist');
	 console.warn("unimplemented");
}

function toggleStarred() {
	// var track = models.player.track;

	// if (track == null) {
	// 	return;
	// }

	// track.starred = !track.starred; 	
	console.warn("unimplemented");
	console.log("Starred: " + track.starred);
}

console.log("Loaded!");
});