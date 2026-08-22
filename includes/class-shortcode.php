<?php
/**
 * Frontend player markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_register_shortcodes() {
	add_shortcode( 'chip_player', 'chip_player_shortcode' );
	add_shortcode( 'pepa_sound', 'chip_player_shortcode' );
}

function chip_player_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	register_block_type(
		'chip-player/player',
		array(
			'title'           => __( 'Chip Player', 'chip-player' ),
			'description'     => __( 'A cover, a player, and chips for songs, languages, and versions.', 'chip-player' ),
			'category'        => 'widgets',
			'icon'            => 'playlist-audio',
			'keywords'        => array( 'audio', 'music', 'player', 'playlist' ),
			'supports'        => array( 'html' => false ),
			'render_callback' => 'chip_player_shortcode',
		)
	);
}

function chip_player_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'cover' => '',
		),
		$atts,
		'chip_player'
	);

	$catalog = chip_player_catalog();
	if ( ! $catalog['tracks'] ) {
		if ( current_user_can( 'edit_posts' ) ) {
			return '<p class="chip-player-empty">' . esc_html__( 'No published tracks yet. Add them from Chip Player in the WordPress menu.', 'chip-player' ) . '</p>';
		}
		return '';
	}

	$cover_id = 0;
	$cover    = trim( (string) $atts['cover'] );
	if ( $cover === '' ) {
		$cover_id = (int) get_post_thumbnail_id();
		$cover    = $cover_id ? (string) wp_get_attachment_image_url( $cover_id, 'large' ) : '';
	}
	if ( $cover === '' ) {
		$setting = (int) get_option( 'chip_player_cover', 0 );
		if ( $setting ) {
			$cover_id = $setting;
			$cover    = (string) wp_get_attachment_image_url( $setting, 'large' );
		}
	}

	$first   = $catalog['tracks'][0];
	$payload = array(
		'cover'  => $cover,
		'songs'  => $catalog['songs'],
		'tracks' => $catalog['tracks'],
		'i18n'   => array(
			'play'     => __( 'Play', 'chip-player' ),
			'pause'    => __( 'Pause', 'chip-player' ),
			'previous' => __( 'Previous track', 'chip-player' ),
			'next'     => __( 'Next track', 'chip-player' ),
			'seek'     => __( 'Seek', 'chip-player' ),
		),
	);

	wp_enqueue_style( 'chip-player' );
	wp_enqueue_script( 'chip-player' );

	ob_start();
	?>
	<div class="chip-player pepa-sound" data-chip-player data-pepa-sound>
		<script type="application/json" class="chip-player-data pepa-sound-data"><?php echo wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
		<article class="chip-player-card pepa-sound-card">
			<div class="chip-player-cover pepa-sound-cover">
				<?php if ( $cover_id ) : ?>
					<?php echo wp_get_attachment_image( $cover_id, 'large', false, array( 'class' => 'chip-player-cover-img pepa-sound-cover-img', 'alt' => '' ) ); ?>
				<?php elseif ( $cover !== '' ) : ?>
					<img class="chip-player-cover-img pepa-sound-cover-img" src="<?php echo esc_url( $cover ); ?>" alt="">
				<?php endif; ?>
			</div>
			<div class="chip-player-now pepa-sound-now">
				<p class="chip-player-kicker pepa-kicker pepa-sound-song" data-sound-song><?php echo esc_html( $first['songLabel'] ); ?></p>
				<h2 class="chip-player-title pepa-sound-title" data-sound-title><?php echo esc_html( $first['title'] ); ?></h2>
				<p class="chip-player-meta pepa-sound-meta" data-sound-meta><?php echo esc_html( $first['langLabel'] . ' · ' . $first['variant'] ); ?></p>
				<div class="chip-player-controls pepa-sound-controls">
					<button type="button" class="chip-player-skip pepa-sound-skip" data-sound-prev aria-label="<?php echo esc_attr( $payload['i18n']['previous'] ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
					</button>
					<button type="button" class="chip-player-play pepa-sound-play" data-sound-play aria-label="<?php echo esc_attr( $payload['i18n']['play'] ); ?>">
						<svg class="chip-player-icon-play pepa-sound-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
						<svg class="chip-player-icon-pause pepa-sound-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
					</button>
					<button type="button" class="chip-player-skip pepa-sound-skip" data-sound-next aria-label="<?php echo esc_attr( $payload['i18n']['next'] ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 6h2v12h-2zM6 18l8.5-6L6 6z"/></svg>
					</button>
				</div>
				<div class="chip-player-progress pepa-sound-progress">
					<span data-sound-current>0:00</span>
					<input type="range" data-sound-seek min="0" max="0" step="0.1" value="0" aria-label="<?php echo esc_attr( $payload['i18n']['seek'] ); ?>">
					<span data-sound-duration>0:00</span>
				</div>
			</div>
		</article>
		<div class="chip-player-filters pepa-sound-filters">
			<p class="chip-player-kicker pepa-kicker"><?php esc_html_e( 'Song', 'chip-player' ); ?></p>
			<div class="chip-player-chips pepa-cat-filters" data-sound-songs role="tablist" aria-label="<?php esc_attr_e( 'Song', 'chip-player' ); ?>"></div>
			<p class="chip-player-kicker pepa-kicker chip-player-lang-kicker pepa-sound-lang-kicker"><?php esc_html_e( 'Language', 'chip-player' ); ?></p>
			<div class="chip-player-chips pepa-cat-filters" data-sound-langs role="tablist" aria-label="<?php esc_attr_e( 'Language', 'chip-player' ); ?>"></div>
			<p class="chip-player-kicker pepa-kicker chip-player-variant-kicker pepa-sound-variant-kicker"><?php esc_html_e( 'Version', 'chip-player' ); ?></p>
			<div class="chip-player-chips pepa-cat-filters" data-sound-variants role="tablist" aria-label="<?php esc_attr_e( 'Version', 'chip-player' ); ?>"></div>
			<p class="chip-player-note pepa-sound-note" data-sound-note></p>
		</div>
		<audio data-sound-audio preload="metadata"></audio>
		<div class="chip-player-dock pepa-sound-dock" data-sound-dock hidden>
			<img class="chip-player-dock-cover pepa-sound-dock-cover" data-sound-dock-cover alt="" width="56" height="56">
			<div class="chip-player-dock-text pepa-sound-dock-text">
				<strong data-sound-dock-title></strong>
				<span data-sound-dock-meta></span>
			</div>
			<button type="button" class="chip-player-play chip-player-play-mini pepa-sound-play pepa-sound-play-mini" data-sound-dock-play aria-label="<?php echo esc_attr( $payload['i18n']['play'] ); ?>">
				<svg class="chip-player-icon-play pepa-sound-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
				<svg class="chip-player-icon-pause pepa-sound-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
			</button>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
