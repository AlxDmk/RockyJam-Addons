<?php

namespace RockyJamAddons\Admin;

use RockyJamAddons\Core\AddonManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings page for RockyJam Addons.
 *
 * @package RockyJamAddons
 */
class AdminPage {

	private AddonManager $addon_manager;

	public function __construct( AddonManager $addon_manager ) {
		$this->addon_manager = $addon_manager;
	}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'handle_requests' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_menu_page(): void {
		add_options_page(
			__( 'RockyJam Addons', 'rockyjam-addons' ),
			__( 'RockyJam Addons', 'rockyjam-addons' ),
			'manage_options',
			'rockyjam-addons',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Enqueue admin CSS/JS only on our page.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'settings_page_rockyjam-addons' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'rockyjam-admin',
			ROCKYJAM_ADDONS_URL . 'assets/admin.css',
			[],
			ROCKYJAM_ADDONS_VERSION
		);
		wp_enqueue_script(
			'rockyjam-admin',
			ROCKYJAM_ADDONS_URL . 'assets/admin.js',
			[ 'jquery' ],
			ROCKYJAM_ADDONS_VERSION,
			true
		);
		wp_localize_script( 'rockyjam-admin', 'RockyJamAdmin', [
			'confirmDelete' => __( 'Are you sure you want to permanently delete this addon? This will remove all its files and cannot be undone.', 'rockyjam-addons' ),
			'nonce'         => wp_create_nonce( 'rockyjam_admin_action' ),
		] );
	}

	/**
	 * Central request handler: toggle, delete, create.
	 */
	public function handle_requests(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = $_REQUEST['rockyjam_action'] ?? '';

		if ( ! $action ) {
			return;
		}

		if ( ! isset( $_REQUEST['rockyjam_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['rockyjam_nonce'] ) ), 'rockyjam_admin_action' )
		) {
			wp_die( esc_html__( 'Security check failed.', 'rockyjam-addons' ) );
		}

		switch ( $action ) {

			// ---- Toggle on/off ----
			case 'toggle':
				$addon_id = sanitize_key( $_POST['addon_id'] ?? '' );
				if ( ! $addon_id ) {
					break;
				}
				$enabled = (array) get_option( 'rockyjam_addons_enabled', [] );
				if ( in_array( $addon_id, $enabled, true ) ) {
					$enabled = array_values( array_filter( $enabled, fn( $id ) => $id !== $addon_id ) );
				} else {
					$enabled[] = $addon_id;
				}
				update_option( 'rockyjam_addons_enabled', $enabled );
				add_settings_error( 'rockyjam_addons', 'saved', __( 'Settings saved.', 'rockyjam-addons' ), 'success' );
				break;

			// ---- Save all toggles at once ----
			case 'save':
				$raw     = $_POST['rockyjam_enabled_addons'] ?? [];
				$enabled = is_array( $raw ) ? array_map( 'sanitize_key', $raw ) : [];
				update_option( 'rockyjam_addons_enabled', $enabled );
				add_settings_error( 'rockyjam_addons', 'saved', __( 'Settings saved.', 'rockyjam-addons' ), 'success' );
				break;

			// ---- Delete addon ----
			case 'delete':
				$addon_id = sanitize_key( $_POST['addon_id'] ?? '' );
				if ( ! $addon_id ) {
					break;
				}
				$result = $this->addon_manager->delete_addon( $addon_id );
				if ( is_wp_error( $result ) ) {
					add_settings_error( 'rockyjam_addons', 'delete_error', $result->get_error_message(), 'error' );
				} else {
					add_settings_error( 'rockyjam_addons', 'deleted',
						sprintf( __( 'Addon "%s" has been deleted.', 'rockyjam-addons' ), esc_html( $addon_id ) ),
						'success'
					);
				}
				break;

			// ---- Create addon ----
			case 'create':
				$raw_id   = sanitize_key( $_POST['new_addon_id'] ?? '' );
				$raw_name = sanitize_text_field( $_POST['new_addon_name'] ?? '' );
				$raw_desc = sanitize_textarea_field( $_POST['new_addon_description'] ?? '' );
				$raw_icon = sanitize_text_field( $_POST['new_addon_icon'] ?? 'dashicons-admin-plugins' );

				if ( ! $raw_id || ! $raw_name ) {
					add_settings_error( 'rockyjam_addons', 'create_error', __( 'ID and Name are required.', 'rockyjam-addons' ), 'error' );
					break;
				}

				$result = $this->addon_manager->create_addon( $raw_id, $raw_name, $raw_desc, $raw_icon );
				if ( is_wp_error( $result ) ) {
					add_settings_error( 'rockyjam_addons', 'create_error', $result->get_error_message(), 'error' );
				} else {
					add_settings_error( 'rockyjam_addons', 'created',
						sprintf( __( 'Addon "%s" created successfully.', 'rockyjam-addons' ), esc_html( $raw_name ) ),
						'success'
					);
				}
				break;
		}

		// Redirect to clean URL (PRG pattern).
		wp_safe_redirect( admin_url( 'options-general.php?page=rockyjam-addons' ) );
		exit;
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$available    = $this->addon_manager->get_available_addons();
		$enabled_list = (array) get_option( 'rockyjam_addons_enabled', [] );
		$nonce        = wp_create_nonce( 'rockyjam_admin_action' );

		settings_errors( 'rockyjam_addons' );
		?>
		<div class="wrap rj-wrap">

			<div class="rj-header">
				<h1><?php esc_html_e( 'RockyJam Addons', 'rockyjam-addons' ); ?></h1>
				<button
					type="button"
					class="button button-primary rj-btn-create"
					id="rj-open-create"
				>
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Create Addon', 'rockyjam-addons' ); ?>
				</button>
			</div>

			<?php if ( empty( $available ) ) : ?>
				<div class="rj-empty">
					<span class="dashicons dashicons-admin-plugins"></span>
					<p><?php esc_html_e( 'No addons found. Create your first one!', 'rockyjam-addons' ); ?></p>
				</div>
			<?php else : ?>

			<form method="post" action="" id="rj-addons-form">
				<input type="hidden" name="rockyjam_action" value="save">
				<input type="hidden" name="rockyjam_nonce" value="<?php echo esc_attr( $nonce ); ?>">

				<div class="rj-grid">
				<?php foreach ( $available as $addon_id ) :
					$meta       = $this->addon_manager->get_addon_meta( $addon_id );
					$is_enabled = empty( $enabled_list ) || in_array( $addon_id, $enabled_list, true );
					$icon       = ! empty( $meta['icon'] ) ? sanitize_html_class( $meta['icon'] ) : 'dashicons-admin-plugins';
					$card_class = 'rj-card' . ( $is_enabled ? ' rj-card--active' : ' rj-card--inactive' );
				?>
				<div class="<?php echo esc_attr( $card_class ); ?>" data-addon="<?php echo esc_attr( $addon_id ); ?>">

					<div class="rj-card__icon">
						<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
					</div>

					<div class="rj-card__body">
						<h3 class="rj-card__title"><?php echo esc_html( $meta['name'] ); ?></h3>
						<?php if ( ! empty( $meta['description'] ) ) : ?>
							<p class="rj-card__desc"><?php echo esc_html( $meta['description'] ); ?></p>
						<?php endif; ?>
						<span class="rj-card__version">v<?php echo esc_html( $meta['version'] ); ?></span>
					</div>

					<div class="rj-card__actions">

						<label class="rj-toggle" title="<?php esc_attr_e( 'Enable / Disable', 'rockyjam-addons' ); ?>">
							<input
								type="checkbox"
								name="rockyjam_enabled_addons[]"
								value="<?php echo esc_attr( $addon_id ); ?>"
								class="rj-toggle__input"
								<?php checked( $is_enabled ); ?>
							>
							<span class="rj-toggle__slider"></span>
						</label>

						<button
							type="button"
							class="button rj-btn-delete"
							data-addon="<?php echo esc_attr( $addon_id ); ?>"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
							title="<?php esc_attr_e( 'Delete addon', 'rockyjam-addons' ); ?>"
						>
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>

				</div>
				<?php endforeach; ?>
				</div><!-- .rj-grid -->

				<p class="submit">
					<?php submit_button( __( 'Save Changes', 'rockyjam-addons' ), 'primary', 'submit', false ); ?>
				</p>

			</form>
			<?php endif; ?>

		</div><!-- .rj-wrap -->


		<!-- ===== CREATE ADDON MODAL ===== -->
		<div id="rj-modal-create" class="rj-modal" style="display:none;" aria-modal="true" role="dialog">
			<div class="rj-modal__overlay rj-modal-close"></div>
			<div class="rj-modal__box">

				<button type="button" class="rj-modal__close rj-modal-close" aria-label="<?php esc_attr_e( 'Close', 'rockyjam-addons' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>

				<h2><?php esc_html_e( 'Create New Addon', 'rockyjam-addons' ); ?></h2>

				<form method="post" action="" id="rj-create-form">
					<input type="hidden" name="rockyjam_action" value="create">
					<input type="hidden" name="rockyjam_nonce" value="<?php echo esc_attr( $nonce ); ?>">

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="new_addon_id"><?php esc_html_e( 'Addon ID', 'rockyjam-addons' ); ?> <span class="required">*</span></label>
							</th>
							<td>
								<input type="text" id="new_addon_id" name="new_addon_id" class="regular-text" placeholder="my-addon" required pattern="[a-z0-9\-]+">
								<p class="description"><?php esc_html_e( 'Lowercase letters, numbers and hyphens only. Used as folder name.', 'rockyjam-addons' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="new_addon_name"><?php esc_html_e( 'Addon Name', 'rockyjam-addons' ); ?> <span class="required">*</span></label>
							</th>
							<td>
								<input type="text" id="new_addon_name" name="new_addon_name" class="regular-text" placeholder="My Addon" required>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="new_addon_description"><?php esc_html_e( 'Description', 'rockyjam-addons' ); ?></label>
							</th>
							<td>
								<textarea id="new_addon_description" name="new_addon_description" class="large-text" rows="3"></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="new_addon_icon"><?php esc_html_e( 'Icon', 'rockyjam-addons' ); ?></label>
							</th>
							<td>
								<div class="rj-icon-picker">
									<input type="text" id="new_addon_icon" name="new_addon_icon" class="regular-text" value="dashicons-admin-plugins" placeholder="dashicons-admin-plugins">
									<span id="rj-icon-preview" class="dashicons dashicons-admin-plugins"></span>
								</div>
								<p class="description">
									<?php esc_html_e( 'Any Dashicons class.', 'rockyjam-addons' ); ?>
									<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank"><?php esc_html_e( 'Browse icons', 'rockyjam-addons' ); ?></a>
								</p>
							</td>
						</tr>
					</table>

					<p class="submit">
						<?php submit_button( __( 'Create Addon', 'rockyjam-addons' ), 'primary', 'rj_create_submit', false ); ?>
						<button type="button" class="button rj-modal-close"><?php esc_html_e( 'Cancel', 'rockyjam-addons' ); ?></button>
					</p>
				</form>
			</div>
		</div>

		<!-- ===== DELETE HIDDEN FORM ===== -->
		<form method="post" action="" id="rj-delete-form" style="display:none;">
			<input type="hidden" name="rockyjam_action" value="delete">
			<input type="hidden" name="rockyjam_nonce" id="rj-delete-nonce" value="">
			<input type="hidden" name="addon_id" id="rj-delete-addon-id" value="">
		</form>
		<?php
	}
}
