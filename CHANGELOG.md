# Changelog #

## v0.13.9 ##
- Fixed: Deprecation error thrown in newer versions of PHP for unparenthesized
	left-associative ternary operator in `Spotifious.php`
- Changed: Updated `spotifious.sublime-project` configuration to use relative
	path of the workflow directory

## v0.13.8 ##
- Fixed: Support new Spotify playlist syntax (without `user:` prefix).

## v0.13.7 ##
- Fixed: Bump up the Spotify API version so Spotifious works again

## v0.13.6 ##
- Added: Version checker added to buildscript so I stop shipping incorrect versions numbers
- Fixed: Addressed a syntax error that prevented Spotify from returning properly

## v0.13.5 ##
- Added: Now checks for installed Spotify on startup
- Added: New hotkeys for playing albums and artists directly from search
- Changed: @philihp clarrified where build location is

## v0.13.4 ##
- Fixed: Compilation albums now have an icon
- Fixed: Can now add playlists with `\` in their titles
- Fixed: Removed light-colored edge of some icons

## v0.13.3.2 ##
- Fixed: Alfred variables are now more reliably passed to the workflow. Should
	fix several people's configuration bugs

## v0.13.3.1 ##
- Added: there's a small install guide inside the Workflow menu
- Fixed: Small playlists now appear correctly
- Fixed: Playlist cache correctly on first run (thanks, @chrsblck!)

## v0.13.3 ##
- Added: Alfred-native hotkey activation means a huge speedboost to hotkeys
- Added: Cache your playlists to add them to search
- Fixed: API search now uses Authentication all the time
- Changed: Do not source control vendor/
- Changed: Use a newer version of JWilsson's API.
- Removed: Opt-out does not work with the Spotify API anymore, so it's removed (thanks @atabbott!)

## v0.13.2.1 ##
- Fixed: Setup bug where Spotifious expects client ID before it exists

## v0.13.2 ##
- Added: Use JSON output when possible
- Added: Exceptions appear in debugger for actions
- Changed: Search uses API with auth
- Changed: Album pages use API with auth
- Changed: Album pages load faster
- Changed: Info loads much faster (but may break in edge cases)
- Changed: Popularity boxes look pretty now (thanks, danielma!)
- Changed: Build size is much smaller
- Fixed: Single-digit seconds are now prefixed with a 0
- Fixed: Add Homebrew to path if needed (thanks, mieubrisse!)
- Fixed: Setup in Alfred 3 works
- Fixed: The UK shows up at the top of country selection

## v0.13.1 ##
- Fixed: Alfred 3 compatibility
- Fixed: America appears first in country selection

## v0.13 ##
- Added: preliminary scaffolding for web API (playlists, starring, all of it is coming)
- Added: new settings menu.
- Added: you can now *disable* track notifications
- Fixed: track notifications say the right thing in Spotify 1.0
- Fixed: control panel stays open properly
- Fixed: weird looping volume changes

## v0.12 ##
- Added: compatiblity with Alfred Remote

## v0.11.2 ##
- Added: opt-out feature for location information
- Changed: better installation guide

## v0.11.1 ##
- Fixed: non-responsiveness after some requests to Spotify

## v0.11 ##
- Added: hotkeys, configurable via Alfred
- Added: notifications
- Added: controls menu (type `c`)
- Changed: using Spotify's faster web API!
- Changed: non-unicode popularity glyphs

## v0.10 ##
- Added: Settings menu (accessible using "s")
- Added: Location-based filtering of search results
- Changed: Actions route through `action.php` now, instead of direct Applescript execution.

## v0.9.4 ##
- Changed: more compatibility changes for PHP v5.3. These did not get included in v0.9.3.

## v0.9.3 ##
- Changed: more compatibility changes for PHP v5.3

## v0.9.2 ##
- Changed: compatibility changes for PHP v5.3

## v0.9.1 ##
- Changed: prevent breakage if no track playing.

## v0.9 ##
- Added: created changelog
- Added: error reporting
- Added: context-based searching
- Added: README in Alfred.
- Added: Packal support
- Changed: restructured to use Composer
- Changed: divided Alfred output code into `OhAlfred`
- Changed: uses more extensible menu system.
- Removed: Alleyoop support
- Removed: album art (as I never use it and it was becoming increasingly untested)

## v0.8 ##
- Added: new icons; set them as default

## v0.7 ##
- Added: artist- and album-specific menus
- Changed: general code clean-up

## v0.6 ##
- Added: Alleyoop support

## v0.5 ##
- Added: menu system

## v0.1 ##
- Added: initial search system
