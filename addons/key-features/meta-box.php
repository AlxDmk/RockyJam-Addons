<?php
/**
 * Key Features - meta-box.php
 * Registers a section inside the shared "RockyJam Addons" WooCommerce product tab.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register section in the shared RockyJam Addons product tab.
 */
add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['key_features'] = [
        'label'    => __( 'Key Features', 'rockyjam-addons' ),
        'icon'     => 'dashicons-star-filled',
        'callback' => 'rockyjam_keyfeatures_panel_render',
        'save'     => 'rockyjam_keyfeatures_panel_save',
        'priority' => 10,
    ];
    return $sections;
} );

/**
 * Render the Key Features panel section.
 */
function rockyjam_keyfeatures_panel_render( int $post_id ): void {
    wp_nonce_field( 'rockyjam_key_features_save', 'rockyjam_key_features_nonce' );

    $raw      = get_post_meta( $post_id, '_rockyjam_key_features', true );
    $features = is_array( $raw ) ? array_values( array_filter( array_map( 'trim', $raw ) ) ) : [];
    ?>
    <p class="rjt-kf-metabox__hint">
        <?php esc_html_e( 'Add the key features of this product. They will be displayed on the product page when the Key Features addon is active.', 'rockyjam-addons' ); ?>
    </p>
    <ul class="rjt-kf-list" id="rjt-kf-list">
        <?php if ( $features ) : ?>
            <?php foreach ( $features as $feature ) : ?>
            <li class="rjt-kf-list__item">
                <span class="rjt-kf-list__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'rockyjam-addons' ); ?>"></span>
                <input
                    type="text"
                    name="rockyjam_key_features[]"
                    class="rjt-kf-list__input widefat"
                    value="<?php echo esc_attr( $feature ); ?>"
                    placeholder="<?php esc_attr_e( 'Feature description…', 'rockyjam-addons' ); ?>"
                />
                <button type="button" class="button rjt-kf-list__remove" title="<?php esc_attr_e( 'Remove', 'rockyjam-addons' ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <button type="button" class="button button-secondary rjt-kf-add-btn" id="rjt-kf-add-btn">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e( 'Add Feature', 'rockyjam-addons' ); ?>
    </button>
    <?php
}

/**
 * Save Key Features.
 */
function rockyjam_keyfeatures_panel_save( int $post_id ): void {
    if (
        ! isset( $_POST['rockyjam_key_features_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rockyjam_key_features_nonce'], 'rockyjam_key_features_save' )
    ) {
        return;
    }

    $raw      = isset( $_POST['rockyjam_key_features'] ) ? (array) $_POST['rockyjam_key_features'] : [];
    $features = array_values( array_filter( array_map( 'sanitize_text_field', $raw ) ) );

    if ( empty( $features ) ) {
        delete_post_meta( $post_id, '_rockyjam_key_features' );
    } else {
        update_post_meta( $post_id, '_rockyjam_key_features', $features );
    }
}
