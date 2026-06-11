<?php
/**
 * Addon: Product FAQ — functions.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rockyjam_product_faq_render' ) ) {
    /**
     * Render the FAQ accordion for the current product.
     */
    function rockyjam_product_faq_render() {
        global $product;
        if ( ! $product ) return;

        $faqs = get_post_meta( $product->get_id(), '_rj_product_faq', true );
        if ( empty( $faqs ) || ! is_array( $faqs ) ) return;

        echo '<section class="rj-faq-section">';
        echo '<h2 class="rj-section-title">' . esc_html__( 'Frequently Asked Questions', 'rockyjam-addons' ) . '</h2>';
        echo '<div class="rj-faq-items">';

        foreach ( $faqs as $faq ) {
            $question = isset( $faq['question'] ) ? $faq['question'] : '';
            $answer   = isset( $faq['answer'] )   ? $faq['answer']   : '';
            if ( empty( $question ) ) continue;

            echo '<div class="rj-faq-item">';
            echo '<h3 class="rj-faq-question">' . esc_html( $question ) . '</h3>';
            echo '<div class="rj-faq-answer">';
            echo '<div class="rj-faq-answer-content">' . wp_kses_post( $answer ) . '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>'; // .rj-faq-items
        echo '</section>'; // .rj-faq-section
    }
}

add_filter( 'rockyjam_addon_hooks', function( $hooks ) {
    $hooks[] = array(
        'addon_id'   => 'product-faq',
        'addon_name' => 'Product FAQ',
        'hook'       => 'woocommerce_after_single_product_summary',
        'function'   => 'rockyjam_product_faq_render',
        'priority'   => 25,
        'label'      => __( 'Product FAQ', 'rockyjam-addons' ),
    );
    return $hooks;
} );
