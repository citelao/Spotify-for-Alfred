# Spotifious #
## a natural Spotify controller for Alfred ##

![The magical interface](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/hero%20shot.png)

So, you've heard of **Spotifious**, eh? *A natural Spotify controller for
Alfred*? Searchs Spotify, controls your music, generally rocks?

It's built off a lot of other people's work—
[PHPFunk](https://github.com/phpfunk/alfred-spotify-controls) and
[David Ferguson](http://jdfwarrior.tumblr.com/) especially— and tries to match
the functionality of Alfred's integrated iTunes player. It's not perfect, but it does the job. And I think it's pretty cool.

It uses a slightly modified [Entypo](http://www.entypo.com/) icon font face for icons.

## Features ##

A quick rundown of its coolness:

### A controller, not a workflow ###

Spotifious just *feels* fun to use, like a real plugin for Alfred, not a workflow. It
can do in-depth, album-scouring searches and gives you useful information.
Not unlike the iTunes Mini Player.

### Alfred-like ###

Spotifious is also especially Alfred-like. Just start typing to scour Spotify's
servers for Music. Browse through artists and albums like nobody's business using an intelligent menu system that leaves you in control.

### Smart ###

Spotifious implements auto-updater Alleyoop to make sure it's always up-to-date.
It gives you a heads-up about what's going on with Spotify *the moment you
start it*. It also got a perfect score on its SATs.

## Download & Install ##

Current version: v0.8

1. [Download](https://github.com/citelao/Spotify-for-Alfred/archive/master.zip)
this repository.
2. Open `spotifious.alfredworkflow` by double-clicking it or dragging it into
Alfred.
3. Double-click the thingy marked `Hotkey` (fig. i1).
4. Click the textfield labeled `Hotkey` and press `^⌘⏎`.
5. Click `Save` to store the binding (fig. i2).
6. Continue on with your merry day.

![fig. i1: Double-click this.](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/fig%20i1%20hotkey.png)

![fig. i2: The Resulting Field](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/fig%20i2%20hotkey.png)

## How to Use ##

So let's assume you've downloaded and installed the workflow. Now what?

1. Press `^⌘⏎`.

Good! You should briefly see a loading entry, then the main menu:

![Loading...](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/loading.png)
![Main Menu](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/main%20menu.png)

You can action the song title (press `⏎`) to play or pause the song, action 
the album or artist to search for that album or artist, or just start typing to 
search for cool music.

![Searching for good music](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/searching.png)

Once you've searched for something, you can continue to browse albums and arists through Spotifious. Actioning an artist will bring up a list of their albums, and actioning an album will bring up the track list.

**Note:** You can always leave a menu and go back just by pressing `⌫`.

![Album list](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/artist%20menu.png)
![Track list](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/album%20menu.png)

## Configuration ##

### Show artwork ###

1. Open Alfred preferences and select `Spotifious` under `Workflows`.
2. Double-click the top-most icon with a Spotify icon, labeled `spot` (fig. c1).
3. In the textarea labeled `Script:`, change `SHOWIMAGES="no"` to `SHOWIMAGES="yes"` (fig. c2).
4. Click `Save`.
5. Boo-yah!

![fig. c1 The thingy called `spot`](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/fig%20c1%20images.png)

![fig. c2 The change as entered](https://dl.dropboxusercontent.com/u/3770885/Spotifious%20Stuff/fig%20c2%20images.png)

## TODO ##

- Allow `⎇`, `^`, and `⌘` to function as modifiers (Open in Spotify, other things?).
- Prevent Last.FM redundant artist downloads.
- Provide settings menu in-app.
- Make main menu links go directly to menus.

## Changelog ##

- v0.8: Added new icons; set them as default
- v0.7: General clean-up; added artist- and album-specific menus
- v0.6: Added Alleyoop support
- v0.5: Added a menu system
- v0.1: Inital search system

<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png" /></a>