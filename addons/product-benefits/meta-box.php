<?php
/**
 * Product Benefits - meta-box.php
 * Registers a section inside the shared "RockyJam Addons" WooCommerce product tab.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['product_benefits'] = [
        'label'    => __( 'Product Benefits', 'rockyjam-addons' ),
        'callback' => 'rja_product_benefits_panel_render',
        'save'     => 'rja_product_benefits_panel_save',
        'priority' => 40,
    ];
    return $sections;
} );

function rja_product_benefits_panel_render( int $post_id ): void {
    wp_nonce_field( 'rja_product_benefits_save', 'rja_product_benefits_nonce' );
    $benefits = get_post_meta( $post_id, '_rj_product_benefits', true );
    if ( ! is_array( $benefits ) ) $benefits = [];
    ?>
    <p class="description"><?php esc_html_e( 'Add numbered benefits shown in a grid on the product page.', 'rockyjam-addons' ); ?></p>
    <div id="rja-benefits-list">
        <?php foreach ( $benefits as $i => $benefit ) : ?>
        <div class="rja-benefit-row">
            <span class="rja-benefit-num"><?php echo ( $i + 1 ); ?></span>
            <div class="rja-benefit-fields">
                <input type="text" name="rja_benefits[<?php echo $i; ?>][title]" value="<?php echo esc_attr( $benefit['title'] ); ?>" placeholder="<?php esc_attr_e( 'Benefit Title', 'rockyjam-addons' ); ?>" class="widefat" />
                <textarea name="rja_benefits[<?php echo $i; ?>][text]" rows="2" class="widefat" placeholder="<?php esc_attr_e( 'Benefit description...', 'rockyjam-addons' ); ?>"><?php echo esc_textarea( $benefit['text'] ); ?></textarea>
            </div>
            <button type="button" class="rja-benefit-remove button"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="rja-add-benefit" class="button button-secondary"><?php esc_html_e( '+ Add Benefit', 'rockyjam-addons' ); ?></button>
    <?php
}

function rja_product_benefits_panel_save( int $post_id ): void {
    if ( ! isset( $_POST['rja_product_benefits_nonce'] ) || ! wp_verify_nonce( $_POST['rja_product_benefits_nonce'], 'rja_product_benefits_save' ) ) return;
    $raw = isset( $_POST['rja_benefits'] ) ? (array) $_POST['rja_benefits'] : [];
    $benefits = [];
    foreach ( $raw as $benefit ) {
        $title = sanitize_text_field( $benefit['title'] ?? '' );
        $text  = sanitize_textarea_field( $benefit['text']  ?? '' );
        if ( $title || $text ) {
            $benefits[] = [ 'title' => $title, 'text' => $text ];
        }
    }
    if ( empty( $benefits ) ) {
        delete_post_meta( $post_id, '_rj_product_benefits' );
    } else {
        update_post_meta( $post_id, '_rj_product_benefits', $benefits );
    }
}
