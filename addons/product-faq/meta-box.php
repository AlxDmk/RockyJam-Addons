<?php
/**
 * Addon: Product FAQ — meta-box.php
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'rja_product_faq',
        __( 'Product FAQ', 'rockyjam-addons' ),
        'rja_product_faq_meta_box_render',
        'product',
        'normal',
        'default'
    );
} );

function rja_product_faq_meta_box_render( $post ) {
    wp_nonce_field( 'rja_product_faq_save', 'rja_product_faq_nonce' );
    $faqs = get_post_meta( $post->ID, '_rj_product_faq', true );
    if ( ! is_array( $faqs ) ) $faqs = array();
    ?>
    <div class="rja-faq-admin">
        <p class="description"><?php esc_html_e( 'Add FAQ items for this product. They will appear as an accordion below the tabs.', 'rockyjam-addons' ); ?></p>
        <div id="rja-faq-list">
            <?php foreach ( $faqs as $i => $faq ) : ?>
            <div class="rja-faq-row">
                <div class="rja-faq-row-header">
                    <span class="rja-faq-handle dashicons dashicons-menu"></span>
                    <strong><?php echo esc_html( $faq['question'] ?: __( 'FAQ Item', 'rockyjam-addons' ) ); ?></strong>
                    <button type="button" class="rja-faq-remove button button-link-delete"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
                </div>
                <div class="rja-faq-row-body">
                    <label><?php esc_html_e( 'Question', 'rockyjam-addons' ); ?></label>
                    <input
                        type="text"
                        name="rja_faq[<?php echo $i; ?>][question]"
                        value="<?php echo esc_attr( $faq['question'] ); ?>"
                        class="widefat rja-faq-question-input"
                    />
                    <label><?php esc_html_e( 'Answer', 'rockyjam-addons' ); ?></label>
                    <textarea
                        name="rja_faq[<?php echo $i; ?>][answer]"
                        rows="3"
                        class="widefat"><?php echo esc_textarea( $faq['answer'] ); ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="rja-add-faq" class="button button-secondary">
            <?php esc_html_e( '+ Add FAQ Item', 'rockyjam-addons' ); ?>
        </button>
    </div>
    <?php
}

add_action( 'save_post_product', function( $post_id ) {
    if (
        ! isset( $_POST['rja_product_faq_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rja_product_faq_nonce'], 'rja_product_faq_save' )
    ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $faqs = array();
    if ( ! empty( $_POST['rja_faq'] ) && is_array( $_POST['rja_faq'] ) ) {
        foreach ( $_POST['rja_faq'] as $faq ) {
            $question = sanitize_text_field( $faq['question'] );
            $answer   = wp_kses_post( $faq['answer'] );
            if ( $question ) {
                $faqs[] = array( 'question' => $question, 'answer' => $answer );
            }
        }
    }
    update_post_meta( $post_id, '_rj_product_faq', $faqs );
} );
