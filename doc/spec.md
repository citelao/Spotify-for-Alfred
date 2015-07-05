# How Spotifious should work #

There are four specific types of output:

1. Main Menu — first activation
4. Generic Menu — list of options, used for settings menu.
2. Search Menu — user typing
3. Detail Menu — autocompleted text with Spotify URL

## Main Menu ##

`^⌘⏎` should show the main menu.

It should check for updates every so often and display an update bar occasionally.

1. ► Crooked Teeth ⏎ `playpause`
	- ???
2. Plans ⏎ album detail
	- More from this album...
3. Death Cab  for Cutie ⏎ artist detail
	- More from this artist...
4. Search for music...
	- Begin typing to search

## Generic Menu ##

We use this for the settings & setup menus.

### Setup menu ###

These are the options required for initial operation.

- Country Code
	- should trigger a list of countries.
- Create a Spotify application
	- should trigger the application-creation web server.
- Link Spotify application
	- should open the Spotify login page for the new app.

### Settings Menu ###

These are all the original setup options, plus some additional ones.

- Track Notifications
	- toggle notifications of next playing tracks

## Search Menu ##

After activation, any typing (3+ chars should work) should show the search menu, unless the query requires a detail menu (see below). The results should be weighted so artists>albums>songs— but only if the query is completely contained by the result. `Lady Danv` should return Lady Danville, then Lady Danville EP, then songs. Otherwise sort by popularity; use a unique id for each query so popular searches follow Alfred's smart order.

If the query starts with a `c`, include control items, like play, pause, shuffle, etc.

1. Lady Danville ⏎ artist detail
	- ★★★★☆ Artist
2. Lady Danville EP ⏎ album detail
	- ★★★☆☆ Album by Lady Danville
3. Love to Love ⏎ `open location &lt;song>`
	- ★★☆☆☆ Lady Danville EP by Lady Danville

## Detail Menu ##

I want to maintain easily navigable menus & submenus, but need to provide the Spotify URL in order to perform an artist or album lookup. To that end, the detail menu uses a smart syntax, separated by some unicode glyphs (`►` & `⟩` maybe).

The query should have all URLs in hierarchical order, than all the queries in hierarchical order, followed by a closing separator:

`a menu url⟩a submenu url⟩another submenu url►query used in menu⟩query used in submenu⟩query used in final submenu⟩`

The different separator should be a sign that the query is parsable. If it isn't present, ignore the query.

This syntax will not be compatible with the old syntax.

### Playlist Detail ###

1. Liked from Radio ⏎ view in Spotify
	- View playlist in Spotify
2. Sticking it to Myself ⏎ `open location &lt;song>`
	- Jonathan Coulton - Artificial Heart 2:20 ★★★☆☆ 

### Artist Detail ###

1. Jonathan Coulton ⏎ view in Spotify
	- View artist in Spotify
2. One Christmas at a Time ⏎ album detail
	- Open this album...
3. ...

### Album Detail ###

1. Artificial Heart ⏎ view in Spotify
	- View album in Spotify
2. 1. Sticking it to Myself ⏎ `open location &lt;song>`
	- Jonathan Coulton 2:20 ★★★☆☆
3. ...