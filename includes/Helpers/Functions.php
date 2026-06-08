<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper: get the plugin version.
 *
 * @return string
 */
function rockyjam_addons_version(): string {
	return ROCKYJAM_ADDONS_VERSION;
}

/**
 * Helper: get a full URL to a plugin asset.
 *
 * @param string $path Relative path inside plugin folder.
 * @return string
 */
function rockyjam_addons_asset_url( string $path ): string {
	return ROCKYJAM_ADDONS_URL . ltrim( $path, '/' );
}

/**
 * Helper: check if a specific addon is active.
 *
 * @param string $addon_id Addon slug.
 * @return bool
 */
function rockyjam_is_addon_active( string $addon_id ): bool {
	return rockyjam_addons()->addon_manager()->is_addon_enabled( $addon_id );
}

/**
 * Helper: log a debug message (only when WP_DEBUG_LOG is enabled).
 *
 * @param mixed  $message Message to log.
 * @param string $context Optional context label.
 * @return void
 */
function rockyjam_log( $message, string $context = 'RockyJam Addons' ): void {
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[' . $context . '] ' . print_r( $message, true ) );
	}
}
