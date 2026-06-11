<?php
/**
 * Key Features — Product meta-box (admin).
 *
 * Adds a "Key Features" box to the product edit screen where the user
 * can add/remove/reorder feature strings via a dynamic list.
 * Data is saved as post meta: _rockyjam_key_features => string[]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Register meta-box ─────────────────────────────────────────────────────────
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'rockyjam_key_features',
		__( 'Key Features', 'rockyjam-addons' ),
		'rockyjam_keyfeatures_metabox_render',
		'product',
		'normal',
		'default'
	);
} );

// ── Render ────────────────────────────────────────────────────────────────────
function rockyjam_keyfeatures_metabox_render( WP_Post $post ): void {
	wp_nonce_field( 'rockyjam_key_features_save', 'rockyjam_key_features_nonce' );

	$raw      = get_post_meta( $post->ID, '_rockyjam_key_features', true );
	$features = is_array( $raw ) ? array_values( array_filter( array_map( 'trim', $raw ) ) ) : [];
	?>
	<div class="rjt-kf-metabox">
		<p class="rjt-kf-metabox__hint">
			<?php esc_html_e( 'Add the key features of this product. They will be displayed on the product page when the Key Features addon is active.', 'rockyjam-addons' ); ?>
		</p>

		<ul class="rjt-kf-list" id="rjt-kf-list">
			<?php if ( $features ) : ?>
				<?php foreach ( $features as $feature ) : ?>
					<li class="rjt-kf-list__item">
						<span class="rjt-kf-list__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'rockyjam-addons' ); ?>"></span>
						<input
							type="text"
							name="rockyjam_key_features[]"
							class="rjt-kf-list__input"
							value="<?php echo esc_attr( $feature ); ?>"
							placeholder="<?php esc_attr_e( 'Feature description…', 'rockyjam-addons' ); ?>"
						/>
						<button type="button" class="button rjt-kf-list__remove" title="<?php esc_attr_e( 'Remove', 'rockyjam-addons' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>

		<button type="button" class="button button-secondary rjt-kf-add-btn" id="rjt-kf-add-btn">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Add Feature', 'rockyjam-addons' ); ?>
		</button>

		<?php if ( empty( $features ) ) : ?>
			<p class="rjt-kf-metabox__empty" id="rjt-kf-empty">
				<?php esc_html_e( 'No features yet. Click "Add Feature" to start.', 'rockyjam-addons' ); ?>
			</p>
		<?php else : ?>
			<p class="rjt-kf-metabox__empty" id="rjt-kf-empty" style="display:none"></p>
		<?php endif; ?>
	</div>
	<?php
}

// ── Save ──────────────────────────────────────────────────────────────────────
add_action( 'save_post_product', function ( int $post_id ) {
	// Nonce check.
	if (
		! isset( $_POST['rockyjam_key_features_nonce'] ) ||
		! wp_verify_nonce( $_POST['rockyjam_key_features_nonce'], 'rockyjam_key_features_save' )
	) {
		return;
	}

	// Capability check.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Autosave / revision guard.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Sanitize and save.
	$raw      = isset( $_POST['rockyjam_key_features'] ) ? (array) $_POST['rockyjam_key_features'] : [];
	$features = array_values(
		array_filter(
			array_map( 'sanitize_text_field', array_map( 'wp_unslash', $raw ) )
		)
	);

	if ( empty( $features ) ) {
		delete_post_meta( $post_id, '_rockyjam_key_features' );
	} else {
		update_post_meta( $post_id, '_rockyjam_key_features', $features );
	}
} );
