<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package RockyJamAddons
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin options.
delete_option( 'rockyjam_addons_enabled' );

// Remove any transients created by the plugin.
delete_transient( 'rockyjam_addons_cache' );

// Optionally clear any custom database tables here if added in future.
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rockyjam_addons" );
