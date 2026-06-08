<?php

namespace RockyJamAddons\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages loading, registration, creation and deletion of addons.
 *
 * @package RockyJamAddons
 */
class AddonManager {

	/**
	 * Registered addons (loaded at runtime).
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private array $addons = [];

	/**
	 * Load all enabled addons from the addons directory.
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
	 * Register an addon (called from addon.php).
	 *
	 * @param string $id   Addon slug.
	 * @param array  $data Addon metadata.
	 * @return void
	 */
	public function register( string $id, array $data ): void {
		$this->addons[ $id ] = array_merge(
			[
				'name'        => $id,
				'version'     => '1.0.0',
				'description' => '',
				'icon'        => 'dashicons-admin-plugins',
				'author'      => '',
				'loaded'      => false,
			],
			$data
		);
	}

	/**
	 * Read header metadata from an addon.php file without executing it.
	 * Supports: Addon Name, Version, Description, Icon, Author.
	 *
	 * @param string $addon_id Addon slug.
	 * @return array<string, string>
	 */
	public function get_addon_meta( string $addon_id ): array {
		$file = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/addon.php';

		if ( ! file_exists( $file ) ) {
			return [];
		}

		$default = [
			'name'        => $addon_id,
			'version'     => '1.0.0',
			'description' => '',
			'icon'        => 'dashicons-admin-plugins',
			'author'      => '',
		];

		// Read only the first 8 KB — enough for the file header.
		$fp      = fopen( $file, 'r' );
		$content = fread( $fp, 8192 );
		fclose( $fp );

		$map = [
			'Addon Name'  => 'name',
			'Version'     => 'version',
			'Description' => 'description',
			'Icon'        => 'icon',
			'Author'      => 'author',
		];

		foreach ( $map as $header => $key ) {
			if ( preg_match( '/' . preg_quote( $header, '/' ) . '\s*:\s*(.+)/i', $content, $m ) ) {
				$default[ $key ] = trim( $m[1] );
			}
		}

		return $default;
	}

	/**
	 * Return all available addon slugs (directories containing addon.php).
	 *
	 * @return string[]
	 */
	public function get_available_addons(): array {
		$addons_dir = ROCKYJAM_ADDONS_PATH . 'addons/';
		$available  = [];

		if ( ! is_dir( $addons_dir ) ) {
			return $available;
		}

		foreach ( (array) glob( $addons_dir . '*', GLOB_ONLYDIR ) as $folder ) {
			$addon_id = basename( $folder );
			if ( file_exists( $folder . '/addon.php' ) ) {
				$available[] = $addon_id;
			}
		}

		return $available;
	}

	/**
	 * Check if an addon is enabled.
	 *
	 * @param string $addon_id Addon slug.
	 * @return bool
	 */
	public function is_addon_enabled( string $addon_id ): bool {
		$enabled = get_option( 'rockyjam_addons_enabled', [] );

		// All addons are on by default until the user saves settings.
		if ( empty( $enabled ) ) {
			return true;
		}

		return in_array( $addon_id, (array) $enabled, true );
	}

	/**
	 * Create a new addon with the standard file scaffold.
	 *
	 * @param string $addon_id   Slug (lowercase, hyphens only).
	 * @param string $addon_name Human-readable name.
	 * @param string $description Short description.
	 * @param string $icon       Dashicons class, e.g. dashicons-star-filled.
	 * @return true|\WP_Error
	 */
	public function create_addon( string $addon_id, string $addon_name, string $description = '', string $icon = 'dashicons-admin-plugins' ) {
		$addon_id = sanitize_key( $addon_id );

		if ( empty( $addon_id ) ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid addon ID.', 'rockyjam-addons' ) );
		}

		$dir = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/';

		if ( is_dir( $dir ) ) {
			return new \WP_Error( 'already_exists', __( 'An addon with this ID already exists.', 'rockyjam-addons' ) );
		}

		if ( ! wp_mkdir_p( $dir . 'assets/' ) ) {
			return new \WP_Error( 'mkdir_failed', __( 'Could not create addon directory.', 'rockyjam-addons' ) );
		}

		$name_esc = esc_attr( $addon_name );
		$desc_esc = esc_attr( $description );
		$ver      = '1.0.0';
		$class    = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $addon_id ) ) );

		// ---- addon.php ----
		file_put_contents( $dir . 'addon.php', <<<PHP
<?php
/**
 * Addon Name: {$name_esc}
 * Addon ID:   {$addon_id}
 * Version:    {$ver}
 * Description: {$desc_esc}
 * Icon:        {$icon}
 * Author:      
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load addon helpers and hooks.
require_once __DIR__ . '/functions.php';

// Register this addon with the manager.
rockyjam_addons()->addon_manager()->register(
	'{$addon_id}',
	[
		'name'        => __('{$name_esc}', 'rockyjam-addons'),
		'description' => __('{$desc_esc}', 'rockyjam-addons'),
		'version'     => '{$ver}',
		'icon'        => '{$icon}',
	]
);

// Enqueue addon assets.
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'rockyjam-{$addon_id}',
		plugin_dir_url( __FILE__ ) . 'assets/style.css',
		[],
		'{$ver}'
	);
	wp_enqueue_script(
		'rockyjam-{$addon_id}',
		plugin_dir_url( __FILE__ ) . 'assets/script.js',
		[ 'jquery' ],
		'{$ver}',
		true
	);
} );
PHP
		);

		// ---- functions.php ----
		file_put_contents( $dir . 'functions.php', <<<PHP
<?php
/**
 * {$name_esc} — helper functions.
 *
 * Place addon-specific functions here.
 * This file is included by addon.php before the addon is registered.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Example external function for the {$name_esc} addon.
 *
 * @return string
 */
function rockyjam_{$class}_example(): string {
	return esc_html__( 'Hello from {$name_esc}!', 'rockyjam-addons' );
}
PHP
		);

		// ---- assets/style.css ----
		file_put_contents( $dir . 'assets/style.css', <<<CSS
/**
 * {$name_esc} — frontend styles.
 */

.rockyjam-{$addon_id} {
	/* Add your styles here */
}
CSS
		);

		// ---- assets/script.js ----
		file_put_contents( $dir . 'assets/script.js', <<<JS
/**
 * {$name_esc} — frontend scripts.
 */
( function ( \$ ) {
	'use strict';

	\$( document ).ready( function () {
		// Add your scripts here.
	} );
} )( jQuery );
JS
		);

		// Auto-enable the new addon.
		$enabled = (array) get_option( 'rockyjam_addons_enabled', [] );
		if ( ! empty( $enabled ) && ! in_array( $addon_id, $enabled, true ) ) {
			$enabled[] = $addon_id;
			update_option( 'rockyjam_addons_enabled', $enabled );
		}

		return true;
	}

	/**
	 * Delete an addon directory recursively.
	 *
	 * @param string $addon_id Addon slug.
	 * @return true|\WP_Error
	 */
	public function delete_addon( string $addon_id ) {
		$addon_id = sanitize_key( $addon_id );
		$dir      = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/';

		if ( ! is_dir( $dir ) ) {
			return new \WP_Error( 'not_found', __( 'Addon not found.', 'rockyjam-addons' ) );
		}

		$this->rmdir_recursive( $dir );

		// Remove from enabled list.
		$enabled = (array) get_option( 'rockyjam_addons_enabled', [] );
		$enabled = array_values( array_filter( $enabled, fn( $id ) => $id !== $addon_id ) );
		update_option( 'rockyjam_addons_enabled', $enabled );

		return true;
	}

	/**
	 * Recursively remove a directory.
	 *
	 * @param string $dir Absolute path.
	 * @return void
	 */
	private function rmdir_recursive( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( scandir( $dir ) as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . $item;
			is_dir( $path ) ? $this->rmdir_recursive( $path . '/' ) : unlink( $path );
		}
		rmdir( $dir );
	}

	/**
	 * Returns all registered addons (runtime data).
	 *
	 * @return array
	 */
	public function get_addons(): array {
		return $this->addons;
	}
}
