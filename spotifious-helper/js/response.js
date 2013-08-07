var spot   = getSpotifyApi(1);
var models = spot.require("sp://import/scripts/api/models");

models.application.observe(models.EVENT.ARGUMENTSCHANGED, handleArgs);
models.application.observe(models.EVENT.CHANGE, handleArgs);

function handleArgs() {
	var args = models.application.arguments;
	console.log(args);
	
	switch(args[0]) {
		case "nowplaying":
			console.log("hi");
			window.localStorage.setItem("test", "test 2");
			console.log(window.localStorage.getItem("test"));
			break;
		case "queue":
			break;
		case "star":
			break;
		default:
			console.error("Could not understand arguments");
			break;
	}
	
}

console.log("Loaded!");

console.log(models.EVENT);
handleArgs();