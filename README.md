# RockyJam Addons

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple)](https://php.net)

Modular WordPress plugin framework for RockyJam features. Each addon lives in its own directory and can be enabled or disabled independently from the WordPress admin.

## Project Structure

```
RockyJam-Addons/
├─ rockyjam-addons.php          # Main plugin file
├─ uninstall.php                # Cleanup on uninstall
├─ composer.json                # PSR-4 autoloading
├─ readme.txt                   # WordPress.org readme
├─ README.md                    # This file
├─ includes/
│  ├─ Core/
│  │  ├─ Plugin.php             # Singleton bootstrap
│  │  └─ AddonManager.php       # Addon loader & registry
│  ├─ Admin/
│  │  └─ AdminPage.php          # Settings page
│  └─ Helpers/
│     └─ Functions.php          # Global helper functions
└─ addons/
   └─ addon-example/
      └─ addon.php              # Example addon
```

## Requirements

- WordPress 6.0+
- PHP 8.0+
- Composer (optional, for PSR-4 autoloading)

## Installation

1. Clone or download this repository into `wp-content/plugins/rockyjam-addons/`.
2. Activate the plugin in **Plugins > Installed Plugins**.
3. Go to **Settings > RockyJam Addons** to enable/disable individual addons.

### With Composer (recommended)

```bash
cd wp-content/plugins/rockyjam-addons
composer install
```

## Creating a New Addon

1. Create a new folder inside `addons/`, e.g. `addons/my-feature/`.
2. Add an `addon.php` file inside it.
3. Register your addon at the top of `addon.php`:

```php
rockyjam_addons()->addon_manager()->register(
    'my-feature',
    [
        'name'        => __( 'My Feature', 'rockyjam-addons' ),
        'description' => __( 'Description of what this addon does.', 'rockyjam-addons' ),
        'version'     => '1.0.0',
    ]
);
```

4. Add your WordPress hooks and logic below the registration call.
5. Enable the addon in **Settings > RockyJam Addons**.

## Helper Functions

| Function | Description |
|---|---|
| `rockyjam_addons()` | Returns the main plugin instance |
| `rockyjam_addons_version()` | Returns the current plugin version |
| `rockyjam_addons_asset_url( $path )` | Returns URL to a plugin asset |
| `rockyjam_is_addon_active( $id )` | Checks if a specific addon is enabled |
| `rockyjam_log( $message )` | Debug logger (respects WP_DEBUG_LOG) |

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
