<?php
/**
 * Addon: Product FAQ
 * Slug: product-faq
 * Description: Accordion FAQ section below the product tabs.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rockyjam_register_addons', function( $addons ) {
    $addons['product-faq'] = array(
        'name'        => __( 'Product FAQ', 'rockyjam-addons' ),
        'description' => __( 'Accordion FAQ section displayed below product tabs.', 'rockyjam-addons' ),
        'version'     => '1.0.0',
        'author'      => 'RockyJam',
    );
    return $addons;
} );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_product() ) return;
    wp_enqueue_style(
        'rja-product-faq',
        plugin_dir_url( __FILE__ ) . 'assets/style.css',
        array(),
        '1.0.0'
    );
    wp_enqueue_script(
        'rja-product-faq',
        plugin_dir_url( __FILE__ ) . 'assets/script.js',
        array(),
        '1.0.0',
        true
    );
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    global $post;
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
    if ( ! $post || 'product' !== get_post_type( $post ) ) return;
    wp_enqueue_style( 'rja-product-faq-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.css', array(), '1.0.0' );
    wp_enqueue_script( 'rja-product-faq-admin', plugin_dir_url( __FILE__ ) . 'assets/script.js', array( 'jquery' ), '1.0.0', true );
} );

require_once __DIR__ . '/meta-box.php';
require_once __DIR__ . '/functions.php';
