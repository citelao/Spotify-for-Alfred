# How Spotifious should work #

There are three specific types of output:

1. Main Menu — first activation
2. Search Menu — user typing
3. Detail Menu — autocompleted text with Spotify URL

## Main Menu ##

`^⌘⏎` should show the main menu.

1. ► Crooked Teeth ⏎ `playpause`
	- ???
2. Plans ⏎ album detail
	- More from this album...
3. Death Cab  for Cutie ⏎ artist detail
	- More from this artist...
4. Search for music...
	- Begin typing to search

## Search Menu ##

After activation, any typing at all should show the search menu, unless the query requires a detail menu (see below). The results should be weighted so artists>albums>songs— but only if the query is completely contained by the result. `Lady Danv` should return Lady Danville, then Lady Danville EP, then songs. Otherwise sort by popularity; use a unique id for each query so popular searches follow Alfred's smart order.

1. Lady Danville ⏎ artist detail
	- ★★★★☆ Artist
2. Lady Danville EP ⏎ album detail
	- ★★★☆☆ Album by Lady Danville
3. Love to Love ⏎ `open location &lt;song>`
	- ★★☆☆☆ Lady Danville EP by Lady Danville

## Detail Menu ##

I want to maintain easily navigable menus, but need to provide the Spotify URL in order to perform an artist or album lookup. To that end, the detail menu uses the syntax `spotify URL ► inital search request ►`. The script should use its smartness to detect a backspace (ie if the final triangle is missing) and provide a search menu with the initial search request. That way, a user can easily back up a level.

**Note**: Transferring from an artist detail menu to an album detail menu should add an additional spotify URL and an extra `►`; ie `artist URL ► album URL ► initial search request ►►`. 

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