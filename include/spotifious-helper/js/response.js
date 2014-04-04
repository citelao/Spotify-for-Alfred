require([
        '$api/models',
        '$api/library#Library',
        'js/reconnecting-websocket'
        ], function(models, Library, sockets) {

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
			queue([models.Track.fromURI(args[1] + ":" + args[2] + ":" + args[3])]);
			break;
		case "star":
			console.log("Toggling starredness!");

			if(args[2] != 'track') {
				console.warn("Can't star things that aren't tracks!");
			}

			toggleStarred(args[1] + ":" + args[2] + ":" + args[3]);
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
		case 'return':
			// TODO should return to previous user page
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

		query = msg.data.split("ഽ");

		handleSocketQuery();
	};

	socket.onclose = function(msg){
		console.log("Disconnected - status " + this.readyState);
	};

	this.handleSocketQuery = function() {
		switch(query[0]) {
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
						p.track.album.load('name').done(function(album) {
							// TODO multiple artists
							// models.Artist.fromURI(p.track.artists.uri)

							// TODO make work for ads.

							var response = p.track.name + "✂" +
							album.name + "✂" +
							p.track.artists[0].name + "✂" +
							p.track.uri + "✂" +
							p.track.album.uri + "✂" +
							p.track.artists[0].uri + "✂" +
							p.track.starred + "✂" + 
							models.player.playing;

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

			case "star":
				if(query[1].indexOf("track") == -1) {
					socketRespond("Can't star things that aren't tracks!");
					break;
				}

				var track = models.Track.fromURI(query[1]);

				track.load("starred", "name").done(function(track) {
					if(!track.starred) {
						Library.forCurrentUser().star(track);
						console.log("Starring.");
						socketRespond("Starred " + track.name);
					} else {
						Library.forCurrentUser().unstar(track);
						console.log("Unstarring.");
						socketRespond("Unstarred " + track.name);
					}
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
// TODO stop reset of current track when switching from non-alfred queue.
// TODO make work even once user changes playing song.
// probably add everything to alfred playlist.
// TODO add feedback.
function queue(tracks) {
	Library.forCurrentUser().playlists.snapshot().done(function(snapshot) {
		var playlistExists = false;
		for (var i = 0; i < snapshot.length; i++) {
			console.log(snapshot.get(i).name);

			if(snapshot.get(i).name == "Alfred Playlist") {
				add(snapshot.get(i));
				playlistExists = true;
				break;
			}
		};

		if(playlistExists == false) {
			models.Playlist.create("Alfred Playlist").done(function(playlist) { add(playlist); });
		}
	});

	function add(playlist) {
		playlist.load('tracks').done(function(playlist) {
			console.log(tracks);

			models.player.load('context').done(function(loadedplayer) {
				if(loadedplayer.context.uri == playlist.uri) {
					models.Playlist.fromURI(playlist.uri).load('tracks').done(function(loadedPlaylist) {
						loadedPlaylist.tracks.add(tracks);
					});
				} else {
					playlist.tracks.add(tracks);
					models.player.playContext(playlist);
				}

			});
		});
	}
}

console.log("Loaded!");
});