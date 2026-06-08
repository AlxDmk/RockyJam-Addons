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
		add_action( 'admin_menu', [ $this, 'register_parent_menu' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		/*
		 * Формы отправляются на admin-post.php с action=rockyjam_handle.
		 * WordPress вызовет 'admin_post_rockyjam_handle' ДО любого вывода,
		 * что гарантирует корректную работу wp_safe_redirect().
		 */
		add_action( 'admin_post_rockyjam_handle', [ $this, 'handle_requests' ] );
	}

	public function register_parent_menu(): void {
		// Register the top-level "RockyJam" menu only once.
		// Both plugins call this; the second call is a no-op because
		// WordPress silently ignores duplicate menu slugs.
		if ( ! $this->parent_menu_exists() ) {
			add_menu_page(
				'RockyJam',
				'RockyJam',
				'manage_options',
				'rockyjam',
				'__return_null',
				'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><circle cx="10" cy="10" r="9" fill="none" stroke="#a7aaad" stroke-width="1.5"/><text x="10" y="14.5" text-anchor="middle" font-size="11" font-weight="bold" fill="#a7aaad" font-family="sans-serif">RJ</text></svg>' ),
				57
			);
		}
	}

	public function add_menu_page(): void {
		add_submenu_page(
			'rockyjam',
			__( 'RockyJam Addons', 'rockyjam-addons' ),
			__( 'Addons', 'rockyjam-addons' ),
			'manage_options',
			'rockyjam-addons',
			[ $this, 'render_page' ]
		);
	}

	/** Check if the top-level RockyJam menu already exists. */
	private function parent_menu_exists(): bool {
		global $menu;
		if ( ! is_array( $menu ) ) {
			return false;
		}
		foreach ( $menu as $item ) {
			if ( isset( $item[2] ) && 'rockyjam' === $item[2] ) {
				return true;
			}
		}
		return false;
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'rockyjam_page_rockyjam-addons' !== $hook ) {
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
		] );
	}

	/**
	 * Central POST handler. Called via admin_post_rockyjam_handle hook.
	 * WordPress fires this hook on admin-post.php before any output,
	 * so wp_safe_redirect() always works correctly.
	 */
	public function handle_requests(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'rockyjam-addons' ) );
		}

		if ( empty( $_POST['rockyjam_action'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=rockyjam-addons' ) );
			exit;
		}

		// Verify nonce.
		$nonce = isset( $_POST['rockyjam_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['rockyjam_nonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'rockyjam_admin_action' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'rockyjam-addons' ) );
		}

		$action   = sanitize_key( $_POST['rockyjam_action'] );
		$redirect = admin_url( 'admin.php?page=rockyjam-addons' );
		$notice   = '';     // text of transient notice
		$notice_type = 'success';

		switch ( $action ) {

			// ----------------------------------------------------------------
			// Save all toggles at once (main form "Save Changes").
			// ----------------------------------------------------------------
			case 'save':
				$raw     = isset( $_POST['rockyjam_enabled_addons'] ) && is_array( $_POST['rockyjam_enabled_addons'] )
					? $_POST['rockyjam_enabled_addons']
					: [];
				$enabled = array_map( 'sanitize_key', $raw );
				update_option( 'rockyjam_addons_enabled', $enabled );
				$notice = __( 'Settings saved.', 'rockyjam-addons' );
				break;

			// ----------------------------------------------------------------
			// Delete addon — physically removes the directory.
			// ----------------------------------------------------------------
			case 'delete':
				$addon_id = sanitize_key( $_POST['addon_id'] ?? '' );
				if ( ! $addon_id ) {
					$notice      = __( 'Invalid addon ID.', 'rockyjam-addons' );
					$notice_type = 'error';
					break;
				}
				$result = $this->addon_manager->delete_addon( $addon_id );
				if ( is_wp_error( $result ) ) {
					$notice      = $result->get_error_message();
					$notice_type = 'error';
				} else {
					/* translators: %s: addon ID */
					$notice = sprintf( __( 'Addon "%s" deleted successfully.', 'rockyjam-addons' ), $addon_id );
				}
				break;

			// ----------------------------------------------------------------
			// Create addon — scaffold files on disk.
			// ----------------------------------------------------------------
			case 'create':
				$raw_id   = sanitize_key( $_POST['new_addon_id'] ?? '' );
				$raw_name = sanitize_text_field( $_POST['new_addon_name'] ?? '' );
				$raw_desc = sanitize_textarea_field( $_POST['new_addon_description'] ?? '' );
				$raw_icon = sanitize_text_field( $_POST['new_addon_icon'] ?? 'dashicons-admin-plugins' );

				if ( ! $raw_id || ! $raw_name ) {
					$notice      = __( 'Addon ID and Name are required.', 'rockyjam-addons' );
					$notice_type = 'error';
					break;
				}
				$result = $this->addon_manager->create_addon( $raw_id, $raw_name, $raw_desc, $raw_icon );
				if ( is_wp_error( $result ) ) {
					$notice      = $result->get_error_message();
					$notice_type = 'error';
				} else {
					/* translators: %s: addon name */
					$notice = sprintf( __( 'Addon "%s" created successfully.', 'rockyjam-addons' ), esc_html( $raw_name ) );
				}
				break;

			default:
				// Unknown action — do nothing.
				return;
		}

		// Store notice in transient so it survives the redirect.
		if ( $notice ) {
			set_transient(
				'rockyjam_admin_notice_' . get_current_user_id(),
				[ 'message' => $notice, 'type' => $notice_type ],
				30
			);
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$available    = $this->addon_manager->get_available_addons();
		$enabled_list = (array) get_option( 'rockyjam_addons_enabled', [] );
		$nonce        = wp_create_nonce( 'rockyjam_admin_action' );

		// Show transient notice (after redirect).
		$notice = get_transient( 'rockyjam_admin_notice_' . get_current_user_id() );
		if ( $notice ) {
			delete_transient( 'rockyjam_admin_notice_' . get_current_user_id() );
			$class = ( 'error' === $notice['type'] ) ? 'notice-error' : 'notice-success';
			printf(
				'<div class="notice %s is-dismissible"><p>%s</p></div>',
				esc_attr( $class ),
				esc_html( $notice['message'] )
			);
		}
		?>
		<div class="wrap rj-wrap">

			<div class="rj-header">
				<h1><?php esc_html_e( 'RockyJam Addons', 'rockyjam-addons' ); ?></h1>
				<button type="button" class="button button-primary rj-btn-create" id="rj-open-create">
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

			<!-- Main form: save toggle states -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="rj-addons-form">
				<input type="hidden" name="action" value="rockyjam_handle">
				<input type="hidden" name="rockyjam_action" value="save">
				<input type="hidden" name="rockyjam_nonce" value="<?php echo esc_attr( $nonce ); ?>">

				<div class="rj-grid">
				<?php foreach ( $available as $addon_id ) :
					$meta       = $this->addon_manager->get_addon_meta( $addon_id );
					$is_enabled = empty( $enabled_list ) || in_array( $addon_id, $enabled_list, true );
					// Use sanitize_text_field — sanitize_html_class strips hyphens and breaks dashicons.
					$icon       = ! empty( $meta['icon'] ) ? sanitize_text_field( $meta['icon'] ) : 'dashicons-admin-plugins';
					$card_class = 'rj-card' . ( $is_enabled ? ' rj-card--active' : ' rj-card--inactive' );
				?>
				<div class="<?php echo esc_attr( $card_class ); ?>">
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
						><span class="dashicons dashicons-trash"></span></button>
					</div>
				</div>
				<?php endforeach; ?>
				</div>

				<p class="submit">
					<?php submit_button( __( 'Save Changes', 'rockyjam-addons' ), 'primary', 'submit', false ); ?>
				</p>
			</form>

			<?php endif; ?>
		</div><!-- .rj-wrap -->

		<!-- ============================================================
		     DELETE form — lives OUTSIDE the main form to avoid nesting.
		     Submitted programmatically from admin.js.
		     ============================================================ -->
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="rj-delete-form">
			<input type="hidden" name="action" value="rockyjam_handle">
			<input type="hidden" name="rockyjam_action" value="delete">
			<input type="hidden" name="rockyjam_nonce" id="rj-delete-nonce" value="">
			<input type="hidden" name="addon_id" id="rj-delete-addon-id" value="">
		</form>

		<!-- ============================================================
		     CREATE MODAL — form also lives outside the grid form.
		     ============================================================ -->
		<div id="rj-modal-create" class="rj-modal" style="display:none;" aria-modal="true" role="dialog">
			<div class="rj-modal__overlay rj-modal-close"></div>
			<div class="rj-modal__box">
				<button type="button" class="rj-modal__close rj-modal-close" aria-label="<?php esc_attr_e( 'Close', 'rockyjam-addons' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
				<h2><?php esc_html_e( 'Create New Addon', 'rockyjam-addons' ); ?></h2>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="rj-create-form">
					<input type="hidden" name="action" value="rockyjam_handle">
					<input type="hidden" name="rockyjam_action" value="create">
					<input type="hidden" name="rockyjam_nonce" value="<?php echo esc_attr( $nonce ); ?>">

					<table class="form-table" role="presentation">
						<tr>
							<th><label for="new_addon_id"><?php esc_html_e( 'Addon ID', 'rockyjam-addons' ); ?> <span class="required">*</span></label></th>
							<td>
								<input type="text" id="new_addon_id" name="new_addon_id" class="regular-text" placeholder="my-addon" required pattern="[a-z0-9\-]+">
								<p class="description"><?php esc_html_e( 'Lowercase letters, numbers and hyphens. Used as folder name.', 'rockyjam-addons' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="new_addon_name"><?php esc_html_e( 'Addon Name', 'rockyjam-addons' ); ?> <span class="required">*</span></label></th>
							<td>
								<input type="text" id="new_addon_name" name="new_addon_name" class="regular-text" placeholder="My Addon" required>
							</td>
						</tr>
						<tr>
							<th><label for="new_addon_description"><?php esc_html_e( 'Description', 'rockyjam-addons' ); ?></label></th>
							<td>
								<textarea id="new_addon_description" name="new_addon_description" class="large-text" rows="3"></textarea>
							</td>
						</tr>
						<tr>
							<th><label for="new_addon_icon"><?php esc_html_e( 'Icon', 'rockyjam-addons' ); ?></label></th>
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
		<?php
	}
}
