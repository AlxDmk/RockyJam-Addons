<?php
/**
 * Key Features — helper functions and WC hook callback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Key Features block on the single product page.
 *
 * Hooked to woocommerce_single_product_summary (default priority 25).
 * Does nothing if the product has no features saved.
 */
function rockyjam_keyfeatures_render(): void {
	global $post;

	if ( ! $post ) {
		return;
	}

	$raw      = get_post_meta( $post->ID, '_rockyjam_key_features', true );
	$features = array_filter( array_map( 'trim', (array) $raw ) );

	if ( empty( $features ) ) {
		return;
	}
	?>
	<div class="product-key-features">
		<p class="product-key-features__title"><?php esc_html_e( 'KEY FEATURES', 'rockyjam-addons' ); ?></p>
		<ul class="product-key-features__list">
			<?php foreach ( $features as $feature ) : ?>
				<li class="product-key-features__item">
					<span class="dashicons dashicons-yes"></span>
					<?php echo esc_html( $feature ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Declare this addon's WooCommerce hook to RockyJam Templates Hook Manager.
 * The callback will appear in the Hooks Editor tab for every template,
 * allowing enable/disable and priority changes per template.
 */
add_filter( 'rockyjam_addon_hooks', function ( array $hooks ): array {
	$hooks[] = [
		'addon_id'   => 'key-features',
		'addon_name' => 'Key Features',
		'hook'       => 'woocommerce_single_product_summary',
		'function'   => 'rockyjam_keyfeatures_render',
		'priority'   => 25,
		'label'      => 'Key Features Block',
	];
	return $hooks;
} );
