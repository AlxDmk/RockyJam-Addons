<?php
/**
 * Addon Name:  Key Features
 * Addon ID:    key-features
 * Version:     1.0.0
 * Description: Displays a "Key Features" list on the single product page. Features are managed per-product in the product editor.
 * Icon:        dashicons-yes-alt
 * Author:
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load helper functions and meta-box.
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/meta-box.php';

// Register with the plugin manager.
rockyjam_addons()->addon_manager()->register(
	'key-features',
	[
		'name'        => __( 'Key Features', 'rockyjam-addons' ),
		'description' => __( 'Displays a "Key Features" list on the single product page.', 'rockyjam-addons' ),
		'version'     => '1.0.0',
		'icon'        => 'dashicons-yes-alt',
	]
);

// ── Frontend assets ───────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_product() ) {
		return;
	}
	wp_enqueue_style(
		'rockyjam-key-features',
		plugin_dir_url( __FILE__ ) . 'assets/style.css',
		[],
		'1.0.0'
	);
	// Dashicons needed for the checkmark icon on frontend.
	wp_enqueue_style( 'dashicons' );
} );

// ── Admin assets (meta-box) ───────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', function ( string $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	global $post;
	if ( ! $post || 'product' !== get_post_type( $post ) ) {
		return;
	}
	wp_enqueue_style(
		'rockyjam-key-features-admin',
		plugin_dir_url( __FILE__ ) . 'assets/admin.css',
		[],
		'1.0.0'
	);
	wp_enqueue_script(
		'rockyjam-key-features-admin',
		plugin_dir_url( __FILE__ ) . 'assets/script.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		'1.0.0',
		true
	);
} );
