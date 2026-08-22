<?php
/**
 * Plugin Name:       Chip Player
 * Plugin URI:        https://github.com/stefanoinnamorati/chip-player
 * Description:       One cover, one player, and chips for songs, languages, and versions. Manage tracks from WordPress.
 * Version:           1.0.1
 * Requires at least: 6.2
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Author:            Stefano Innamorati
 * Author URI:        https://profiles.wordpress.org/sinnamorati/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       chip-player
 * Domain Path:       /languages
 * Update URI:        https://github.com/stefanoinnamorati/chip-player
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CHIP_PLAYER_VERSION', '1.0.1' );
define( 'CHIP_PLAYER_FILE', __FILE__ );
define( 'CHIP_PLAYER_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHIP_PLAYER_URL', plugin_dir_url( __FILE__ ) );

require_once CHIP_PLAYER_DIR . 'includes/helpers.php';
require_once CHIP_PLAYER_DIR . 'includes/class-cpt.php';
require_once CHIP_PLAYER_DIR . 'includes/class-admin.php';
require_once CHIP_PLAYER_DIR . 'includes/class-shortcode.php';
require_once CHIP_PLAYER_DIR . 'includes/class-assets.php';
require_once CHIP_PLAYER_DIR . 'includes/class-updater.php';

register_activation_hook( __FILE__, 'chip_player_activate' );
register_deactivation_hook( __FILE__, 'chip_player_deactivate' );

function chip_player_activate() {
	if ( get_posts(
		array(
			'post_type'      => 'pepa_track',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	) ) {
		update_option( 'chip_player_legacy', '1', false );
	}

	chip_player_register_types();
	flush_rewrite_rules( false );
}

function chip_player_deactivate() {
	flush_rewrite_rules( false );
}

add_action( 'plugins_loaded', 'chip_player_register_updater' );
add_action( 'plugins_loaded', 'chip_player_load_textdomain' );
function chip_player_load_textdomain() {
	load_plugin_textdomain( 'chip-player', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'chip_player_register_types', 5 );
add_action( 'init', 'chip_player_register_shortcodes', 6 );
add_action( 'init', 'chip_player_register_block', 20 );
add_action( 'add_meta_boxes', 'chip_player_metaboxes' );
add_action( 'save_post', 'chip_player_save_meta' );
add_action( 'admin_enqueue_scripts', 'chip_player_admin_assets' );
add_action( 'admin_notices', 'chip_player_admin_notice' );
add_action( 'wp_enqueue_scripts', 'chip_player_assets', 16 );
add_action( 'admin_init', 'chip_player_register_settings' );
add_action( 'admin_menu', 'chip_player_settings_menu' );

add_filter( 'use_block_editor_for_post_type', 'chip_player_classic_editor', 10, 2 );

add_action( 'init', 'chip_player_bind_admin_columns', 6 );
function chip_player_bind_admin_columns() {
	$cpt = chip_player_cpt();
	add_filter( "manage_{$cpt}_posts_columns", 'chip_player_columns' );
	add_action( "manage_{$cpt}_posts_custom_column", 'chip_player_column_content', 10, 2 );
	add_filter( "manage_edit-{$cpt}_sortable_columns", 'chip_player_sortable_columns' );
}
