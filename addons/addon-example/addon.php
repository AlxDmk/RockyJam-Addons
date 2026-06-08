<?php
/**
 * Addon Name: Example Addon
 * Addon ID:   addon-example
 * Version:    1.0.0
 * Description: A sample addon demonstrating the RockyJam Addons framework.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register this addon with the manager.
rockyjam_addons()->addon_manager()->register(
	'addon-example',
	[
		'name'        => __( 'Example Addon', 'rockyjam-addons' ),
		'description' => __( 'A sample addon demonstrating the RockyJam Addons framework.', 'rockyjam-addons' ),
		'version'     => '1.0.0',
	]
);

/**
 * Example: add a custom body class on the frontend.
 */
add_filter( 'body_class', function( array $classes ): array {
	if ( ! is_admin() ) {
		$classes[] = 'rockyjam-addon-example-active';
	}
	return $classes;
} );

/**
 * Example: log a message when this addon loads.
 */
rockyjam_log( 'addon-example loaded successfully.' );
