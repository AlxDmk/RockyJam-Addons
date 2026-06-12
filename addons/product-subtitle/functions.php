<?php
/**
 * Product Subtitle - meta handling and WooCommerce hook callbacks.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Meta key for the subtitle.
 */
const ROCKYJAM_PRODUCT_SUBTITLE_META_KEY = '_rockyjam_product_subtitle';

/**
 * Register section in the shared RockyJam Addons product tab.
 */
add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['product_subtitle'] = [
        'label'    => __( 'Product Subtitle', 'rockyjam-addons' ),
        'callback' => 'rockyjam_product_subtitle_panel_render',
        'save'     => 'rockyjam_product_subtitle_panel_save',
        'priority' => 5,
    ];
    return $sections;
} );

/**
 * Render the Product Subtitle panel section.
 */
function rockyjam_product_subtitle_panel_render( int $post_id ): void {
    wp_nonce_field( 'rockyjam_product_subtitle_save', 'rockyjam_product_subtitle_nonce' );
    $subtitle = get_post_meta( $post_id, ROCKYJAM_PRODUCT_SUBTITLE_META_KEY, true );
    ?>
    <p>
        <label for="rockyjam_product_subtitle_field"><strong><?php esc_html_e( 'Product subtitle', 'rockyjam-addons' ); ?></strong></label>
        <input
            type="text"
            id="rockyjam_product_subtitle_field"
            name="<?php echo esc_attr( ROCKYJAM_PRODUCT_SUBTITLE_META_KEY ); ?>"
            value="<?php echo esc_attr( (string) $subtitle ); ?>"
            class="widefat"
            placeholder="<?php esc_attr_e( 'Heavy-duty training sandbag with multiple configurations', 'rockyjam-addons' ); ?>"
        />
        <span class="description"><?php esc_html_e( 'Short marketing subtitle shown under the product title on the product page.', 'rockyjam-addons' ); ?></span>
    </p>
    <?php
}

/**
 * Save the subtitle.
 */
function rockyjam_product_subtitle_panel_save( int $post_id ): void {
    if (
        ! isset( $_POST['rockyjam_product_subtitle_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rockyjam_product_subtitle_nonce'], 'rockyjam_product_subtitle_save' )
    ) {
        return;
    }
    if ( isset( $_POST[ ROCKYJAM_PRODUCT_SUBTITLE_META_KEY ] ) ) {
        $subtitle = sanitize_text_field( wp_unslash( $_POST[ ROCKYJAM_PRODUCT_SUBTITLE_META_KEY ] ) );
        update_post_meta( $post_id, ROCKYJAM_PRODUCT_SUBTITLE_META_KEY, $subtitle );
    }
}

/**
 * Render the subtitle on the single product page.
 *
 * Hooked near the product title. The exact hook/priority is controlled
 * via RockyJam Templates Hook Manager (see filter below).
 */
function rockyjam_product_subtitle_render(): void {
    global $post;

    if ( ! $post ) {
        return;
    }

    $subtitle = get_post_meta( $post->ID, ROCKYJAM_PRODUCT_SUBTITLE_META_KEY, true );
    $subtitle = trim( (string) $subtitle );

    if ( '' === $subtitle ) {
        return;
    }

    echo '<p class="rj-product-subtitle">' . esc_html( $subtitle ) . '</p>';
}

/**
 * Declare hook to RockyJam Templates Hook Manager.
 */
add_filter( 'rockyjam_addon_hooks', function ( array $hooks ): array {
    $hooks[] = [
        'addon_id'   => 'product-subtitle',
        'addon_name' => 'Product Subtitle',
        'hook'       => 'woocommerce_single_product_summary',
        'function'   => 'rockyjam_product_subtitle_render',
        'priority'   => 6,
        'label'      => 'Product Subtitle (under title)',
    ];
    return $hooks;
} );
