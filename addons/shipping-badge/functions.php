<?php
/**
 * Addon: Shipping Badge — functions.php
 * Render callback + hook registration.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rockyjam_shipping_badge_render' ) ) {
    /**
     * Render the shipping badge for the current product.
     */
    function rockyjam_shipping_badge_render() {
        global $product;
        if ( ! $product ) return;

        $badge_text = get_post_meta( $product->get_id(), '_rj_shipping_badge_text', true );
        if ( empty( $badge_text ) ) return;

        echo '<div class="rj-shipping-badge-wrap">';
        echo '<span class="rj-badge-shipping">' . esc_html( $badge_text ) . '</span>';
        echo '</div>';
    }
}

/**
 * Register hooks provided by this addon so the Hook Editor can see them.
 */
add_filter( 'rockyjam_addon_hooks', function( $hooks ) {
    $hooks[] = array(
        'addon_id'   => 'shipping-badge',
        'addon_name' => 'Shipping Badge',
        'hook'       => 'woocommerce_single_product_summary',
        'function'   => 'rockyjam_shipping_badge_render',
        'priority'   => 3,
        'label'      => __( 'Shipping Badge', 'rockyjam-addons' ),
    );
    return $hooks;
} );
