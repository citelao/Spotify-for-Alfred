# Spotifious #
## a natural Spotify controller for Alfred ##

![The magical interface](http://f.cl.ly/items/0r1F1t1h1i2w1F2o100j/Screen%20Shot%202013-03-28%20at%201.06.32%20PM.png)

So, you've heard of **Spotifious**, eh? *A natural Spotify controller for
Alfred*? Searchs Spotify, controls your music, generally rocks?

It's built off a lot of other people's work— 
[PHPFunk](https://github.com/phpfunk/alfred-spotify-controls) and
[David Ferguson](http://jdfwarrior.tumblr.com/) especially— and tries to match
the functionality of Alfred's integrated iTunes player. It's not done, but it's
getting there. And I think it's pretty cool.

## Features ##

A quick rundown of its coolness:

### A controller, not a workflow ###

Spotifious just *feels* fun to use, like a real plugin for Alfred, not a workflow. It
can do in-depth, album-scouring searches and gives you useful information.
Not unlike the iTunes Mini Player

### Alfred-like ###

Spotifious is also especially Alfred-like. Just start typing to scour Spotify's
servers for Music. And it uses Last.FM *and* Spotify's servers to gather artwork.

### Smart ###

Spotifious implements auto-updater Alleyoop to make sure it's always up-to-date.
It gives you a heads-up about what's going on with Spotify *the moment you
start it*. It got a perfect score on its SATs.

## Download & Install ##

Current version: v0.7

1. [Download](https://github.com/citelao/Spotify-for-Alfred/archive/master.zip)
this repository.
2. Open `spotifious.alfredworkflow` by double-clicking it or dragging it into
Alfred.
3. Continue on with your merry day.

## How to Use ##

So let's assume you've downloaded and installed the workflow. Now what?

1. Press ^⌘⏎.

Good! You should briefly see a loading entry, then the main menu

![Loading...](http://f.cl.ly/items/000G2a0E3y0k3g2R311Y/Screen%20Shot%202013-03-28%20at%201.18.27%20PM.png)
![Main Menu](http://f.cl.ly/items/0y1l3O2E212O3E0N331Y/Screen%20Shot%202013-03-28%20at%201.20.12%20PM.png)

You can action the song title (press `enter`) to play or pause the song, action 
the album or artist to search for that album or artist, or just start typing to 
search for cool music.

![Searching](http://f.cl.ly/items/0e1R1M2U1T3N2w1d3n3F/Screen%20Shot%202013-03-28%20at%201.22.04%20PM.png)

**Note:** Give it a moment when you search; it uses Spotify's slow API.

## TODO ##

- Allow ⎇, ^, and ⌘ to function as modifiers (Open in Spotify, other things?).
- Prevent Last.FM redundant artist downloads.
- Create playing/paused icons instead of dumb ►/❙❙.
- Provide fallback album/artist art.

## Changelog ##

- v0.7: General clean-up; added artist- and album-specific menus
- v0.6: Added Alleyoop support
- v0.5: Added a menu system
- v0.1: Inital search system