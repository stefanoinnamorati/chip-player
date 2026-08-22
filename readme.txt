=== Chip Player ===
Contributors: sinnamorati
Tags: audio, music, player, playlist, mp3
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One cover, one player, and chips for songs, languages, and versions. Manage tracks from WordPress.

== Description ==

Chip Player is a lightweight HTML5 audio player for sites that publish the same song in several languages or arrangements.

Editors add tracks from the WordPress admin. Each track gets:

* a **Song** (the piece)
* a **Language**
* a **Version** (arrangement, mix, or title)

The public player shows one cover and one set of controls. Visitors pick a song, then language and version chips. A small dock follows along when they scroll.

= How to use =

1. Go to **Chip Player → Add track**.
2. Choose an audio file from the Media Library.
3. Assign Song, Language, and Version.
4. Insert `[chip_player]` or the Chip Player block on any page.

Optional shortcode attribute:

`[chip_player cover="https://example.com/cover.jpg"]`

If you omit the cover, the plugin uses the page featured image, then the default cover from settings.

= For developers =

Filterable identifiers:

* `chip_player_post_type`
* `chip_player_song_taxonomy`
* `chip_player_lang_taxonomy`
* `chip_player_version_taxonomy`
* `chip_player_use_legacy`

Existing installs that already use `pepa_track` keep working automatically.

== Installation ==

1. Upload the `chip-player` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Add tracks and insert the shortcode or block.

== Frequently Asked Questions ==

= Does it replace other playlist plugins? =

It replaces a list of separate players with one player and chips. It does not import playlists from other plugins.

= Will it work on WordPress.com? =

On WordPress.com, custom plugins can be uploaded on plans that allow them, such as Business or Commerce.

= Can I translate the interface? =

Yes. The text domain is `chip-player`.

== Changelog ==

= 1.0.0 =
* First public release: CPT, chips, HTML5 player, dock, shortcode, and block.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
