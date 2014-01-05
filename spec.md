# How Spotifious v1.0 should work #

## Goals ##

Spotifious should be a powerful and fast way of controlling Spotify from Alfred, rather than directly in-app.

I should want to use Spotifious over Spotify to queue music I like.

## Overview ##

Spotifious can be used via its hotkeys or it's main interface. 

Hotkeys can play, pause, change tracks, or modify volume. They can also star & unstar tracks, and even toggle shuffling and looping (?). 

Spotifious's main selling point, though, is it's main interface, which functions like the iTunes controller — searching, starring, queuing, previewing, it does it all.

I considered also scripting a background service that notifies on song changes, but I'm hesitant to do that for overhead reasons.

## Hotkeys in depth ##

Spotifious should provide bindable hotkeys (and a nice tutorial on first load of the main interface on binding) for any action I regularly do, like playing, skipping, changing volume, etc. If it's possible to subset, maybe even liking radio songs can be possible.

So here's what it can do:

- Play*/Pause
- Previous Track*
- Next track*
- Volume Up (in increments of 10%)
- Volume Down (in increments of 10%)
- Star*/Unstar\*
- Toggle shuffle* (?)
- Toggle looping* (?)

*: displays notification. This can be an NC or Growl popup _and_ a HUD-style popover.

?: … maybe.

Due to Alfred's method of implementing extensions, all shortcuts are wiped on fresh install. Spotifious should account for this and provide a tutorial on binding if it notices it is unbound.

## Interface in depth ##

Spotifious's interface is quite powerful.

Upon launch, it gives a quick and informative overview of your current spotify status. It also allows quick and powerful music browsing— search artists, albums, and tracks (and playlists?) and queue, preview, star, or view them in Spotify.

--
users should be able to switch between growl and nc and nothing

"before you start using Spotifious, make sure you set this up…"
	"binding hotkeys"
	"installing the helper app"
	"configuring country"
	"how to access settings"