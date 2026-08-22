<?php
/**
 * Custom post type and taxonomies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_register_types() {
	$cpt = chip_player_cpt();

	register_taxonomy(
		chip_player_tax_song(),
		$cpt,
		array(
			'labels'            => array(
				'name'          => __( 'Songs', 'chip-player' ),
				'singular_name' => __( 'Song', 'chip-player' ),
				'search_items'  => __( 'Search songs', 'chip-player' ),
				'all_items'     => __( 'All songs', 'chip-player' ),
				'edit_item'     => __( 'Edit song', 'chip-player' ),
				'add_new_item'  => __( 'Add song', 'chip-player' ),
				'new_item_name' => __( 'Song name', 'chip-player' ),
				'menu_name'     => __( 'Songs', 'chip-player' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		chip_player_tax_lang(),
		$cpt,
		array(
			'labels'            => array(
				'name'          => __( 'Languages', 'chip-player' ),
				'singular_name' => __( 'Language', 'chip-player' ),
				'search_items'  => __( 'Search languages', 'chip-player' ),
				'all_items'     => __( 'All languages', 'chip-player' ),
				'edit_item'     => __( 'Edit language', 'chip-player' ),
				'add_new_item'  => __( 'Add language', 'chip-player' ),
				'new_item_name' => __( 'Language name', 'chip-player' ),
				'menu_name'     => __( 'Languages', 'chip-player' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => false,
		)
	);

	register_taxonomy(
		chip_player_tax_version(),
		$cpt,
		array(
			'labels'            => array(
				'name'          => __( 'Versions', 'chip-player' ),
				'singular_name' => __( 'Version', 'chip-player' ),
				'search_items'  => __( 'Search versions', 'chip-player' ),
				'all_items'     => __( 'All versions', 'chip-player' ),
				'edit_item'     => __( 'Edit version', 'chip-player' ),
				'add_new_item'  => __( 'Add version', 'chip-player' ),
				'new_item_name' => __( 'Version name', 'chip-player' ),
				'menu_name'     => __( 'Versions', 'chip-player' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => false,
		)
	);

	register_post_type(
		$cpt,
		array(
			'labels'              => array(
				'name'               => __( 'Tracks', 'chip-player' ),
				'singular_name'      => __( 'Track', 'chip-player' ),
				'add_new'            => __( 'Add track', 'chip-player' ),
				'add_new_item'       => __( 'Add track', 'chip-player' ),
				'edit_item'          => __( 'Edit track', 'chip-player' ),
				'new_item'           => __( 'New track', 'chip-player' ),
				'view_item'          => __( 'View track', 'chip-player' ),
				'search_items'       => __( 'Search tracks', 'chip-player' ),
				'not_found'          => __( 'No tracks', 'chip-player' ),
				'not_found_in_trash' => __( 'No tracks in trash', 'chip-player' ),
				'menu_name'          => __( 'Chip Player', 'chip-player' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'menu_icon'           => 'dashicons-playlist-audio',
			'menu_position'       => 21,
			'supports'            => array( 'title', 'page-attributes' ),
			'taxonomies'          => array( chip_player_tax_song(), chip_player_tax_lang(), chip_player_tax_version() ),
			'capability_type'     => 'post',
		)
	);
}

function chip_player_classic_editor( $use, $type ) {
	return $type === chip_player_cpt() ? false : $use;
}
