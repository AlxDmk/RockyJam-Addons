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

	/** @var array<string, array<string, mixed>> Registered addons (runtime). */
	private array $addons = [];

	// =========================================================================
	// Loading
	// =========================================================================

	/**
	 * Load all enabled addons from the addons/ directory.
	 * Hooked to 'init' priority 5 (after load_textdomain at priority 1).
	 */
	public function load_addons(): void {
		$addons_dir = ROCKYJAM_ADDONS_PATH . 'addons/';

		if ( ! is_dir( $addons_dir ) ) {
			return;
		}

		foreach ( (array) glob( $addons_dir . '*', GLOB_ONLYDIR ) as $folder ) {
			$addon_id   = basename( $folder );
			$addon_file = $folder . '/addon.php';

			if ( ! file_exists( $addon_file ) ) {
				continue;
			}
			if ( ! $this->is_addon_enabled( $addon_id ) ) {
				continue;
			}

			require_once $addon_file;
			if ( isset( $this->addons[ $addon_id ] ) ) {
				$this->addons[ $addon_id ]['loaded'] = true;
			}
		}
	}

	// =========================================================================
	// Registration (called from each addon.php)
	// =========================================================================

	/**
	 * Register an addon. Called from addon.php.
	 *
	 * @param string               $id   Addon slug.
	 * @param array<string, mixed> $data Metadata: name, description, version, icon, author.
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

	// =========================================================================
	// Metadata
	// =========================================================================

	/**
	 * Read file-header metadata from an addon.php without executing it.
	 * Supports: Addon Name, Version, Description, Icon, Author.
	 *
	 * @param  string               $addon_id Addon slug.
	 * @return array<string, string>
	 */
	public function get_addon_meta( string $addon_id ): array {
		$file = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/addon.php';

		$meta = [
			'name'        => $addon_id,
			'version'     => '1.0.0',
			'description' => '',
			'icon'        => 'dashicons-admin-plugins',
			'author'      => '',
		];

		if ( ! file_exists( $file ) ) {
			return $meta;
		}

		// Read only the header block (first 8 KB is more than enough).
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
				$meta[ $key ] = trim( $m[1] );
			}
		}

		return $meta;
	}

	/**
	 * Return all addon slugs that have an addon.php on disk.
	 *
	 * @return string[]
	 */
	public function get_available_addons(): array {
		$addons_dir = ROCKYJAM_ADDONS_PATH . 'addons/';

		if ( ! is_dir( $addons_dir ) ) {
			return [];
		}

		$list = [];
		foreach ( (array) glob( $addons_dir . '*', GLOB_ONLYDIR ) as $folder ) {
			$addon_id = basename( $folder );
			if ( file_exists( $folder . '/addon.php' ) ) {
				$list[] = $addon_id;
			}
		}

		return $list;
	}

	// =========================================================================
	// Enabled / disabled state (stored in wp_options)
	// =========================================================================

	/**
	 * Check whether an addon is in the enabled list.
	 *
	 * When the option is empty (never saved) every addon is considered enabled
	 * by default, matching the "install and it just works" expectation.
	 *
	 * @param  string $addon_id Addon slug.
	 * @return bool
	 */
	public function is_addon_enabled( string $addon_id ): bool {
		$enabled = get_option( 'rockyjam_addons_enabled', null );

		// Option has never been saved → all addons on by default.
		if ( null === $enabled || '' === $enabled ) {
			return true;
		}

		return in_array( $addon_id, (array) $enabled, true );
	}

	/**
	 * Persist the full list of enabled addon slugs.
	 *
	 * @param string[] $addon_ids
	 */
	public function set_enabled_addons( array $addon_ids ): void {
		update_option( 'rockyjam_addons_enabled', array_values( array_map( 'sanitize_key', $addon_ids ) ) );
	}

	// =========================================================================
	// Create
	// =========================================================================

	/**
	 * Scaffold a new addon on disk and mark it as enabled.
	 *
	 * Creates:
	 *   addons/{id}/addon.php
	 *   addons/{id}/functions.php
	 *   addons/{id}/assets/style.css
	 *   addons/{id}/assets/script.js
	 *
	 * @param  string $addon_id    Slug (a-z, 0-9, hyphens).
	 * @param  string $addon_name  Human-readable name.
	 * @param  string $description Short description.
	 * @param  string $icon        Dashicons class.
	 * @return true|\WP_Error
	 */
	public function create_addon(
		string $addon_id,
		string $addon_name,
		string $description = '',
		string $icon = 'dashicons-admin-plugins'
	) {
		$addon_id = sanitize_key( $addon_id );

		if ( empty( $addon_id ) ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid addon ID.', 'rockyjam-addons' ) );
		}

		$dir = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/';

		if ( is_dir( $dir ) ) {
			return new \WP_Error( 'already_exists', __( 'An addon with this ID already exists.', 'rockyjam-addons' ) );
		}

		// Create directory tree: addons/{id}/assets/
		if ( ! wp_mkdir_p( $dir . 'assets/' ) ) {
			return new \WP_Error( 'mkdir_failed', __( 'Could not create addon directory. Check filesystem permissions.', 'rockyjam-addons' ) );
		}

		// Safe values for use inside generated PHP strings.
		// We intentionally do NOT use esc_attr() here — that would inject
		// HTML entities (&amp; etc.) into PHP source code.
		$safe_name = wp_strip_all_tags( $addon_name );
		$safe_desc = wp_strip_all_tags( $description );
		$safe_icon = sanitize_text_field( $icon );
		$ver       = '1.0.0';

		// PascalCase class prefix, e.g. "my-addon" → "MyAddon"
		$class_prefix = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $addon_id ) ) );

		// -----------------------------------------------------------------
		// addon.php
		// -----------------------------------------------------------------
		$addon_php = '<?php' . "\n";
		$addon_php .= '/**' . "\n";
		$addon_php .= ' * Addon Name: ' . $safe_name . "\n";
		$addon_php .= ' * Addon ID:   ' . $addon_id . "\n";
		$addon_php .= ' * Version:    ' . $ver . "\n";
		$addon_php .= ' * Description: ' . $safe_desc . "\n";
		$addon_php .= ' * Icon:        ' . $safe_icon . "\n";
		$addon_php .= ' * Author:      ' . "\n";
		$addon_php .= ' */' . "\n\n";
		$addon_php .= "if ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n";
		$addon_php .= "// Load addon helpers.\n";
		$addon_php .= "require_once __DIR__ . '/functions.php';\n\n";
		$addon_php .= "// Register with the plugin manager.\n";
		$addon_php .= "rockyjam_addons()->addon_manager()->register(\n";
		$addon_php .= "\t'" . $addon_id . "',\n";
		$addon_php .= "\t[\n";
		$addon_php .= "\t\t'name'        => __('" . addslashes( $safe_name ) . "', 'rockyjam-addons'),\n";
		$addon_php .= "\t\t'description' => __('" . addslashes( $safe_desc ) . "', 'rockyjam-addons'),\n";
		$addon_php .= "\t\t'version'     => '" . $ver . "',\n";
		$addon_php .= "\t\t'icon'        => '" . $safe_icon . "',\n";
		$addon_php .= "\t]\n";
		$addon_php .= ");\n\n";
		$addon_php .= "// Enqueue frontend assets.\n";
		$addon_php .= "add_action( 'wp_enqueue_scripts', function () {\n";
		$addon_php .= "\twp_enqueue_style(\n";
		$addon_php .= "\t\t'rockyjam-" . $addon_id . "',\n";
		$addon_php .= "\t\tplugin_dir_url( __FILE__ ) . 'assets/style.css',\n";
		$addon_php .= "\t\t[],\n";
		$addon_php .= "\t\t'" . $ver . "'\n";
		$addon_php .= "\t);\n";
		$addon_php .= "\twp_enqueue_script(\n";
		$addon_php .= "\t\t'rockyjam-" . $addon_id . "',\n";
		$addon_php .= "\t\tplugin_dir_url( __FILE__ ) . 'assets/script.js',\n";
		$addon_php .= "\t\t[ 'jquery' ],\n";
		$addon_php .= "\t\t'" . $ver . "',\n";
		$addon_php .= "\t\ttrue\n";
		$addon_php .= "\t);\n";
		$addon_php .= "} );\n";

		// -----------------------------------------------------------------
		// functions.php
		// -----------------------------------------------------------------
		$functions_php = '<?php' . "\n";
		$functions_php .= '/**' . "\n";
		$functions_php .= ' * ' . $safe_name . " — helper functions.\n";
		$functions_php .= ' *' . "\n";
		$functions_php .= ' * Add your addon-specific functions here.' . "\n";
		$functions_php .= ' * This file is included by addon.php on every page load.' . "\n";
		$functions_php .= ' */' . "\n\n";
		$functions_php .= "if ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n";
		$functions_php .= '/**' . "\n";
		$functions_php .= ' * Example public function for the ' . $safe_name . " addon.\n";
		$functions_php .= ' * Call it anywhere: rockyjam_' . $class_prefix . "_hello();\n";
		$functions_php .= ' *' . "\n";
		$functions_php .= ' * @return string' . "\n";
		$functions_php .= ' */' . "\n";
		$functions_php .= 'function rockyjam_' . $class_prefix . "_hello(): string {\n";
		$functions_php .= "\treturn esc_html__( 'Hello from " . addslashes( $safe_name ) . "!', 'rockyjam-addons' );\n";
		$functions_php .= "}\n";

		// -----------------------------------------------------------------
		// assets/style.css
		// -----------------------------------------------------------------
		$style_css  = "/**\n";
		$style_css .= " * " . $safe_name . " — frontend styles.\n";
		$style_css .= " */\n\n";
		$style_css .= ".rockyjam-" . $addon_id . " {\n";
		$style_css .= "\t/* Add your styles here */\n";
		$style_css .= "}\n";

		// -----------------------------------------------------------------
		// assets/script.js
		// -----------------------------------------------------------------
		$script_js  = "/**\n";
		$script_js .= " * " . $safe_name . " — frontend scripts.\n";
		$script_js .= " */\n";
		$script_js .= "( function ( $ ) {\n";
		$script_js .= "\t'use strict';\n\n";
		$script_js .= "\t$( document ).ready( function () {\n";
		$script_js .= "\t\t// Add your scripts here.\n";
		$script_js .= "\t} );\n";
		$script_js .= "} )( jQuery );\n";

		// -----------------------------------------------------------------
		// Write all files
		// -----------------------------------------------------------------
		$files = [
			$dir . 'addon.php'         => $addon_php,
			$dir . 'functions.php'     => $functions_php,
			$dir . 'assets/style.css'  => $style_css,
			$dir . 'assets/script.js'  => $script_js,
		];

		foreach ( $files as $path => $content ) {
			if ( false === file_put_contents( $path, $content ) ) {
				// Clean up partially-created directory on failure.
				$this->rmdir_recursive( $dir );
				return new \WP_Error(
					'write_failed',
					/* translators: %s: file path */
					sprintf( __( 'Could not write file: %s', 'rockyjam-addons' ), $path )
				);
			}
		}

		// -----------------------------------------------------------------
		// Auto-enable the new addon in the stored list.
		// -----------------------------------------------------------------
		$enabled = get_option( 'rockyjam_addons_enabled', null );
		// If the option already exists (user has saved settings before),
		// add the new addon to it so it shows as active immediately.
		if ( null !== $enabled && '' !== $enabled ) {
			$enabled = (array) $enabled;
			if ( ! in_array( $addon_id, $enabled, true ) ) {
				$enabled[] = $addon_id;
				update_option( 'rockyjam_addons_enabled', array_values( $enabled ) );
			}
		}
		// If the option was never saved, all addons are on by default —
		// nothing to update.

		return true;
	}

	// =========================================================================
	// Delete
	// =========================================================================

	/**
	 * Permanently delete an addon directory and remove it from the enabled list.
	 *
	 * @param  string $addon_id Addon slug.
	 * @return true|\WP_Error
	 */
	public function delete_addon( string $addon_id ) {
		$addon_id = sanitize_key( $addon_id );

		if ( empty( $addon_id ) ) {
			return new \WP_Error( 'invalid_id', __( 'Invalid addon ID.', 'rockyjam-addons' ) );
		}

		$dir = ROCKYJAM_ADDONS_PATH . 'addons/' . $addon_id . '/';

		if ( ! is_dir( $dir ) ) {
			return new \WP_Error( 'not_found', __( 'Addon directory not found.', 'rockyjam-addons' ) );
		}

		$this->rmdir_recursive( $dir );

		// Remove from enabled list (if it was there).
		$enabled = (array) get_option( 'rockyjam_addons_enabled', [] );
		$enabled = array_values( array_filter( $enabled, fn( $id ) => $id !== $addon_id ) );
		update_option( 'rockyjam_addons_enabled', $enabled );

		return true;
	}

	// =========================================================================
	// Internals
	// =========================================================================

	/**
	 * Recursively remove a directory and all its contents.
	 *
	 * @param string $dir Absolute path (with trailing slash).
	 */
	private function rmdir_recursive( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$items = array_diff( (array) scandir( $dir ), [ '.', '..' ] );
		foreach ( $items as $item ) {
			$path = rtrim( $dir, '/' ) . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->rmdir_recursive( $path . '/' );
			} else {
				wp_delete_file( $path );
			}
		}

		rmdir( $dir );
	}

	/**
	 * Returns all registered addons (runtime, only loaded ones).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_addons(): array {
		return $this->addons;
	}
}
