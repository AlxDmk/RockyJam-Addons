<?php
/**
 * Addon: Product Badges — functions.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rockyjam_product_badges_render' ) ) {
    /**
     * Render trust badges for the current product.
     */
    function rockyjam_product_badges_render() {
        global $product;
        if ( ! $product ) return;

        $badges = get_post_meta( $product->get_id(), '_rj_product_badges', true );
        if ( empty( $badges ) || ! is_array( $badges ) ) return;

        echo '<div class="rj-trust-badges">';
        foreach ( $badges as $badge ) {
            $title = isset( $badge['title'] ) ? $badge['title'] : '';
            $text  = isset( $badge['text'] )  ? $badge['text']  : '';
            if ( empty( $title ) ) continue;

            echo '<div class="rj-trust-badge">';
            echo '<div class="rj-trust-badge-title">' . esc_html( $title ) . '</div>';
            if ( $text ) {
                echo '<div class="rj-trust-badge-text">' . esc_html( $text ) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}

add_filter( 'rockyjam_addon_hooks', function( $hooks ) {
    $hooks[] = array(
        'name'        => 'rockyjam_product_badges_render',
        'label'       => __( 'Product Badges', 'rockyjam-addons' ),
        'description' => __( 'Renders trust badges (Made in USA, Warranty, etc.) on the product page.', 'rockyjam-addons' ),
        'addon'       => 'product-badges',
        'hook'        => 'woocommerce_single_product_summary',
        'priority'    => 45,
    );
    return $hooks;
} );
