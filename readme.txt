=== RockyJam Addons ===
Contributors: AlxDmk
Tags: addons, modular, rockyjam
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modular WordPress addon framework for RockyJam features.

== Description ==

RockyJam Addons is a modular WordPress plugin that provides a clean framework for building and managing feature addons.

Each addon lives in its own directory inside the `addons/` folder and can be enabled or disabled independently from the admin settings page at **Settings > RockyJam Addons**.

**Features:**

* Modular addon architecture
* Admin page to enable/disable individual addons
* PSR-4 autoloading via Composer
* Fallback autoloading without Composer
* WordPress Coding Standards compliant

== Installation ==

1. Upload the `rockyjam-addons` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > RockyJam Addons** to configure addons.
4. Optionally run `composer install` in the plugin folder for PSR-4 autoloading.

== Changelog ==

= 0.1.0 =
* Initial release with modular addon framework.
* Admin settings page to enable/disable addons.
* Example addon included.
