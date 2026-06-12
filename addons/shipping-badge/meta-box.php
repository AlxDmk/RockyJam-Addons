<?php
/**
 * Addon: Shipping Badge — meta-box.php
 * Admin meta-box to set shipping badges per product.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register meta-box.
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'rja_shipping_badge',
        __( 'Shipping Badges', 'rockyjam-addons' ),
        'rja_shipping_badge_meta_box_render',
        'product',
        'side',
        'default'
    );
} );

/**
 * Render the meta-box.
 *
 * Supports multiple badges stored as a JSON-encoded array in _rj_shipping_badges.
 * Each badge has: text, background (CSS color class), and optional title attribute.
 */
function rja_shipping_badge_meta_box_render( $post ) {
    wp_nonce_field( 'rja_shipping_badge_save', 'rja_shipping_badge_nonce' );

    $raw   = get_post_meta( $post->ID, '_rj_shipping_badges', true );
    $items = array();

    if ( is_string( $raw ) && '' !== $raw ) {
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            $items = $decoded;
        }
    }

    // Fallback for old single-badge meta.
    if ( empty( $items ) ) {
        $legacy = get_post_meta( $post->ID, '_rj_shipping_badge_text', true );
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
    ?>
    <div class="rja-shipping-badge-admin">
        <p class="description"><?php esc_html_e( 'Configure one or more shipping badges. Empty rows will be ignored. Drag rows to change order.', 'rockyjam-addons' ); ?></p>

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
                <?php foreach ( $items as $index => $item ) :
                    $text  = isset( $item['text'] ) ? $item['text'] : '';
                    $bg    = isset( $item['bg'] ) ? $item['bg'] : 'rj-badge-bg-default';
                    ?>
                    <tr>
                        <td class="rja-sb-col-handle"><span class="rja-sb-handle" aria-hidden="true">⋮⋮</span></td>
                        <td>
                            <input
                                type="text"
                                name="rja_shipping_badges[<?php echo esc_attr( $index ); ?>][text]"
                                value="<?php echo esc_attr( $text ); ?>"
                                placeholder="<?php esc_attr_e( 'e.g. Ships Free', 'rockyjam-addons' ); ?>"
                                class="widefat"
                            />
                        </td>
                        <td>
                            <select
                                name="rja_shipping_badges[<?php echo esc_attr( $index ); ?>][bg]"
                                class="widefat"
                            >
                                <option value="rj-badge-bg-default" <?php selected( $bg, 'rj-badge-bg-default' ); ?>><?php esc_html_e( 'Default', 'rockyjam-addons' ); ?></option>
                                <option value="rj-badge-bg-green" <?php selected( $bg, 'rj-badge-bg-green' ); ?>><?php esc_html_e( 'Green', 'rockyjam-addons' ); ?></option>
                                <option value="rj-badge-bg-orange" <?php selected( $bg, 'rj-badge-bg-orange' ); ?>><?php esc_html_e( 'Orange', 'rockyjam-addons' ); ?></option>
                                <option value="rj-badge-bg-blue" <?php selected( $bg, 'rj-badge-bg-blue' ); ?>><?php esc_html_e( 'Blue', 'rockyjam-addons' ); ?></option>
                            </select>
                        </td>
                        <td class="rja-sb-col-remove">
                            <button type="button" class="button-link rja-sb-remove" aria-label="<?php esc_attr_e( 'Remove badge', 'rockyjam-addons' ); ?>">&times;</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Template row for JS-based add-more. -->
                <tr class="rja-shipping-badge-template" style="display:none;">
                    <td class="rja-sb-col-handle"><span class="rja-sb-handle" aria-hidden="true">⋮⋮</span></td>
                    <td>
                        <input
                            type="text"
                            name="rja_shipping_badges[__index__][text]"
                            value=""
                            placeholder="<?php esc_attr_e( 'e.g. Ships Free', 'rockyjam-addons' ); ?>"
                            class="widefat"
                        />
                    </td>
                    <td>
                        <select
                            name="rja_shipping_badges[__index__][bg]"
                            class="widefat"
                        >
                            <option value="rj-badge-bg-default"><?php esc_html_e( 'Default', 'rockyjam-addons' ); ?></option>
                            <option value="rj-badge-bg-green"><?php esc_html_e( 'Green', 'rockyjam-addons' ); ?></option>
                            <option value="rj-badge-bg-orange"><?php esc_html_e( 'Orange', 'rockyjam-addons' ); ?></option>
                            <option value="rj-badge-bg-blue"><?php esc_html_e( 'Blue', 'rockyjam-addons' ); ?></option>
                        </select>
                    </td>
                    <td class="rja-sb-col-remove">
                        <button type="button" class="button-link rja-sb-remove" aria-label="<?php esc_attr_e( 'Remove badge', 'rockyjam-addons' ); ?>">&times;</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            <button type="button" class="button rja-shipping-badge-add"><?php esc_html_e( 'Add badge', 'rockyjam-addons' ); ?></button>
        </p>
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

    // New multi-badge format.
    if ( isset( $_POST['rja_shipping_badges'] ) && is_array( $_POST['rja_shipping_badges'] ) ) {
        $badges   = array();
        $raw_rows = wp_unslash( $_POST['rja_shipping_badges'] );

        foreach ( $raw_rows as $row ) {
            $text = isset( $row['text'] ) ? sanitize_text_field( $row['text'] ) : '';
            $bg   = isset( $row['bg'] ) ? sanitize_key( $row['bg'] ) : 'rj-badge-bg-default';

            if ( '' === $text ) {
                continue; // Skip empty rows.
            }

            $badges[] = array(
                'text'  => $text,
                'bg'    => $bg,
                'title' => '',
            );
        }

        if ( ! empty( $badges ) ) {
            update_post_meta( $post_id, '_rj_shipping_badges', wp_json_encode( $badges ) );
        } else {
            delete_post_meta( $post_id, '_rj_shipping_badges' );
        }
    }

    // Keep legacy single_text meta for backward compatibility (optional).
    if ( isset( $_POST['rja_shipping_badge_text'] ) ) {
        update_post_meta( $post_id, '_rj_shipping_badge_text', sanitize_text_field( $_POST['rja_shipping_badge_text'] ) );
    }
} );
