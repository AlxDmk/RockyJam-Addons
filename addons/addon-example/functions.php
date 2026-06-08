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
