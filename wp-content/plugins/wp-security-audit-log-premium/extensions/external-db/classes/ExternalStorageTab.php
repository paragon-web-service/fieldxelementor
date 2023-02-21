<?php
/**
 * View: External storage tab
 *
 * Integrations / External storage tab view.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */

use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * Integrations / External storage tab class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
final class WSAL_Ext_ExternalStorageTab {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * Integration settings object.
	 *
	 * @var WSAL_Ext_Settings
	 */
	private $integrations_settings;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin                Instance of WSAL.
	 * @param WSAL_Ext_Settings  $integrations_settings Integration settings object.
	 */
	public function __construct( $plugin, $integrations_settings ) {
		$this->plugin                = $plugin;
		$this->integrations_settings = $integrations_settings;

		add_action( 'wsal_ext_db_header', array( $this, 'enqueue_styles' ) );
		add_action( 'wsal_ext_db_footer', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Tab Mirroring Render.
	 */
	public function render() {
		$allowed_tags = array(
			'a' => array(
				'href'   => true,
				'target' => true,
			),
		);
		$help_link    = sprintf(
		/* Translators: 1 is the help type being linked */
			__( 'Read more on %1$s.', 'wp-security-audit-log' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( 'https://wpactivitylog.com/support/kb/store-wordpress-activity-log-external-database/' ),
				__( 'external storage for activity logs', 'wp-security-audit-log' )
			)
		);
		?>
		<p><?php esc_html_e( 'In this section you can configure the plugin to store the WordPress activity log in an external storage rather than the WordPress database. This could be another database on a remote server.', 'wp-security-audit-log' ); ?><?php echo wp_kses( $help_link, $allowed_tags ); ?></p>
		<?php
		$migration_job_data = \WSAL\Helpers\Settings_Helper::get_option_value( 'migration_job', null );
		if ( ! is_null( $migration_job_data ) ) {
			$this->render_migration_in_progress_widget( $migration_job_data );
		} else {
			$this->render_current_connection_widget();
		}
	}

	/**
	 * Renders migration in progress widget.
	 *
	 * @param array $migration_job_data Migration job data.
	 *
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	private function render_migration_in_progress_widget( $migration_job_data ) {
		$direction = $migration_job_data['direction'];

		$elapsed_time = current_time( 'timestamp' ) - $migration_job_data['start_time']; // phpcs:ignore
		$top_label    = '';
		$note         = '';
		if ( 'to_external' === $direction ) {
			$top_label = esc_html__( 'Migrating data to external database...', 'wp-security-audit-log' );
			$note      = esc_html__( 'new events are still being saved to the local database.', 'wp-security-audit-log' );
		} elseif ( 'from_external' === $direction ) {
			$top_label = esc_html__( 'Migrating data to local database...', 'wp-security-audit-log' );
			$note      = esc_html__( 'new events are still being saved to the external database.', 'wp-security-audit-log' );
		}

		$is_cancellation_pending = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'migration_job_cancel_pending', false );
		?>
		<div class="card migration-progress">
			<h3><?php echo $top_label; ?></h3>
			<span class="time">
				<?php printf( '%02d:%02d:%02d', $elapsed_time / 3600 % 24, $elapsed_time / 60 % 60, $elapsed_time % 60 ); ?>
			</span>
			<p>
				<?php
				printf(
					esc_html__( 'Processed: %1$d out of %2$d', 'wp-security-audit-log' ), // phpcs:ignore
					$migration_job_data['events_migrated_count'],
					$migration_job_data['total_events_count']
				);
				?>
			</p>
			<p class="note">
				<span><?php esc_html_e( 'Note:', 'wp-security-audit-log' ); ?></span> <?php echo $note; ?>
			</p>
			<?php if ( $is_cancellation_pending ) : ?>
				<p class="notice notice-info"
					style="margin-bottom: 0; padding: 5px 12px;"><?php esc_html_e( 'Migration cancellation is pending. It will be cancelled next time the associated background task runs.', 'wp-security-audit-log' ); ?></p>
			<?php else : ?>
				<input type="button" class="button button-primary" name="wsal-external-migration-cancel"
					value="<?php esc_attr_e( 'Cancel migration', 'wp-security-audit-log' ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'wsal-cancel-external-migration' ) ); ?>"/>
				<span class="spinner" style="float: none;'"></span>
			<?php endif; ?>
		</div><!-- /.card -->
		<?php
	}

	/**
	 * Renders current connection widget.
	 *
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	private function render_current_connection_widget() {
		$using_external_storage = $this->check_setting();
		?>
		<div class="card">
			<?php wp_nonce_field( 'wsal-external-storage-switch', 'wsal-external-storage-switch-nonce' ); ?>
			<?php if ( $using_external_storage ) : ?>
				<p><?php esc_html_e( 'Plugin is using external storage for activity logs at the moment.', 'wp-security-audit-log' ); ?></p>
				<input type="button" name="wsal-switch-to-local-db" id="wsal-switch-to-local-db"
						class="button button-primary" data-remodal-target="wsal-external-db-switch-to-local-modal"
						value="<?php esc_attr_e( 'Switch to local database', 'wp-security-audit-log' ); ?>" />
				<input type="hidden" id="adapter-test-nonce"
						value="<?php echo esc_attr( wp_create_nonce( 'wsal_adapter-test' ) ); ?>" />
				<input type="button" data-connection="adapter" id="adapter-test" class="button button-primary"
						value="<?php esc_attr_e( 'Test Connection', 'wp-security-audit-log' ); ?>" />
				<?php $this->render_switch_back_to_local_connection_modal(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Plugin is using local storage for activity logs at the moment.', 'wp-security-audit-log' ); ?></p>
				<input type="button" name="wsal-switch-to-external-db" id="wsal-switch-to-external-db"
						class="button button-primary" data-remodal-target="wsal-external-db-connection-modal"
						value="<?php esc_attr_e( 'Switch to external database', 'wp-security-audit-log' ); ?>" />
				<?php $this->render_external_connection_selection_modal(); ?>
			<?php endif; ?>
			<?php $this->render_existing_source_data_choice_modal( $using_external_storage ); ?>
			<?php $this->render_existing_target_data_choice_modal( $using_external_storage ); ?>
		</div><!-- /.card -->
		<?php
		$this->render_connection_details();
	}

	/**
	 * Checks if there is the adapter setting.
	 *
	 * @return bool true|false
	 */
	protected function check_setting() {
		$config = \WSAL\Helpers\Settings_Helper::get_option_value( 'adapter-connection' );

		return ! empty( $config );
	}

	/**
	 * Renders connection modal to switch back to local connection.
	 */
	private function render_switch_back_to_local_connection_modal() {
		?>
		<div class="remodal" data-remodal-id="wsal-external-db-switch-to-local-modal"
			data-remodal-options="hashTracking: false, closeOnConfirm: false, closeOnOutsideClick: false, closeOnEscape: false">
			<h3><?php esc_html_e( 'Switch to local storage', 'wp-security-audit-log' ); ?></h3>
			<p><?php esc_html_e( 'Do you want to change the plugin settings to start using the local database for storing the logs?', 'wp-security-audit-log' ); ?></p>
			<br>
			<button data-remodal-action="cancel"
					class="remodal-cancel"><?php esc_html_e( 'Cancel', 'wp-security-audit-log' ); ?></button>
			<button data-remodal-action="confirm"
					class="remodal-confirm"><?php esc_html_e( 'Switch connection', 'wp-security-audit-log' ); ?></button>
			<span class="spinner"></span>
		</div>
		<?php
	}

	/**
	 * Renders modal for external connection selection.
	 */
	private function render_external_connection_selection_modal() {
		?>
		<div class="remodal" data-remodal-id="wsal-external-db-connection-modal"
			data-remodal-options="hashTracking: false, closeOnConfirm: false, closeOnOutsideClick: false, closeOnEscape: false">
			<h3><?php esc_html_e( 'Select connection for external storage', 'wp-security-audit-log' ); ?></h3>
			<form method="post" autocomplete="off">
				<input type="hidden" name="page"
					value="<?php echo \esc_html( \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) ); ?>"/>
				<?php wp_nonce_field( 'external-db-form', 'wsal_external_db' ); ?>
				<div class="wsal-setting-option">
					<table class="form-table">
						<th>
							<label for="AdapterConnection"><?php esc_html_e( 'Connection', 'wp-security-audit-log' ); ?></label>
						</th>
						<td><?php $this->integrations_settings->get_connection_field( 'adapter' ); ?></td>
					</table>
				</div>
			</form>
			<br>
			<button data-remodal-action="cancel"
					class="remodal-cancel"><?php esc_html_e( 'Cancel', 'wp-security-audit-log' ); ?></button>
			<button data-remodal-action="confirm"
					class="remodal-confirm"><?php esc_html_e( 'Switch connection', 'wp-security-audit-log' ); ?></button>
			<span class="spinner"></span>
		</div>
		<?php
	}

	/**
	 * Renders modal communicating that there is some data in the source database.
	 *
	 * @param bool $using_external_storage True if external storage is in use.
	 */
	private function render_existing_source_data_choice_modal( $using_external_storage ) {
		$title   = $using_external_storage ? esc_html__( 'Activity log events in the external (source) database', 'wp-security-audit-log' ) : esc_html__( 'Activity log events in the local (source) database', 'wp-security-audit-log' );
		$message = $using_external_storage ? esc_html__( 'Would you like to move these events to the local database or delete them and start afresh?', 'wp-security-audit-log' ) : esc_html__( 'Would you like to move these events to the external database or delete them and start afresh?', 'wp-security-audit-log' );
		?>
		<div class="remodal" data-remodal-id="wsal-external-db-source-data-choice-modal"
			data-direction="<?php echo $using_external_storage ? 'from_external' : 'to_external'; ?>"
			data-remodal-options="hashTracking: false, closeOnConfirm: false, closeOnCancel: false, closeOnOutsideClick: false, closeOnEscape: false">
			<h3><?php echo $title; ?></h3>
			<p><?php esc_html_e( 'There are activity log events in the source database.', 'wp-security-audit-log' ); ?></p>
			<p><?php echo $message; ?></p>
			<br>
			<button data-remodal-action="cancel"
					class="remodal-cancel"><?php esc_html_e( 'Migrate existing events', 'wp-security-audit-log' ); ?></button>
			<button data-remodal-action="confirm"
					class="remodal-confirm"><?php esc_html_e( 'Delete existing events', 'wp-security-audit-log' ); ?></button>
			<span class="spinner"></span>
		</div>
		<?php
	}

	/**
	 * Renders modal communicating that there is some data in the target database.
	 *
	 * @param bool $using_external_storage True if external storage is in use.
	 */
	private function render_existing_target_data_choice_modal( $using_external_storage ) {
		$title   = $using_external_storage ? esc_html__( 'Activity log events in the local (target) database', 'wp-security-audit-log' ) : esc_html__( 'Activity log events in the external (target) database', 'wp-security-audit-log' );
		$message = $using_external_storage ? esc_html__( 'Would you like to keep these events in the local database or delete them and continue?', 'wp-security-audit-log' ) : esc_html__( 'Would you like to keep these events in the external database or delete them and continue?', 'wp-security-audit-log' );
		?>
		<div class="remodal" data-remodal-id="wsal-external-db-target-data-choice-modal"
				data-direction="<?php echo $using_external_storage ? 'from_external' : 'to_external'; ?>"
				data-remodal-options="hashTracking: false, closeOnConfirm: false, closeOnCancel: false, closeOnOutsideClick: false, closeOnEscape: false">
			<h3><?php echo $title; ?></h3>
			<p><?php esc_html_e( 'There are activity log events in the target database.', 'wp-security-audit-log' ); ?></p>
			<p><?php echo $message; ?></p>
			<br>
			<button data-remodal-action="cancel"
					class="remodal-cancel"><?php esc_html_e( 'Delete existing events', 'wp-security-audit-log' ); ?></button>
			<button data-remodal-action="confirm"
					class="remodal-confirm"><?php esc_html_e( 'Merge data', 'wp-security-audit-log' ); ?></button>
			<span class="spinner"></span>
		</div>
		<?php
	}

	/**
	 * Renders connection details.
	 */
	private function render_connection_details() {

		$connection_name  = Settings_Helper::get_option_value( 'adapter-connection' );
		$adapter_name     = esc_html__( 'Default', 'wp-security-audit-log' );
		$adapter_hostname = esc_html__( 'Current', 'wp-security-audit-log' );
		if ( ! empty( $connection_name ) ) {
			$connection = $this->plugin->external_db_util->get_connection( $connection_name );
			if ( is_array( $connection ) ) {
				$adapter_name     = $connection['db_name'];
				$adapter_hostname = $connection['hostname'];
			}
		}
		?>
		<p>
		<span class="current-connection">
			<?php
			printf(
				esc_html__( 'Currently connected to database %1$s on server %2$s', 'wp-security-audit-log' ), // phpcs:ignore
				'<strong>' . esc_html( $adapter_name ) . '</strong>',
				'<strong>' . esc_html( $adapter_hostname ) . '</strong>'
			);
			?>
							</span>
		</p>
		<?php
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_styles() {
		$this->integrations_settings->enqueue_remodal();
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_scripts() {
		// @todo separate the script related to this screen only
	}
}
