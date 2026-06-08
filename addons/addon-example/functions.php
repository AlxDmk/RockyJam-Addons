<?php
/**
 * Example Addon — helper functions.
 *
 * Place addon-specific functions here.
 * This file is included by addon.php before the addon is registered,
 * so functions defined here are available throughout WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a greeting string from the Example Addon.
 *
 * Usage anywhere in your theme or other addons:
 *   echo rockyjam_example_hello();
 *
 * @return string
 */
function rockyjam_example_hello(): string {
	return esc_html__( 'Hello from Example Addon!', 'rockyjam-addons' );
}

/**
 * Example WooCommerce hook: outputs a custom block after the product title.
 * Priority 6 — just after the title (priority 5).
 */
function rockyjam_example_after_title(): void {
	echo '<div class="rockyjam-example-notice">';
	echo '<p>' . esc_html__( 'This block is added by Example Addon.', 'rockyjam-addons' ) . '</p>';
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'rockyjam_example_after_title', 6 );

/**
 * Declare this addon's WC-hooks to the RockyJam Templates Hook Manager.
 *
 * This filter lets the Hooks Editor (Templates plugin) show and manage
 * functions from this addon — including toggling them on/off and
 * changing their priorities from the UI.
 */
add_filter( 'rockyjam_addon_hooks', function ( array $hooks ): array {
	$hooks[] = [
		'addon_id'   => 'addon-example',
		'addon_name' => 'Example Addon',
		'hook'       => 'woocommerce_single_product_summary',
		'function'   => 'rockyjam_example_after_title',
		'priority'   => 6,
		'label'      => 'After Title Block (Example Addon)',
	];
	return $hooks;
} );
