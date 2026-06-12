<?php
/**
 * Product Badges - meta-box.php
 * Registers a section inside the shared "RockyJam Addons" WooCommerce product tab.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['product_badges'] = [
        'label'    => __( 'Product Badges', 'rockyjam-addons' ),
        'callback' => 'rja_product_badges_panel_render',
        'save'     => 'rja_product_badges_panel_save',
        'priority' => 30,
    ];
    return $sections;
} );

function rja_product_badges_panel_render( int $post_id ): void {
    wp_nonce_field( 'rja_product_badges_save', 'rja_product_badges_nonce' );
    $badges = get_post_meta( $post_id, '_rj_product_badges', true );
    if ( ! is_array( $badges ) ) $badges = [];
    ?>
    <p class="description"><?php esc_html_e( 'Add trust badges displayed on the product page (e.g. Made in USA, Warranty).', 'rockyjam-addons' ); ?></p>
    <div id="rja-badges-list">
        <?php foreach ( $badges as $i => $badge ) : ?>
        <div class="rja-badge-row">
            <input type="text" name="rja_badges[<?php echo $i; ?>][title]" value="<?php echo esc_attr( $badge['title'] ); ?>" placeholder="<?php esc_attr_e( 'Badge Title (e.g. us Made in USA)', 'rockyjam-addons' ); ?>" class="rja-badge-title" />
            <input type="text" name="rja_badges[<?php echo $i; ?>][text]" value="<?php echo esc_attr( $badge['text'] ); ?>" placeholder="<?php esc_attr_e( 'Badge Text (e.g. Proudly manufactured in Ohio)', 'rockyjam-addons' ); ?>" class="rja-badge-text" />
            <button type="button" class="rja-badge-remove button"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="rja-add-badge" class="button button-secondary"><?php esc_html_e( '+ Add Badge', 'rockyjam-addons' ); ?></button>
    <?php
}

function rja_product_badges_panel_save( int $post_id ): void {
    if ( ! isset( $_POST['rja_product_badges_nonce'] ) || ! wp_verify_nonce( $_POST['rja_product_badges_nonce'], 'rja_product_badges_save' ) ) return;
    $raw = isset( $_POST['rja_badges'] ) ? (array) $_POST['rja_badges'] : [];
    $badges = [];
    foreach ( $raw as $badge ) {
        $title = sanitize_text_field( $badge['title'] ?? '' );
        $text  = sanitize_text_field( $badge['text']  ?? '' );
        if ( $title || $text ) {
            $badges[] = [ 'title' => $title, 'text' => $text ];
        }
    }
    if ( empty( $badges ) ) {
        delete_post_meta( $post_id, '_rj_product_badges' );
    } else {
        update_post_meta( $post_id, '_rj_product_badges', $badges );
    }
}
