<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use Spotifious\Menus\Helper;
use OhAlfred\HTTP\JsonFetcher;
use OhAlfred\HTTP\JsonParser;
use OhAlfred\OhAlfred;

class Search implements Menu {
	protected $alfred;	
	protected $search;
	protected $query;

	public function __construct($query, $alfred, $api) {
		$this->query = $query;
		$this->alfred = $alfred;

		$locale = $this->alfred->options('country');

		// Use the API to fetch, if possible.
		$json = "";
		if($api) {
			$options = array();
			if($locale != 'not-given') {
				$options['market'] = $locale;
			}
			$json = $api->search($query, ['artist', 'album', 'track'], $options);
		} else {
			/* Fetch and parse the search results. */
			$urlQuery = urlencode($query);
			$url = "https://api.spotify.com/v1/search?q=$urlQuery&type=artist,album,track";
			if($locale != 'not-given') {
				$url .= "&market=$locale";
			}
			
			$fetcher = new JsonFetcher($url);
			$json = $fetcher->run();
		}

		// Albums do not include artist data.
		// Grab all the album ids, and find their artists
		$albumIDs = array();
		foreach ($json->albums->items as $key => $value) {
			$albumIDs[] = $value->id;
		}

		if(sizeof($albumIDs) != 0)
		{
			$albumsJson = null;
			if($api) {
				$albumsJson = $api->getAlbums($albumIDs);
			} else {
				$urlQuery = str_replace("%3A", ":", urlencode(join(',', $albumIDs)));
				$url = "https://api.spotify.com/v1/albums?ids=$urlQuery";
				
				$albumFetcher = new JsonFetcher($url);
				$albumsJson = $albumFetcher->run();
			}

			$albums = array();
			foreach ($albumsJson->albums as $key => $value) {
				$albums[] = array(
					'artist' => $value->artists[0]->name,
					'popularity' => $value->popularity
				);
			}
		}

		// Build the search results
		// for each query type
		foreach (array('artist', 'album', 'track') as $type) {
			// Create the search results array
			foreach ($json->{$type . "s"}->items as $key => $value) {
				// Weight popularity
				if($type == 'album') {
					$popularity = $albums[$key]['popularity'];
				} else {
					$popularity = $value->popularity;
				}

				if($type == 'artist')
					$popularity += 50;

				if($type == 'album')
					$popularity += 25;

				if ($type == 'track') {
					$currentRaw['album'] = $value->album->name;
					$currentRaw['album_uri'] = $value->album->uri;
					$currentRaw['artist'] = $value->artists[0]->name;	
				} elseif ($type == 'album') {
					$currentRaw['artist'] = $albums[$key]['artist'];
				}

				if($type == 'album') {
					$currentRaw['type'] = $value->album_type;	
				} else {
					$currentRaw['type'] = $value->type;	
				}
				
				$currentRaw['title'] = $value->name;
				$currentRaw['popularity'] = $popularity;
				$currentRaw['uri'] = $value->uri;

				$this->search[] = $currentRaw;
			}
		}

		// Add playlists to result
		$playlists_json = $this->alfred->options('playlists');
		if($playlists_json !== '') {
			$playlists = json_decode($playlists_json);

			foreach ($playlists as $playlist) {
				if(!@mb_stristr(mb_strtoupper($playlist->name), mb_strtoupper($query))) {
					continue;
				}

				$this->search[] = array(
					'type' => 'playlist',
					'uri' => $playlist->uri,
					'title' => $playlist->name,
					'owner' => $playlist->owner,
					'popularity' => 80
				);
			}
		}

		if(!empty($this->search))
			usort($this->search, array($this, 'popularitySort'));
	}
	
	public function output() {
		if(!empty($this->search)) {
			foreach ($this->search as $key => $current) {
				$popularity = Helper::floatToBars($current['popularity']);

				if ($current['type'] == 'track') {
					$subtitle = "$popularity {$current['album']} by {$current['artist']}";
				} elseif ($current['type'] == 'album') {
					$subtitle = "$popularity Album by {$current['artist']}";
				} elseif ($current['type'] == 'single') {
					$subtitle = "$popularity Single by {$current['artist']}";
				} elseif ($current['type'] == 'playlist') {
					$subtitle = "Playlist by {$current['owner']}";
				} else {
					$subtitle = "$popularity " . ucfirst($current['type']);
				}

				if ($current['type'] == 'track') {
					$valid = true;
					$arg = "spotify⟩play track \"{$current['uri']}\"";
					$autocomplete = '';

					// $album_json = '{ "action":"spotifious", "command":"test" }';

					$mods = array(
						// 'alt' => array(
						// 	'subtitle' => "Browse to artist ({$current['artist']})..."
						// ),
						// 'ctrl' => array(
						// 	'subtitle' => "Browse to album ({$current['album']})...",
						// 	'arg' => $album_json,
						// ),
						'shift' => array(
							'subtitle' => 'Reveal in Spotify',
							'arg' => "spotify⟩activate (open location \"{$current['uri']}\")"
						)
					);
				} else {
					$valid = false;
					$arg = '';
					$autocomplete = "{$current['uri']} ⟩ {$this->query} ⟩";
				}

				// Modifiers :D
				if($current['type'] == 'playlist') {
					$mods = array(
						'ctrl' => array(
							'subtitle' => 'Play this playlist',
							'valid' => true,
							'arg' => "spotify⟩play track \"{$current['uri']}\"",
							'autocomplete' => ''
 						),
						// 'alt' => array(),
						// 'cmd' => array(
						// 	'subtitle' => 'Queue playlist'
						// )
					);
				} else if($current['type'] == 'artist') {
					$mods = array(
						'ctrl' => array(
							'subtitle' => 'Play this artist',
							'valid' => true,
							'arg' => "spotify⟩play track \"{$current['uri']}\"",
							'autocomplete' => ''
 						)
 						// 'cmd' => array( 'subtitle' => 'Queue this artist' ),
 						// 'shift' => array( 'subtitle' => 'Open in Spotify' )
					);
				} else if($current['type'] == 'album') {
					$mods = array(
						'ctrl' => array(
							'subtitle' => 'Play this album',
							'valid' => true,
							'arg' => "spotify⟩play track \"{$current['uri']}\"",
							'autocomplete' => ''
 						)
 						// 'cmd' => array( 'subtitle' => 'Queue this album' ),
 						// 'shift' => array( 'subtitle' => 'Open in Spotify' ),
 						// 'alt' => array('subtitle' => 'Browse to artist'),
					);
				} else if($current['type'] == 'single') {
					$mods = array(
						'ctrl' => array(
							'subtitle' => 'Play this single',
							'valid' => true,
							'arg' => "spotify⟩play track \"{$current['uri']}\"",
							'autocomplete' => ''
 						)
 						// 'cmd' => array( 'subtitle' => 'Queue this single' ),
 						// 'shift' => array( 'subtitle' => 'Open in Spotify' ),
 						// 'alt' => array('subtitle' => 'Browse to artist'),
					);
				} else if($current['type'] == 'track') {
					// Unimplemented since we have no good way of changing
					// autocomplete strings.
					$mods = array(
						// 'cmd' => array( 'subtitle' => 'Queue this song' ),
						// 'shift' => array( 'subtitle' => 'Open in Spotify' ),
						// 'alt' => array('subtitle' => 'Browse to artist'),
						// 'ctrl' => array( 'subtitle' => 'Browse to album' ),
					);
				}

				$currentResult['title']    = $current['title'];
				$currentResult['subtitle'] = $subtitle;
				$currentResult['uid'] = "bs-spotify-{$this->query}-{$current['type']}-{$current['title']}";
				$currentResult['valid'] = $valid;
				$currentResult['arg'] = $arg;
				$currentResult['autocomplete'] = $autocomplete;
				$currentResult['copy'] = $current['uri'];
				$currentResult['icon'] = array('path' => "include/images/{$current['type']}.png");
				if(isset($mods)) {
					$currentResult['mods'] = $mods;
				}

				$results[] = $currentResult;
			}
		}

		/* Give the option to continue searching in Spotify because even I know my limits. */
		$results[] = array(
			'title' => "Search for {$this->query}",
			'subtitle' => "Continue this search in Spotify…",
			'uid' => "bs-spotify-{$this->query}-more",
			'arg' => "spotify⟩activate (open location \"spotify:search:{$this->query}\")",
			'icon' => array('path' => 'include/images/search.png')
		);

		return $results;
	}

	protected function popularitySort($a, $b) {
		if($a['popularity'] == $b['popularity'])
			return 0;

		return ($a['popularity'] < $b['popularity']) ? 1 : -1;
	}

	protected function contains($stack, $needle) {
		return (strpos($stack, $needle) !== false);
	}
}
