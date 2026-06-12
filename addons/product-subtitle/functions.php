<?php
/**
 * Product Subtitle — meta handling and WooCommerce hook callbacks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key for the subtitle.
 */
const ROCKYJAM_PRODUCT_SUBTITLE_META_KEY = '_rockyjam_product_subtitle';

/**
 * Add a simple text field for the product subtitle in the Product data → General tab.
 */
add_action( 'woocommerce_product_options_general_product_data', function (): void {
	echo '<div class="options_group">';

	woocommerce_wp_text_input(
		[
			'id'          => ROCKYJAM_PRODUCT_SUBTITLE_META_KEY,
			'label'       => __( 'Product subtitle', 'rockyjam-addons' ),
			'placeholder' => __( 'Heavy-duty training sandbag with multiple configurations', 'rockyjam-addons' ),
			'desc_tip'    => true,
			'description' => __( 'Short marketing subtitle shown under the product title on the product page.', 'rockyjam-addons' ),
		]
	);

	echo '</div>';
} );

/**
 * Save subtitle when the product is saved.
 */
add_action( 'woocommerce_admin_process_product_object', function ( WC_Product $product ): void {
	if ( isset( $_POST[ ROCKYJAM_PRODUCT_SUBTITLE_META_KEY ] ) ) {
		$subtitle = sanitize_text_field( wp_unslash( $_POST[ ROCKYJAM_PRODUCT_SUBTITLE_META_KEY ] ) );
		$product->update_meta_data( ROCKYJAM_PRODUCT_SUBTITLE_META_KEY, $subtitle );
	}
} );

/**
 * Render the subtitle on the single product page.
 *
 * Hooked near the product title. The exact hook/priority is controlled
 * via RockyJam Templates Hook Manager (see filter below).
 */
function rockyjam_product_subtitle_render(): void {
	global $post;

	if ( ! $post ) {
		return;
	}

	$subtitle = get_post_meta( $post->ID, ROCKYJAM_PRODUCT_SUBTITLE_META_KEY, true );
	$subtitle = trim( (string) $subtitle );

	if ( '' === $subtitle ) {
		return;
	}
	?>
	<p class="rj-product-subtitle"><?php echo esc_html( $subtitle ); ?></p>
	<?php
}

/**
 * Register this addon's WooCommerce hook for the RockyJam Templates Hook Manager.
 *
 * By default we attach it just after the main title (priority 6), but
 * you can change/disable it per-template in the Hooks UI.
 */
add_filter( 'rockyjam_addon_hooks', function ( array $hooks ): array {
	$hooks[] = [
		'addon_id'   => 'product-subtitle',
		'addon_name' => 'Product Subtitle',
		'hook'       => 'woocommerce_single_product_summary',
		'function'   => 'rockyjam_product_subtitle_render',
		'priority'   => 6,
		'label'      => 'Product Subtitle (under title)',
	];

	return $hooks;
} );
