<?php
/**
 * Product FAQ - meta-box.php
 * Registers a section inside the shared "RockyJam Addons" WooCommerce product tab.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['product_faq'] = [
        'label'    => __( 'Product FAQ', 'rockyjam-addons' ),
        'callback' => 'rja_product_faq_panel_render',
        'save'     => 'rja_product_faq_panel_save',
        'priority' => 50,
    ];
    return $sections;
} );

function rja_product_faq_panel_render( int $post_id ): void {
    wp_nonce_field( 'rja_product_faq_save', 'rja_product_faq_nonce' );
    $faqs = get_post_meta( $post_id, '_rj_product_faq', true );
    if ( ! is_array( $faqs ) ) $faqs = [];
    ?>
    <p class="description"><?php esc_html_e( 'Add FAQ items for this product. They will appear as an accordion below the tabs.', 'rockyjam-addons' ); ?></p>
    <div id="rja-faq-list">
        <?php foreach ( $faqs as $i => $faq ) : ?>
        <div class="rja-faq-row">
            <div class="rja-faq-row-header">
                <span class="rja-faq-handle dashicons dashicons-menu"></span>
                <strong><?php echo esc_html( $faq['question'] ); ?></strong>
                <button type="button" class="rja-faq-remove button button-link-delete"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
            </div>
            <div class="rja-faq-row-body">
                <label><?php esc_html_e( 'Question', 'rockyjam-addons' ); ?></label>
                <input type="text" name="rja_faq[<?php echo $i; ?>][question]" value="<?php echo esc_attr( $faq['question'] ); ?>" class="widefat rja-faq-question-input" />
                <label><?php esc_html_e( 'Answer', 'rockyjam-addons' ); ?></label>
                <textarea name="rja_faq[<?php echo $i; ?>][answer]" rows="3" class="widefat"><?php echo esc_textarea( $faq['answer'] ); ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="rja-add-faq" class="button button-secondary"><?php esc_html_e( '+ Add FAQ Item', 'rockyjam-addons' ); ?></button>
    <?php
}

function rja_product_faq_panel_save( int $post_id ): void {
    if ( ! isset( $_POST['rja_product_faq_nonce'] ) || ! wp_verify_nonce( $_POST['rja_product_faq_nonce'], 'rja_product_faq_save' ) ) return;
    $raw  = isset( $_POST['rja_faq'] ) ? (array) $_POST['rja_faq'] : [];
    $faqs = [];
    foreach ( $raw as $item ) {
        $q = sanitize_text_field( $item['question'] ?? '' );
        $a = sanitize_textarea_field( $item['answer'] ?? '' );
        if ( $q || $a ) {
            $faqs[] = [ 'question' => $q, 'answer' => $a ];
        }
    }
    if ( empty( $faqs ) ) {
        delete_post_meta( $post_id, '_rj_product_faq' );
    } else {
        update_post_meta( $post_id, '_rj_product_faq', $faqs );
    }
}
