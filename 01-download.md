---
layout: page
title: Download & Install
permalink: /download/
---

{: .center}
[Grab the latest version][latest]{: .button .button--purple target="_blank"}

<div class="download-information">
  v0.13.0 | <a href="https://github.com/citelao/Spotify-for-Alfred/releases">All versions</a> | 
  <a href="https://github.com/citelao/Spotify-for-Alfred">Source</a>
</div>

Installation is gosh-darned simple. Follow these instructions and you'll be
happier than a fourth-grader at recess.

It's a two-part process, but it shouldn't take all of 5 minutes.

## Download the thingy

{: .installation-guide}
1. Download the [latest version][latest] of Spotifious, specifically the `alfredworkflow` file.
2. Open `Spotifious.alfredworkflow` by double-clicking it or dragging it
	into Alfred.
3. 
	{: .installation-guide__aside}
	![Figure 1]({{site.baseurl}}/img/hotkey.png){: .shadow}
	**Figure 1**: The first thingy marked `Hotkey`
	
	**Bind the launcher hotkey**:

	1. Double-click the first thingy marked `Hotkey` (see **Figure 1**).
	2. Click the text field labeled `Hotkey` and press `^⌘⏎`. If your iTunes Mini Player is set to the same hotkey, you will have to unbind/rebind it first (`Features` &rarr; `iTunes` &rarr; the thing that has a hotkey in it).
	3. Click `Save` to store the binding.
4. Bind the other hotkeys as you wish. Their actions are visible in the `Text`
	field, under `Argument`
5. You can now install Spotifious!

[latest]: https://github.com/citelao/Spotify-for-Alfred/releases/latest "Always the latest version of Spotifious"

## Install the thingy

{: .installation-guide}
1. Pop up Spotifious with the key command you set— I would have `^⌘⏎`
2. Select **Set your country code** and choose your country from the list, or select "I'd rather not give a country!"

	(adding a country prevents music you can't play from showing up in search. I don't do anything with it)
3. Select **Create a Spotify application**. This should open a webpage that will guide you through setup. Read that or follow below:
	1. Open the [Spotify application manager](https://developer.spotify.com/my-applications/#!/applications), logging in if asked.
	2. Click the shiny **Create an app** button and enter an awesome application name & description. Or a boring name, it doesn't matter. .
	5. Add `http://localhost:11114/callback.php` as a redirect URI.
	6. Save those changes by clicking `Save` at the bottom of the page.
	7. Enter your `Client ID` and `Client Secret` on the [guide page](http://localhost:11114/include/setup/index.php#ajax){: target="_blank"}.

		This link will not work if you are not in the middle of setup.
	8. Pop open Spotifious again to continue setup!
4. **Link your Spotify application** by selecting the so-named option in Spotifious and logging in to Spotify. You'll see a list of what permissions Spotifious uses.
5. You're done! Read the [User's Guide]({{site.baseurl}}/usage) to learn the ins and outs of Spotifious.
