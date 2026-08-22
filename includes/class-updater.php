<?php
/**
 * GitHub Releases updater. Disabled when WordPress.org hosts the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_github_repo() {
	return sanitize_text_field( apply_filters( 'chip_player_github_repo', 'stefanoinnamorati/chip-player' ) );
}

function chip_player_github_updates_enabled() {
	if ( defined( 'CHIP_PLAYER_DISABLE_GITHUB_UPDATES' ) && CHIP_PLAYER_DISABLE_GITHUB_UPDATES ) {
		return false;
	}

	$headers = get_file_data(
		CHIP_PLAYER_FILE,
		array(
			'UpdateURI' => 'Update URI',
		)
	);
	$uri = isset( $headers['UpdateURI'] ) ? (string) $headers['UpdateURI'] : '';
	if ( $uri !== '' && false !== strpos( $uri, 'wordpress.org/plugins' ) ) {
		return false;
	}

	return (bool) apply_filters( 'chip_player_github_updates', true );
}

function chip_player_register_updater() {
	if ( ! chip_player_github_updates_enabled() ) {
		return;
	}
	if ( ! is_admin() && ! wp_doing_cron() ) {
		return;
	}

	add_filter( 'pre_set_site_transient_update_plugins', 'chip_player_inject_update' );
	add_filter( 'plugins_api', 'chip_player_plugins_api', 10, 3 );
	add_filter( 'upgrader_source_selection', 'chip_player_upgrader_source_selection', 10, 4 );
}

function chip_player_plugin_basename_file() {
	return plugin_basename( CHIP_PLAYER_FILE );
}

function chip_player_github_release( $force = false ) {
	$cache_key = 'chip_player_github_release';
	if ( ! $force ) {
		$cached = get_site_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$repo = chip_player_github_repo();
	$url  = 'https://api.github.com/repos/' . $repo . '/releases/latest';
	$args = array(
		'timeout' => 15,
		'headers' => array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'Chip-Player/' . CHIP_PLAYER_VERSION . '; ' . home_url( '/' ),
		),
	);
	if ( defined( 'CHIP_PLAYER_GITHUB_TOKEN' ) && CHIP_PLAYER_GITHUB_TOKEN ) {
		$args['headers']['Authorization'] = 'Bearer ' . CHIP_PLAYER_GITHUB_TOKEN;
	}

	$response = wp_remote_get( $url, $args );
	if ( is_wp_error( $response ) ) {
		set_site_transient( $cache_key, array(), 3 * HOUR_IN_SECONDS );
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( $code !== 200 || ! is_array( $body ) || empty( $body['tag_name'] ) ) {
		set_site_transient( $cache_key, array(), 3 * HOUR_IN_SECONDS );
		return array();
	}

	$tag     = ltrim( (string) $body['tag_name'], 'v' );
	$package = 'https://github.com/' . $repo . '/archive/refs/tags/' . rawurlencode( (string) $body['tag_name'] ) . '.zip';
	if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
		foreach ( $body['assets'] as $asset ) {
			$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
			$url  = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';
			if ( $url !== '' && preg_match( '/chip-player.*\.zip$/i', $name ) ) {
				$package = $url;
				break;
			}
		}
	}

	$release = array(
		'version'     => $tag,
		'package'     => $package,
		'url'         => isset( $body['html_url'] ) ? (string) $body['html_url'] : 'https://github.com/' . $repo,
		'changelog'   => isset( $body['body'] ) ? (string) $body['body'] : '',
		'published'   => isset( $body['published_at'] ) ? (string) $body['published_at'] : '',
		'tag'         => (string) $body['tag_name'],
	);

	set_site_transient( $cache_key, $release, 12 * HOUR_IN_SECONDS );
	return $release;
}

function chip_player_update_item( $release ) {
	$plugin = chip_player_plugin_basename_file();
	return (object) array(
		'id'            => 'github.com/' . chip_player_github_repo(),
		'slug'          => 'chip-player',
		'plugin'        => $plugin,
		'new_version'   => $release['version'],
		'url'           => $release['url'],
		'package'       => $release['package'],
		'icons'         => array(),
		'banners'       => array(),
		'banners_rtl'   => array(),
		'tested'        => '7.1',
		'requires_php'  => '7.4',
		'requires'      => '6.2',
		'compatibility' => new stdClass(),
	);
}

function chip_player_inject_update( $transient ) {
	if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
		return $transient;
	}

	$release = chip_player_github_release();
	if ( empty( $release['version'] ) || empty( $release['package'] ) ) {
		return $transient;
	}

	$plugin = chip_player_plugin_basename_file();
	$item   = chip_player_update_item( $release );

	if ( version_compare( CHIP_PLAYER_VERSION, $release['version'], '<' ) ) {
		$transient->response[ $plugin ] = $item;
		unset( $transient->no_update[ $plugin ] );
	} else {
		$transient->no_update[ $plugin ] = $item;
		unset( $transient->response[ $plugin ] );
	}

	return $transient;
}

function chip_player_plugins_api( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || 'chip-player' !== $args->slug ) {
		return $result;
	}

	$release = chip_player_github_release();
	$version = ! empty( $release['version'] ) ? $release['version'] : CHIP_PLAYER_VERSION;
	$package = ! empty( $release['package'] ) ? $release['package'] : '';
	$url     = ! empty( $release['url'] ) ? $release['url'] : 'https://github.com/' . chip_player_github_repo();
	$notes   = ! empty( $release['changelog'] ) ? $release['changelog'] : __( 'See the GitHub release page for details.', 'chip-player' );

	return (object) array(
		'name'          => 'Chip Player',
		'slug'          => 'chip-player',
		'version'       => $version,
		'author'        => '<a href="https://profiles.wordpress.org/sinnamorati/">Stefano Innamorati</a>',
		'homepage'      => $url,
		'requires'      => '6.2',
		'tested'        => '7.1',
		'requires_php'  => '7.4',
		'download_link' => $package,
		'sections'      => array(
			'description' => __( 'One cover, one player, and chips for songs, languages, and versions.', 'chip-player' ),
			'changelog'   => wp_kses_post( wpautop( $notes ) ),
		),
	);
}

function chip_player_upgrader_source_selection( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;

	if ( empty( $hook_extra['plugin'] ) || chip_player_plugin_basename_file() !== $hook_extra['plugin'] ) {
		return $source;
	}

	if ( ! $wp_filesystem || ! is_string( $source ) || ! is_string( $remote_source ) ) {
		return $source;
	}

	$desired = trailingslashit( $remote_source ) . 'chip-player';
	$current = untrailingslashit( $source );
	if ( $current === $desired ) {
		return trailingslashit( $source );
	}

	if ( $wp_filesystem->is_dir( $desired ) ) {
		$wp_filesystem->delete( $desired, true );
	}

	if ( ! $wp_filesystem->move( $current, $desired ) ) {
		return new WP_Error(
			'chip_player_rename',
			__( 'Chip Player could not rename the GitHub download folder.', 'chip-player' )
		);
	}

	return trailingslashit( $desired );
}
