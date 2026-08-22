<?php
/**
 * Cleanup when the plugin is deleted.
 * Tracks stay in the database unless CHIP_PLAYER_DELETE_DATA is defined true.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'chip_player_cover' );

if ( defined( 'CHIP_PLAYER_DELETE_DATA' ) && CHIP_PLAYER_DELETE_DATA ) {
	delete_option( 'chip_player_legacy' );
}
