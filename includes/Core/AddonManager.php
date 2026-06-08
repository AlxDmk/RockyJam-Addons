<?php

namespace RockyJamAddons\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages loading and registration of addons.
 *
 * @package RockyJamAddons
 */
class AddonManager {

	/**
	 * Registered addons.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $addons = [];

	/**
	 * Load all addons from the addons directory.
	 *
	 * @return void
	 */
	public function load_addons(): void {
		$addons_dir = ROCKYJAM_ADDONS_PATH . 'addons/';

		if ( ! is_dir( $addons_dir ) ) {
			return;
		}

		$addon_folders = glob( $addons_dir . '*', GLOB_ONLYDIR );

		if ( empty( $addon_folders ) ) {
			return;
		}

		foreach ( $addon_folders as $folder ) {
			$addon_id   = basename( $folder );
			$addon_file = $folder . '/addon.php';

			if ( ! file_exists( $addon_file ) ) {
				continue;
			}

			if ( ! $this->is_addon_enabled( $addon_id ) ) {
				continue;
			}

			require_once $addon_file;
			$this->addons[ $addon_id ]['loaded'] = true;
		}
	}

	/**
	 * Register an addon.
	 *
	 * @param string $id   Addon slug.
	 * @param array  $data Addon metadata.
	 * @return void
	 */
	public function register( string $id, array $data ): void {
		$this->addons[ $id ] = array_merge(
			[
				'name'    => $id,
				'version' => '1.0.0',
				'loaded'  => false,
			],
			$data
		);
	}

	/**
	 * Check if an addon is enabled in settings.
	 *
	 * @param string $addon_id Addon slug.
	 * @return bool
	 */
	public function is_addon_enabled( string $addon_id ): bool {
		$enabled = get_option( 'rockyjam_addons_enabled', [] );

		if ( empty( $enabled ) ) {
			// All addons enabled by default if no settings saved.
			return true;
		}

		return in_array( $addon_id, (array) $enabled, true );
	}

	/**
	 * Returns all registered addons.
	 *
	 * @return array
	 */
	public function get_addons(): array {
		return $this->addons;
	}

	/**
	 * Scan addons directory and return all available addon slugs.
	 *
	 * @return array
	 */
	public function get_available_addons(): array {
		$addons_dir = ROCKYJAM_ADDONS_PATH . 'addons/';
		$available  = [];

		if ( ! is_dir( $addons_dir ) ) {
			return $available;
		}

		$folders = glob( $addons_dir . '*', GLOB_ONLYDIR );

		foreach ( (array) $folders as $folder ) {
			$addon_id = basename( $folder );
			if ( file_exists( $folder . '/addon.php' ) ) {
				$available[] = $addon_id;
			}
		}

		return $available;
	}
}
