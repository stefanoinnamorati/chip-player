# Chip Player

WordPress plugin: one cover, one HTML5 player, and chips for songs, languages, and versions.

This repository is dedicated to the plugin. The first implementation lives on [pescarapattini.it](https://www.pescarapattini.it/pescara-pattini-sound/).

## Use it

1. Copy the plugin folder into `wp-content/plugins/chip-player`.
2. Activate **Chip Player**.
3. Add tracks from the WordPress menu.
4. Insert `[chip_player]` or the Chip Player block.

`[pepa_sound]` still works as an alias.

## Develop

```
chip-player.php
includes/
assets/css/player.css
assets/js/player.js
assets/js/admin.js
```

PHP 7.4+, WordPress 6.2+. No build step.

## Updates

Until the plugin is on WordPress.org, WordPress checks [GitHub Releases](https://github.com/stefanoinnamorati/chip-player/releases). Tag a version (`1.0.2`) that matches the plugin header, push, and publish a release. The host must allow outbound HTTPS.

Optional in `wp-config.php` if GitHub rate-limits the server:

```php
define( 'CHIP_PLAYER_GITHUB_TOKEN', 'ghp_…' );
```

Set `CHIP_PLAYER_DISABLE_GITHUB_UPDATES` to true if WordPress.org should be the only update source.

## WordPress.org

The WordPress.org contributor account is `sinnamorati`. `readme.txt` follows the plugin directory format.

Submit a zip of the plugin folder (not this Git working copy). Exclude `.git`, `.gitignore`, and this `README.md`. After approval, add banners and icons in the plugin directory SVN `assets/` folder, not in this repository.

## License

GPL-2.0-or-later
