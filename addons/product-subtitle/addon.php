<?php
/**
 * Addon Name: Product Subtitle
 * Addon ID:   product-subtitle
 * Version:    1.0.0
 * Description: Adds a custom product subtitle field and renders it under the product title.
 * Icon:        dashicons-editor-italic
 * Author:      AlxDmk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load addon helpers and hooks.
require_once __DIR__ . '/functions.php';

// Register this addon with the RockyJam Addons manager.
rockyjam_addons()->addon_manager()->register(
	'product-subtitle',
	[
		'name'        => __( 'Product Subtitle', 'rockyjam-addons' ),
		'description' => __( 'Adds a custom product subtitle field and renders it under the product title.', 'rockyjam-addons' ),
		'version'     => '1.0.0',
		'icon'        => 'dashicons-editor-italic',
		'author'      => 'AlxDmk',
	]
);
