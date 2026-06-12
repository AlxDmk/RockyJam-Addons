<?php
/**
 * RockyJam Addons — Product Data Tab Manager.
 *
 * Registers a single "RockyJam Addons" tab inside the WooCommerce
 * Product Data meta-box (the panel that contains General, Inventory,
 * Shipping, etc.). Every addon registers its own panel section via the
 * filter `rockyjam_product_tab_sections`.
 *
 * Usage in an addon's meta-box.php (replaces add_meta_box):
 *
 *   add_filter( 'rockyjam_product_tab_sections', function( $sections ) {
 *       $sections['my_addon'] = [
 *           'label'    => __( 'My Addon', 'rockyjam-addons' ),
 *           'icon'     => 'dashicons-admin-generic', // any dashicon class
 *           'callback' => 'my_addon_panel_render',   // callable
 *           'save'     => 'my_addon_save',           // callable (optional)
 *           'priority' => 20,                        // order inside panel
 *       ];
 *       return $sections;
 *   } );
 *
 * The `save` callback receives ( int $post_id ) and is called on
 * `woocommerce_process_product_meta`.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the top-level "RockyJam Addons" tab in the WC Product Data panel.
 */
add_filter( 'woocommerce_product_data_tabs', function ( array $tabs ): array {
    $tabs['rockyjam_addons'] = [
        'label'    => __( 'RockyJam Addons', 'rockyjam-addons' ),
        'target'   => 'rockyjam_addons_panel',
        'class'    => [],
        'priority' => 100,
    ];
    return $tabs;
} );

/**
 * Render the panel content.
 *
 * Each registered section is called in `priority` order.
 */
add_action( 'woocommerce_product_data_panels', function (): void {
    $sections = (array) apply_filters( 'rockyjam_product_tab_sections', [] );

    // Sort by priority.
    usort( $sections, function ( $a, $b ) {
        return ( $a['priority'] ?? 10 ) <=> ( $b['priority'] ?? 10 );
    } );

    echo '<div id="rockyjam_addons_panel" class="panel woocommerce_options_panel rja-product-tab">';
    echo '<div class="rja-product-tab__inner">';

    if ( empty( $sections ) ) {
        echo '<p class="rja-product-tab__empty">';
        esc_html_e( 'No RockyJam Addons are active.', 'rockyjam-addons' );
        echo '</p>';
    } else {
        foreach ( $sections as $id => $section ) {
            if ( empty( $section['callback'] ) || ! is_callable( $section['callback'] ) ) {
                continue;
            }
            $label = isset( $section['label'] ) ? esc_html( $section['label'] ) : '';
            echo '<div class="rja-product-tab__section" id="rja-section-' . esc_attr( $id ) . '">';
            if ( $label ) {
                echo '<h3 class="rja-product-tab__section-title">' . $label . '</h3>';
            }
            echo '<div class="rja-product-tab__section-body">';
            call_user_func( $section['callback'], get_the_ID() );
            echo '</div>';
            echo '</div>';
        }
    }

    echo '</div>';
    echo '</div>';
} );

/**
 * Run save callbacks for all registered sections.
 */
add_action( 'woocommerce_process_product_meta', function ( int $post_id ): void {
    $sections = (array) apply_filters( 'rockyjam_product_tab_sections', [] );
    foreach ( $sections as $section ) {
        if ( ! empty( $section['save'] ) && is_callable( $section['save'] ) ) {
            call_user_func( $section['save'], $post_id );
        }
    }
} );

/**
 * Enqueue admin styles for the tab.
 */
add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || 'product' !== $screen->id ) {
        return;
    }
    wp_enqueue_style(
        'rja-product-tab',
        plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/css/product-tab.css',
        [],
        '1.0.0'
    );
} );
