var spot   = getSpotifyApi(1);
var models = spot.require("sp://import/scripts/api/models");

models.application.observe(models.EVENT.ARGUMENTSCHANGED, handleArgs);
models.application.observe(models.EVENT.CHANGE, handleArgs);

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