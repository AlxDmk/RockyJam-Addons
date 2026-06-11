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
        'name'        => 'rockyjam_shipping_badge_render',
        'label'       => __( 'Shipping Badge', 'rockyjam-addons' ),
        'description' => __( 'Renders a shipping badge above the product title.', 'rockyjam-addons' ),
        'addon'       => 'shipping-badge',
        'hook'        => 'woocommerce_single_product_summary',
        'priority'    => 3,
    );
    return $hooks;
} );
