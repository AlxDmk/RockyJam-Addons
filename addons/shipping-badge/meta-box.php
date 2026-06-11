<?php
/**
 * Addon: Shipping Badge — meta-box.php
 * Admin meta-box to set the badge text per product.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register meta-box.
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'rja_shipping_badge',
        __( 'Shipping Badge', 'rockyjam-addons' ),
        'rja_shipping_badge_meta_box_render',
        'product',
        'side',
        'default'
    );
} );

/**
 * Render the meta-box.
 */
function rja_shipping_badge_meta_box_render( $post ) {
    wp_nonce_field( 'rja_shipping_badge_save', 'rja_shipping_badge_nonce' );
    $badge_text = get_post_meta( $post->ID, '_rj_shipping_badge_text', true );
    ?>
    <div class="rja-shipping-badge-admin">
        <p>
            <label for="rja_shipping_badge_text"><?php esc_html_e( 'Badge Text', 'rockyjam-addons' ); ?></label>
            <input
                type="text"
                id="rja_shipping_badge_text"
                name="rja_shipping_badge_text"
                value="<?php echo esc_attr( $badge_text ); ?>"
                placeholder="<?php esc_attr_e( 'e.g. 3 Ships Free Item', 'rockyjam-addons' ); ?>"
                class="widefat"
            />
        </p>
        <p class="description"><?php esc_html_e( 'Leave blank to hide the badge.', 'rockyjam-addons' ); ?></p>
    </div>
    <?php
}

/**
 * Save meta-box data.
 */
add_action( 'save_post_product', function( $post_id ) {
    if (
        ! isset( $_POST['rja_shipping_badge_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rja_shipping_badge_nonce'], 'rja_shipping_badge_save' )
    ) return;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['rja_shipping_badge_text'] ) ) {
        update_post_meta( $post_id, '_rj_shipping_badge_text', sanitize_text_field( $_POST['rja_shipping_badge_text'] ) );
    }
} );
