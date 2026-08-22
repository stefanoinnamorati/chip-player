<?php
/**
 * Shared helpers and filterable identifiers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_uses_legacy() {
	return apply_filters( 'chip_player_use_legacy', get_option( 'chip_player_legacy' ) === '1' );
}

function chip_player_cpt() {
	$default = chip_player_uses_legacy() ? 'pepa_track' : 'chip_track';
	return sanitize_key( apply_filters( 'chip_player_post_type', $default ) );
}

function chip_player_tax_song() {
	$default = chip_player_uses_legacy() ? 'pepa_brano' : 'chip_song';
	return sanitize_key( apply_filters( 'chip_player_song_taxonomy', $default ) );
}

function chip_player_tax_lang() {
	$default = chip_player_uses_legacy() ? 'pepa_lingua' : 'chip_lang';
	return sanitize_key( apply_filters( 'chip_player_lang_taxonomy', $default ) );
}

function chip_player_tax_version() {
	$default = chip_player_uses_legacy() ? 'pepa_versione' : 'chip_version';
	return sanitize_key( apply_filters( 'chip_player_version_taxonomy', $default ) );
}

function chip_player_first_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	return array_values( $terms )[0];
}

function chip_player_audio_src( $post_id ) {
	$audio_id = (int) get_post_meta( $post_id, '_chip_audio_id', true );
	if ( ! $audio_id ) {
		$audio_id = (int) get_post_meta( $post_id, '_pepa_audio_id', true );
	}
	if ( $audio_id ) {
		$url = wp_get_attachment_url( $audio_id );
		if ( $url ) {
			return $url;
		}
	}
	$url = (string) get_post_meta( $post_id, '_chip_audio_url', true );
	if ( $url === '' ) {
		$url = (string) get_post_meta( $post_id, '_pepa_audio_url', true );
	}
	return $url;
}

function chip_player_catalog() {
	$posts = get_posts(
		array(
			'post_type'      => chip_player_cpt(),
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);

	$tracks = array();
	$songs  = array();
	foreach ( $posts as $post ) {
		$src = chip_player_audio_src( $post->ID );
		if ( $src === '' ) {
			continue;
		}

		$song_term    = chip_player_first_term( $post->ID, chip_player_tax_song() );
		$lang_term    = chip_player_first_term( $post->ID, chip_player_tax_lang() );
		$version_term = chip_player_first_term( $post->ID, chip_player_tax_version() );

		$song_id    = $song_term ? $song_term->slug : 'track-' . $post->ID;
		$song_label = $song_term ? $song_term->name : $post->post_title;
		$note       = $song_term ? trim( (string) $song_term->description ) : '';
		$lang_id    = $lang_term ? $lang_term->slug : 'other';
		$lang_label = $lang_term ? $lang_term->name : __( 'Other', 'chip-player' );
		$variant    = $version_term ? $version_term->name : $post->post_title;

		$tracks[] = array(
			'id'        => (string) $post->ID,
			'song'      => $song_id,
			'songLabel' => $song_label,
			'lang'      => $lang_id,
			'langLabel' => $lang_label,
			'variant'   => $variant,
			'title'     => $post->post_title,
			'src'       => $src,
			'order'     => (int) $post->menu_order,
		);

		if ( ! isset( $songs[ $song_id ] ) ) {
			$songs[ $song_id ] = array(
				'id'    => $song_id,
				'label' => $song_label,
				'note'  => $note,
				'order' => (int) $post->menu_order,
			);
		}
	}

	uasort(
		$songs,
		static function ( $a, $b ) {
			return $a['order'] <=> $b['order'];
		}
	);

	return array(
		'songs'  => array_values( $songs ),
		'tracks' => $tracks,
	);
}

function chip_player_should_enqueue() {
	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();
	if ( ! $post ) {
		return false;
	}

	$content = (string) $post->post_content;

	return has_shortcode( $content, 'chip_player' )
		|| has_shortcode( $content, 'pepa_sound' )
		|| has_block( 'chip-player/player', $post );
}
