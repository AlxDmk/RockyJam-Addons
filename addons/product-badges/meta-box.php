<?php
/**
 * Addon: Product Badges — meta-box.php
 * Admin meta-box to add/remove/edit trust badges per product.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'rja_product_badges',
        __( 'Product Badges', 'rockyjam-addons' ),
        'rja_product_badges_meta_box_render',
        'product',
        'normal',
        'default'
    );
} );

function rja_product_badges_meta_box_render( $post ) {
    wp_nonce_field( 'rja_product_badges_save', 'rja_product_badges_nonce' );
    $badges = get_post_meta( $post->ID, '_rj_product_badges', true );
    if ( ! is_array( $badges ) ) $badges = array();
    ?>
    <div class="rja-badges-admin">
        <p class="description"><?php esc_html_e( 'Add trust badges displayed on the product page (e.g. Made in USA, Warranty).', 'rockyjam-addons' ); ?></p>
        <div id="rja-badges-list">
            <?php foreach ( $badges as $i => $badge ) : ?>
            <div class="rja-badge-row">
                <input
                    type="text"
                    name="rja_badges[<?php echo $i; ?>][title]"
                    value="<?php echo esc_attr( $badge['title'] ); ?>"
                    placeholder="<?php esc_attr_e( 'Badge Title (e.g. 🇺🇸 Made in USA)', 'rockyjam-addons' ); ?>"
                    class="rja-badge-title"
                />
                <input
                    type="text"
                    name="rja_badges[<?php echo $i; ?>][text]"
                    value="<?php echo esc_attr( $badge['text'] ); ?>"
                    placeholder="<?php esc_attr_e( 'Badge Text (e.g. Proudly manufactured in Ohio)', 'rockyjam-addons' ); ?>"
                    class="rja-badge-text"
                />
                <button type="button" class="rja-badge-remove button"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="rja-add-badge" class="button button-secondary">
            <?php esc_html_e( '+ Add Badge', 'rockyjam-addons' ); ?>
        </button>
    </div>
    <?php
}

add_action( 'save_post_product', function( $post_id ) {
    if (
        ! isset( $_POST['rja_product_badges_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rja_product_badges_nonce'], 'rja_product_badges_save' )
    ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $badges = array();
    if ( ! empty( $_POST['rja_badges'] ) && is_array( $_POST['rja_badges'] ) ) {
        foreach ( $_POST['rja_badges'] as $badge ) {
            $title = sanitize_text_field( $badge['title'] );
            $text  = sanitize_text_field( $badge['text'] );
            if ( $title ) {
                $badges[] = array( 'title' => $title, 'text' => $text );
            }
        }
    }
    update_post_meta( $post_id, '_rj_product_badges', $badges );
} );
