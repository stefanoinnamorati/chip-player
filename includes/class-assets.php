<?php
/**
 * Front and admin assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_assets() {
	if ( is_admin() ) {
		return;
	}

	wp_register_style(
		'chip-player',
		CHIP_PLAYER_URL . 'assets/css/player.css',
		array(),
		CHIP_PLAYER_VERSION
	);
	wp_register_script(
		'chip-player',
		CHIP_PLAYER_URL . 'assets/js/player.js',
		array(),
		CHIP_PLAYER_VERSION,
		true
	);

	$should = chip_player_content_has( '[chip_player]' )
		|| chip_player_content_has( '[pepa_sound]' )
		|| chip_player_content_has( 'chip-player/player' )
		|| is_page( 'pescara-pattini-sound' );

	if ( $should ) {
		wp_enqueue_style( 'chip-player' );
		wp_enqueue_script( 'chip-player' );
	}
}

function chip_player_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$on_track = $screen && $screen->post_type === chip_player_cpt() && in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	$on_settings = $screen && $screen->id === chip_player_cpt() . '_page_chip-player-settings';
	if ( ! $on_track && ! $on_settings ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'chip-player-admin',
		CHIP_PLAYER_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		CHIP_PLAYER_VERSION,
		true
	);
	wp_localize_script(
		'chip-player-admin',
		'chipPlayerAdmin',
		array(
			'title'  => __( 'Choose audio file', 'chip-player' ),
			'button' => __( 'Use this file', 'chip-player' ),
			'image'  => __( 'Choose cover image', 'chip-player' ),
			'none'   => __( 'No file', 'chip-player' ),
		)
	);
}
