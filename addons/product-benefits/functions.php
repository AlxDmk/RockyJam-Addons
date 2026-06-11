<?php
/**
 * Addon: Product Benefits — functions.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rockyjam_product_benefits_render' ) ) {
    /**
     * Render the numbered benefits grid for the current product.
     */
    function rockyjam_product_benefits_render() {
        global $product;
        if ( ! $product ) return;

        $benefits = get_post_meta( $product->get_id(), '_rj_product_benefits', true );
        if ( empty( $benefits ) || ! is_array( $benefits ) ) return;

        echo '<section class="rj-benefits-section">';
        echo '<div class="rj-benefit-grid">';

        $num = 1;
        foreach ( $benefits as $benefit ) {
            $title = isset( $benefit['title'] ) ? $benefit['title'] : '';
            $text  = isset( $benefit['text'] )  ? $benefit['text']  : '';
            if ( empty( $title ) ) { $num++; continue; }

            echo '<div class="rj-benefit-item">';
            echo '<span class="rj-benefit-number">' . $num . '</span>';
            echo '<h3>' . esc_html( $title ) . '</h3>';
            if ( $text ) {
                echo '<p>' . wp_kses_post( $text ) . '</p>';
            }
            echo '</div>';
            $num++;
        }

        echo '</div>'; // .rj-benefit-grid
        echo '</section>'; // .rj-benefits-section
    }
}

add_filter( 'rockyjam_addon_hooks', function( $hooks ) {
    $hooks[] = array(
        'addon_id'   => 'product-benefits',
        'addon_name' => 'Product Benefits',
        'hook'       => 'woocommerce_after_single_product_summary',
        'function'   => 'rockyjam_product_benefits_render',
        'priority'   => 18,
        'label'      => __( 'Product Benefits', 'rockyjam-addons' ),
    );
    return $hooks;
} );
