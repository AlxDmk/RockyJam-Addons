<?php
/**
 * Addon Name: Example Addon
 * Addon ID:   addon-example
 * Version:    1.0.0
 * Description: A sample addon demonstrating the RockyJam Addons framework.
 * Icon:        dashicons-star-filled
 * Author:      AlxDmk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load addon helpers and hooks.
require_once __DIR__ . '/functions.php';

// Register this addon with the manager.
rockyjam_addons()->addon_manager()->register(
	'addon-example',
	[
		'name'        => __( 'Example Addon', 'rockyjam-addons' ),
		'description' => __( 'A sample addon demonstrating the RockyJam Addons framework.', 'rockyjam-addons' ),
		'version'     => '1.0.0',
		'icon'        => 'dashicons-star-filled',
		'author'      => 'AlxDmk',
	]
);

// Enqueue addon assets.
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'rockyjam-addon-example',
		plugin_dir_url( __FILE__ ) . 'assets/style.css',
		[],
		'1.0.0'
	);
	wp_enqueue_script(
		'rockyjam-addon-example',
		plugin_dir_url( __FILE__ ) . 'assets/script.js',
		[ 'jquery' ],
		'1.0.0',
		true
	);
} );

// Example: add a custom body class on the frontend.
add_filter( 'body_class', function ( array $classes ): array {
	if ( ! is_admin() ) {
		$classes[] = 'rockyjam-addon-example-active';
	}
	return $classes;
} );
