<?php
/**
 * Track metabox, columns, and settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function chip_player_metaboxes() {
	add_meta_box(
		'chip_player_audio',
		__( 'Audio file', 'chip-player' ),
		'chip_player_audio_metabox',
		chip_player_cpt(),
		'normal',
		'high'
	);
}

function chip_player_audio_metabox( $post ) {
	wp_nonce_field( 'chip_player_audio', 'chip_player_audio_nonce' );
	$audio_id  = (int) get_post_meta( $post->ID, '_chip_audio_id', true );
	$audio_url = (string) get_post_meta( $post->ID, '_chip_audio_url', true );
	if ( ! $audio_id ) {
		$audio_id = (int) get_post_meta( $post->ID, '_pepa_audio_id', true );
	}
	if ( $audio_url === '' ) {
		$audio_url = (string) get_post_meta( $post->ID, '_pepa_audio_url', true );
	}
	if ( $audio_id ) {
		$from_id = wp_get_attachment_url( $audio_id );
		if ( $from_id ) {
			$audio_url = $from_id;
		}
	}
	$label = __( 'No file', 'chip-player' );
	if ( $audio_id ) {
		$label = basename( (string) get_attached_file( $audio_id ) );
	} elseif ( $audio_url !== '' ) {
		$label = basename( wp_parse_url( $audio_url, PHP_URL_PATH ) ?: $audio_url );
	}
	?>
	<p><?php esc_html_e( 'Pick an audio file from the Media Library, then assign Song, Language, and Version in the sidebar. Those tags become the player chips.', 'chip-player' ); ?></p>
	<p>
		<input type="hidden" id="chip_audio_id" name="chip_audio_id" value="<?php echo esc_attr( (string) $audio_id ); ?>">
		<input type="hidden" id="chip_audio_url" name="chip_audio_url" value="<?php echo esc_attr( $audio_url ); ?>">
		<strong data-chip-audio-name><?php echo esc_html( $label ); ?></strong>
	</p>
	<p>
		<button type="button" class="button button-primary" data-chip-audio-pick><?php esc_html_e( 'Choose audio', 'chip-player' ); ?></button>
		<button type="button" class="button" data-chip-audio-clear><?php esc_html_e( 'Remove', 'chip-player' ); ?></button>
	</p>
	<p class="description"><?php esc_html_e( 'Change the order with Attributes → Order (lower numbers appear first). Trash a track to remove it from the player.', 'chip-player' ); ?></p>
	<?php
}

function chip_player_save_meta( $post_id ) {
	if ( get_post_type( $post_id ) !== chip_player_cpt() ) {
		return;
	}
	if ( ! isset( $_POST['chip_player_audio_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['chip_player_audio_nonce'] ) ), 'chip_player_audio' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$audio_id  = isset( $_POST['chip_audio_id'] ) ? absint( $_POST['chip_audio_id'] ) : 0;
	$audio_url = isset( $_POST['chip_audio_url'] ) ? esc_url_raw( wp_unslash( $_POST['chip_audio_url'] ) ) : '';
	if ( $audio_id && ! $audio_url ) {
		$audio_url = (string) wp_get_attachment_url( $audio_id );
	}
	update_post_meta( $post_id, '_chip_audio_id', $audio_id );
	update_post_meta( $post_id, '_chip_audio_url', $audio_url );
	update_post_meta( $post_id, '_pepa_audio_id', $audio_id );
	update_post_meta( $post_id, '_pepa_audio_url', $audio_url );
}

function chip_player_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== chip_player_cpt() || $screen->base !== 'edit' ) {
		return;
	}
	echo '<div class="notice notice-info"><p>';
	echo '<strong>' . esc_html__( 'Chip Player.', 'chip-player' ) . '</strong> ';
	echo esc_html__( 'Each row is a track. Song, Language, and Version tags become the chips. Use the shortcode [chip_player] or the Chip Player block.', 'chip-player' );
	echo '</p></div>';
}

function chip_player_columns( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( $key === 'title' ) {
			$out['chip_audio'] = __( 'Audio', 'chip-player' );
			$out['menu_order'] = __( 'Order', 'chip-player' );
		}
	}
	return $out;
}

function chip_player_column_content( $column, $post_id ) {
	if ( $column === 'menu_order' ) {
		$post = get_post( $post_id );
		echo $post ? (int) $post->menu_order : '';
		return;
	}
	if ( $column !== 'chip_audio' ) {
		return;
	}
	echo chip_player_audio_src( $post_id ) !== '' ? esc_html__( 'Yes', 'chip-player' ) : esc_html__( 'Missing', 'chip-player' );
}

function chip_player_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}

function chip_player_register_settings() {
	register_setting(
		'chip_player',
		'chip_player_cover',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		)
	);
}

function chip_player_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=' . chip_player_cpt(),
		__( 'Chip Player settings', 'chip-player' ),
		__( 'Settings', 'chip-player' ),
		'manage_options',
		'chip-player-settings',
		'chip_player_settings_page'
	);
}

function chip_player_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$cover_id = (int) get_option( 'chip_player_cover', 0 );
	$cover    = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Chip Player settings', 'chip-player' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'chip_player' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Default cover', 'chip-player' ); ?></th>
					<td>
						<input type="hidden" id="chip_player_cover" name="chip_player_cover" value="<?php echo esc_attr( (string) $cover_id ); ?>">
						<p>
							<button type="button" class="button" data-chip-cover-pick><?php esc_html_e( 'Choose image', 'chip-player' ); ?></button>
							<button type="button" class="button" data-chip-cover-clear><?php esc_html_e( 'Remove', 'chip-player' ); ?></button>
						</p>
						<p class="description"><?php esc_html_e( 'Used when the page has no featured image and the shortcode has no cover attribute.', 'chip-player' ); ?></p>
						<?php if ( $cover ) : ?>
							<p><img src="<?php echo esc_url( $cover ); ?>" alt="" style="max-width:220px;height:auto;"></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<p><?php esc_html_e( 'Shortcode:', 'chip-player' ); ?> <code>[chip_player]</code> &nbsp; <?php esc_html_e( 'Optional cover:', 'chip-player' ); ?> <code>[chip_player cover="https://example.com/cover.jpg"]</code></p>
	</div>
	<?php
}
