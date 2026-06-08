<?php
/**
 * Plugin Name: RockyJam Addons
 * Plugin URI: https://github.com/AlxDmk/RockyJam-Addons
 * Description: Modular WordPress addon framework for RockyJam features.
 * Version: 0.1.0
 * Author: AlxDmk
 * Author URI: https://github.com/AlxDmk
 * Text Domain: rockyjam-addons
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ROCKYJAM_ADDONS_VERSION', '0.1.0' );
define( 'ROCKYJAM_ADDONS_FILE', __FILE__ );
define( 'ROCKYJAM_ADDONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ROCKYJAM_ADDONS_URL', plugin_dir_url( __FILE__ ) );
define( 'ROCKYJAM_ADDONS_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader.
if ( file_exists( ROCKYJAM_ADDONS_PATH . 'vendor/autoload.php' ) ) {
	require_once ROCKYJAM_ADDONS_PATH . 'vendor/autoload.php';
} else {
	require_once ROCKYJAM_ADDONS_PATH . 'includes/Core/Plugin.php';
	require_once ROCKYJAM_ADDONS_PATH . 'includes/Core/AddonManager.php';
	require_once ROCKYJAM_ADDONS_PATH . 'includes/Admin/AdminPage.php';
	require_once ROCKYJAM_ADDONS_PATH . 'includes/Helpers/Functions.php';
}

/**
 * Returns the main plugin instance.
 *
 * @return \RockyJamAddons\Core\Plugin
 */
function rockyjam_addons() {
	return \RockyJamAddons\Core\Plugin::instance();
}

rockyjam_addons()->boot();

register_activation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );
