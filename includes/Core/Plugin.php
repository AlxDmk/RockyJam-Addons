<?php

namespace RockyJamAddons\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin class (Singleton).
 *
 * @package RockyJamAddons
 */
final class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Addon Manager.
	 *
	 * @var AddonManager
	 */
	private AddonManager $addon_manager;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		$this->addon_manager = new AddonManager();
	}

	/**
	 * Returns the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boot the plugin: register hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'plugins_loaded', [ $this->addon_manager, 'load_addons' ], 5 );

		if ( is_admin() ) {
			$admin = new \RockyJamAddons\Admin\AdminPage( $this->addon_manager );
			$admin->register();
		}
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'rockyjam-addons',
			false,
			dirname( ROCKYJAM_ADDONS_BASENAME ) . '/languages'
		);
	}

	/**
	 * Returns the AddonManager instance.
	 *
	 * @return AddonManager
	 */
	public function addon_manager(): AddonManager {
		return $this->addon_manager;
	}
}
