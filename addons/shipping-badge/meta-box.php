<?php
/**
 * Addon: Shipping Badge - meta-box.php
 * Registers a section inside the shared "RockyJam Addons" WooCommerce product tab.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register section in the shared RockyJam Addons product tab.
 */
add_filter( 'rockyjam_product_tab_sections', function( array $sections ): array {
    $sections['shipping_badge'] = [
        'label'    => __( 'Shipping Badges', 'rockyjam-addons' ),
        'icon'     => 'dashicons-tag',
        'callback' => 'rja_shipping_badge_panel_render',
        'save'     => 'rja_shipping_badge_save',
        'priority' => 20,
    ];
    return $sections;
} );

/**
 * Render the panel section.
 */
function rja_shipping_badge_panel_render( int $post_id ): void {
    wp_nonce_field( 'rja_shipping_badge_save', 'rja_shipping_badge_nonce' );

    $raw   = get_post_meta( $post_id, '_rj_shipping_badges', true );
    $items = array();

    if ( is_string( $raw ) && '' !== $raw ) {
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            $items = $decoded;
        }
    }

    // Fallback for old single-badge meta.
    if ( empty( $items ) ) {
        $legacy = get_post_meta( $post_id, '_rj_shipping_badge_text', true );
        if ( ! empty( $legacy ) ) {
            $items = array(
                array(
                    'text'  => $legacy,
                    'bg'    => 'rj-badge-bg-default',
                    'title' => '',
                ),
            );
        }
    }

    // Ensure at least one empty row.
    if ( empty( $items ) ) {
        $items = array(
            array(
                'text'  => '',
                'bg'    => 'rj-badge-bg-default',
                'title' => '',
            ),
        );
    }

    $bg_options = array(
        'rj-badge-bg-default' => __( 'Default (Dark)', 'rockyjam-addons' ),
        'rj-badge-bg-green'   => __( 'Green', 'rockyjam-addons' ),
        'rj-badge-bg-orange'  => __( 'Orange', 'rockyjam-addons' ),
        'rj-badge-bg-blue'    => __( 'Blue', 'rockyjam-addons' ),
    );
    ?>
    <table class="widefat rja-shipping-badge-table">
        <thead>
            <tr>
                <th class="rja-sb-col-handle"></th>
                <th><?php esc_html_e( 'Text', 'rockyjam-addons' ); ?></th>
                <th><?php esc_html_e( 'Background', 'rockyjam-addons' ); ?></th>
                <th class="rja-sb-col-remove"></th>
            </tr>
        </thead>
        <tbody class="rja-shipping-badge-rows">
            <?php foreach ( $items as $i => $item ) : ?>
            <tr>
                <td class="rja-sb-col-handle"><span class="rja-sb-handle">&#8942;&#8942;</span></td>
                <td>
                    <input type="text"
                        name="rja_shipping_badges[<?php echo $i; ?>][text]"
                        value="<?php echo esc_attr( $item['text'] ?? '' ); ?>"
                        class="widefat"
                        placeholder="<?php esc_attr_e( 'e.g. Free Shipping', 'rockyjam-addons' ); ?>"
                    />
                </td>
                <td>
                    <select name="rja_shipping_badges[<?php echo $i; ?>][bg]">
                        <?php foreach ( $bg_options as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $item['bg'] ?? 'rj-badge-bg-default', $val ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="rja-sb-col-remove">
                    <button type="button" class="button-link rja-sb-remove">&times;</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <!-- Badge row template (hidden) -->
            <tr class="rja-shipping-badge-template" style="display:none;">
                <td class="rja-sb-col-handle"><span class="rja-sb-handle">&#8942;&#8942;</span></td>
                <td>
                    <input type="text"
                        name="rja_shipping_badges[__index__][text]"
                        value=""
                        class="widefat"
                        placeholder="<?php esc_attr_e( 'e.g. Free Shipping', 'rockyjam-addons' ); ?>"
                    />
                </td>
                <td>
                    <select name="rja_shipping_badges[__index__][bg]">
                        <?php foreach ( $bg_options as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="rja-sb-col-remove">
                    <button type="button" class="button-link rja-sb-remove">&times;</button>
                </td>
            </tr>
        </tbody>
    </table>
    <p>
        <button type="button" class="button rja-shipping-badge-add">
            <?php esc_html_e( '+ Add Badge', 'rockyjam-addons' ); ?>
        </button>
    </p>
    <?php
}

/**
 * Save shipping badges.
 */
function rja_shipping_badge_save( int $post_id ): void {
    if (
        ! isset( $_POST['rja_shipping_badge_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rja_shipping_badge_nonce'], 'rja_shipping_badge_save' )
    ) {
        return;
    }

    $raw    = isset( $_POST['rja_shipping_badges'] ) ? (array) $_POST['rja_shipping_badges'] : [];
    $badges = [];

    foreach ( $raw as $item ) {
        $text = sanitize_text_field( $item['text'] ?? '' );
        if ( '' === $text ) {
            continue;
        }
        $badges[] = [
            'text'  => $text,
            'bg'    => sanitize_html_class( $item['bg'] ?? 'rj-badge-bg-default' ),
            'title' => sanitize_text_field( $item['title'] ?? '' ),
        ];
    }

    if ( empty( $badges ) ) {
        delete_post_meta( $post_id, '_rj_shipping_badges' );
    } else {
        update_post_meta( $post_id, '_rj_shipping_badges', wp_json_encode( $badges ) );
    }
}
