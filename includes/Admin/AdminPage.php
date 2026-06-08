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

	/**
	 * AddonManager instance.
	 *
	 * @var AddonManager
	 */
	private AddonManager $addon_manager;

	/**
	 * Constructor.
	 *
	 * @param AddonManager $addon_manager AddonManager instance.
	 */
	public function __construct( AddonManager $addon_manager ) {
		$this->addon_manager = $addon_manager;
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'handle_settings_save' ] );
	}

	/**
	 * Add admin menu page.
	 *
	 * @return void
	 */
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
	 * Handle settings form submission.
	 *
	 * @return void
	 */
	public function handle_settings_save(): void {
		if (
			! isset( $_POST['rockyjam_addons_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rockyjam_addons_nonce'] ) ), 'rockyjam_addons_save' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$enabled = [];
		if ( isset( $_POST['rockyjam_enabled_addons'] ) && is_array( $_POST['rockyjam_enabled_addons'] ) ) {
			$enabled = array_map( 'sanitize_key', $_POST['rockyjam_enabled_addons'] );
		}

		update_option( 'rockyjam_addons_enabled', $enabled );

		add_settings_error(
			'rockyjam_addons',
			'settings_saved',
			__( 'Settings saved.', 'rockyjam-addons' ),
			'success'
		);
	}

	/**
	 * Render the admin settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$available_addons = $this->addon_manager->get_available_addons();
		$enabled_addons   = (array) get_option( 'rockyjam_addons_enabled', [] );

		settings_errors( 'rockyjam_addons' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Enable or disable individual addons below.', 'rockyjam-addons' ); ?></p>

			<form method="post" action="">
				<?php wp_nonce_field( 'rockyjam_addons_save', 'rockyjam_addons_nonce' ); ?>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Addon', 'rockyjam-addons' ); ?></th>
							<th><?php esc_html_e( 'Status', 'rockyjam-addons' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $available_addons ) ) : ?>
						<tr><td colspan="2"><?php esc_html_e( 'No addons found.', 'rockyjam-addons' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $available_addons as $addon_id ) : ?>
							<?php $is_enabled = empty( $enabled_addons ) || in_array( $addon_id, $enabled_addons, true ); ?>
							<tr>
								<td><strong><?php echo esc_html( $addon_id ); ?></strong></td>
								<td>
									<label>
										<input
											type="checkbox"
											name="rockyjam_enabled_addons[]"
											value="<?php echo esc_attr( $addon_id ); ?>"
											<?php checked( $is_enabled ); ?>
										/>
										<?php esc_html_e( 'Enabled', 'rockyjam-addons' ); ?>
									</label>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Settings', 'rockyjam-addons' ) ); ?>
			</form>
		</div>
		<?php
	}
}
