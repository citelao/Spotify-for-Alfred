# Changelog #

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
