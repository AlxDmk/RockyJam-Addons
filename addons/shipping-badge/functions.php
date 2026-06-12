<?php
/**
 * Addon: Shipping Badge — functions.php
 * Render callback + hook registration.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rockyjam_shipping_badge_render' ) ) {
    /**
     * Render the shipping badges for the current product.
     *
     * Supports multiple badges stored as JSON in _rj_shipping_badges.
     * Falls back to legacy single-text meta _rj_shipping_badge_text.
     * Also prepends an automatic "Sale" badge when the product is on sale.
     */
    function rockyjam_shipping_badge_render() {
        global $product;
        if ( ! $product ) return;

        $product_id = $product->get_id();

        // New multi-badge format.
        $raw   = get_post_meta( $product_id, '_rj_shipping_badges', true );
        $items = array();

        if ( is_string( $raw ) && '' !== $raw ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $items = $decoded;
            }
        }

        // Legacy single badge fallback.
        if ( empty( $items ) ) {
            $legacy = get_post_meta( $product_id, '_rj_shipping_badge_text', true );
            if ( ! empty( $legacy ) ) {
                $items = array(
                    array(
                        'text'  => $legacy,
                        'bg'    => 'rj-badge-bg-default',
                        'title' => '',
                    ),
                );
            }
        }

        // Automatic "Sale" badge at the first position when product is on sale.
        if ( $product->is_on_sale() ) {
            $sale_badge = array(
                'text'  => __( 'Sale', 'rockyjam-addons' ),
                'bg'    => 'rj-badge-bg-orange',
                'title' => '',
            );

            // Prepend sale badge only if there is no existing badge with exactly same text.
            $has_sale = false;
            foreach ( $items as $item ) {
                if ( isset( $item['text'] ) && $item['text'] === $sale_badge['text'] ) {
                    $has_sale = true;
                    break;
                }
            }

            if ( ! $has_sale ) {
                array_unshift( $items, $sale_badge );
            }
        }

        if ( empty( $items ) ) return;

        echo '<div class="rj-shipping-badge-wrap">';

        foreach ( $items as $item ) {
            $text  = isset( $item['text'] ) ? $item['text'] : '';
            $bg    = isset( $item['bg'] ) ? $item['bg'] : 'rj-badge-bg-default';
            $title = isset( $item['title'] ) ? $item['title'] : '';

            if ( '' === $text ) {
                continue;
            }

            $classes = array( 'rj-badge-shipping', $bg );
            $attrs   = array();

            if ( '' !== $title ) {
                $attrs[] = 'title="' . esc_attr( $title ) . '"';
            }

            echo '<span class="' . esc_attr( implode( ' ', $classes ) ) . '" ' . implode( ' ', $attrs ) . '>' . esc_html( $text ) . '</span>';
        }

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
