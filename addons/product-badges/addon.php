<?php
/**
 * Addon: Product Badges
 * Slug: product-badges
 * Description: Displays trust badges (e.g. "Made in USA", "Warranty") on the product page.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rockyjam_register_addons', function( $addons ) {
    $addons['product-badges'] = array(
        'name'        => __( 'Product Badges', 'rockyjam-addons' ),
        'description' => __( 'Displays configurable trust badges (Made in USA, Warranty, etc.) on product pages.', 'rockyjam-addons' ),
        'version'     => '1.0.0',
        'author'      => 'RockyJam',
    );
    return $addons;
} );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_product() ) return;
    wp_enqueue_style(
        'rja-product-badges',
        plugin_dir_url( __FILE__ ) . 'assets/style.css',
        array(),
        '1.0.0'
    );
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    global $post;
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
    if ( ! $post || 'product' !== get_post_type( $post ) ) return;
    wp_enqueue_style( 'rja-product-badges-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.css', array(), '1.0.0' );
    wp_enqueue_script( 'rja-product-badges-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array( 'jquery' ), '1.0.0', true );
} );

require_once __DIR__ . '/meta-box.php';
require_once __DIR__ . '/functions.php';
