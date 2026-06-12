<?php
/**
 * Addon: Shipping Badge
 * Slug: shipping-badge
 * Description: Displays shipping-related badges above the product title in the sidebar.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register addon metadata.
 */
add_filter( 'rockyjam_register_addons', function( $addons ) {
    $addons['shipping-badge'] = array(
        'name'        => __( 'Shipping Badge', 'rockyjam-addons' ),
        'description' => __( 'Displays one or more shipping-related badges above the product title.', 'rockyjam-addons' ),
        'version'     => '1.1.0',
        'author'      => 'RockyJam',
    );
    return $addons;
} );

/**
 * Enqueue frontend styles.
 */
add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_product() ) return;

    wp_enqueue_style(
        'rja-shipping-badge',
        plugin_dir_url( __FILE__ ) . 'assets/style.css',
        array(),
        '1.1.0'
    );
} );

/**
 * Admin: enqueue admin styles + scripts for the meta-box.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    global $post;
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
    if ( ! $post || 'product' !== get_post_type( $post ) ) return;

    wp_enqueue_style(
        'rja-shipping-badge-admin',
        plugin_dir_url( __FILE__ ) . 'assets/admin.css',
        array(),
        '1.1.0'
    );

    wp_enqueue_script(
        'rja-shipping-badge-admin',
        plugin_dir_url( __FILE__ ) . 'assets/admin.js',
        array( 'jquery', 'jquery-ui-sortable' ),
        '1.1.0',
        true
    );
} );

require_once __DIR__ . '/meta-box.php';
require_once __DIR__ . '/functions.php';
