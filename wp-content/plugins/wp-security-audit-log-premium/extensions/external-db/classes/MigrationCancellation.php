<?php
/**
 * Class WSAL_Ext_MigrationCancellation.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * Handler class for AJAX call to cancel a migration from/to external storage.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
final class WSAL_Ext_MigrationCancellation {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin – Instance of WSAL.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'wp_ajax_wsal_cancel_external_migration', array( $this, 'handle_ajax_call' ) );
	}

	/**
	 * Handles AJAX call to cancel the external migration.
	 */
	public function handle_ajax_call() {
		// Verify nonce.
		if ( false == wp_verify_nonce( $_POST['nonce'], 'wsal-cancel-external-migration' ) ) { // phpcs:ignore
			wp_send_json_error( esc_html__( 'Insecure request.', 'wp-security-audit-log' ) );
		}

		// Check if there is an ongoing migration.
		$migration_data = \WSAL\Helpers\Settings_Helper::get_option_value( 'migration_job', null );
		if ( is_null( $migration_data ) ) {
			wp_send_json_error( esc_html__( 'Migration has already finished or it was cancelled.', 'wp-security-audit-log' ) );
		}

		/**
		 * The migration is running as a background task therefore it can only be cancelled from the job itself. We create
		 * a special database option to indicate we want to cancel the migration. This is checked by the migration task
		 * on each run.
		 */
		\WSAL\Helpers\Settings_Helper::set_boolean_option_value( 'migration_job_cancel_pending', true );

		// Invoking the data migration class should trigger the cancellation almost instantly.
		new WSAL_Ext_DataMigration();

		wp_send_json_success( esc_html__( 'Migration will be cancelled shortly', 'wp-security-audit-log' ) );
	}

}
