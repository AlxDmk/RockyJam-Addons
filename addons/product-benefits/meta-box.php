<?php
/**
 * Addon: Product Benefits — meta-box.php
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'rja_product_benefits',
        __( 'Product Benefits', 'rockyjam-addons' ),
        'rja_product_benefits_meta_box_render',
        'product',
        'normal',
        'default'
    );
} );

function rja_product_benefits_meta_box_render( $post ) {
    wp_nonce_field( 'rja_product_benefits_save', 'rja_product_benefits_nonce' );
    $benefits = get_post_meta( $post->ID, '_rj_product_benefits', true );
    if ( ! is_array( $benefits ) ) $benefits = array();
    ?>
    <div class="rja-benefits-admin">
        <p class="description"><?php esc_html_e( 'Add numbered benefits shown in a grid on the product page.', 'rockyjam-addons' ); ?></p>
        <div id="rja-benefits-list">
            <?php foreach ( $benefits as $i => $benefit ) : ?>
            <div class="rja-benefit-row">
                <span class="rja-benefit-num"><?php echo ( $i + 1 ); ?></span>
                <div class="rja-benefit-fields">
                    <input
                        type="text"
                        name="rja_benefits[<?php echo $i; ?>][title]"
                        value="<?php echo esc_attr( $benefit['title'] ); ?>"
                        placeholder="<?php esc_attr_e( 'Benefit Title', 'rockyjam-addons' ); ?>"
                        class="widefat"
                    />
                    <textarea
                        name="rja_benefits[<?php echo $i; ?>][text]"
                        rows="2"
                        class="widefat"
                        placeholder="<?php esc_attr_e( 'Benefit description...', 'rockyjam-addons' ); ?>"><?php echo esc_textarea( $benefit['text'] ); ?></textarea>
                </div>
                <button type="button" class="rja-benefit-remove button"><?php esc_html_e( 'Remove', 'rockyjam-addons' ); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="rja-add-benefit" class="button button-secondary">
            <?php esc_html_e( '+ Add Benefit', 'rockyjam-addons' ); ?>
        </button>
    </div>
    <?php
}

add_action( 'save_post_product', function( $post_id ) {
    if (
        ! isset( $_POST['rja_product_benefits_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rja_product_benefits_nonce'], 'rja_product_benefits_save' )
    ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $benefits = array();
    if ( ! empty( $_POST['rja_benefits'] ) && is_array( $_POST['rja_benefits'] ) ) {
        foreach ( $_POST['rja_benefits'] as $benefit ) {
            $title = sanitize_text_field( $benefit['title'] );
            $text  = wp_kses_post( $benefit['text'] );
            if ( $title ) {
                $benefits[] = array( 'title' => $title, 'text' => $text );
            }
        }
    }
    update_post_meta( $post_id, '_rj_product_benefits', $benefits );
} );
