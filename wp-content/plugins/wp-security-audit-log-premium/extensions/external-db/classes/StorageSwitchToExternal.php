<?php
/**
 * Class WSAL_Ext_StorageSwitchToExternal.
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
 * Abstract handler class for AJAX plugin storage switching.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
final class WSAL_Ext_StorageSwitchToExternal extends WSAL_Ext_StorageSwitch {

	/**
	 * Selected connection name.
	 *
	 * @var string
	 */
	private $connection;

	/**
	 * Selected database connection config.
	 *
	 * @var array
	 */
	private $db_connection;

	/**
	 * {@inheritDoc}
	 */
	public function run_additional_connection_checks() {
		// Check selected connection.
		$connection = isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : false; // phpcs:ignore
		if ( empty( $connection ) ) {
			wp_send_json_error( esc_html__( 'Connection name parameter is missing.', 'wp-security-audit-log' ) );
		}

		// Clear old external storage connection just to be safe (this should not be possible as of version 4.3.2).
		$old_conn_name = $this->plugin->external_db_util->get_setting_by_name( 'adapter-connection', false );
		if ( $old_conn_name && $connection !== $old_conn_name ) {
			// Get old connection object.
			$old_connection = $this->plugin->external_db_util->get_connection( $old_conn_name );

			// Clear old connection used for.
			$old_connection['used_for'] = '';

			// Save the old connection object.
			$this->plugin->external_db_util->save_connection( $old_connection['name'], $old_connection );
		}

		$this->connection = $connection;

		// Get connection option.
		$db_connection = $this->plugin->external_db_util->get_connection( $connection );

		// Error handling.
		if ( empty( $db_connection ) ) {
			wp_send_json_error(
				sprintf(
					esc_html__( 'Connection %s not found.', 'wp-security-audit-log' ), // phpcs:ignore
					'<strong>' . $connection . '</strong>'
				)
			);
		}

		$this->db_connection = $db_connection;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_ajax_action() {
		return 'wsal_MigrateOccurrence';
	}
	/**
	 * {@inheritDoc}
	 */
	protected function check_existing_connection() {
		// Stop if the system is already using the external connection (this could happen if the UI was out of sync).
		$current_connection = \WSAL\Helpers\Settings_Helper::get_option_value( 'adapter-connection' );
		if ( ! empty( $current_connection ) ) {
			wp_send_json_error( esc_html__( 'Plugin already uses an external storage.', 'wp-security-audit-log' ) );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_data_migration_bg_job_args( $args ) {
		$args['connection'] = $this->connection;

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_migration_direction() {
		return 'to_external';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function switch_connection_after_data_deleted() {
		$this->delete_tables_and_update_connection(
			esc_html__( 'Activity log events deleted from local database', 'wp-security-audit-log' ),
			'<p>' . sprintf(
				esc_html__( 'The plugin has successfully deleted the activity log events from the local database. Now the plugin is connected and will save the activity log events using the external database connection %s.', 'wp-security-audit-log' ), // phpcs:ignore
				'<strong>' . $this->connection . '</strong>'
			) . '</p>'
		);
	}

	/**
	 * Deletes database tables and updated connection.
	 *
	 * @param string $title Modal title.
	 * @param string $content Modal content.
	 */
	private function delete_tables_and_update_connection( $title, $content ) {
		// This will cause the tables to be deleted, output buffering is here to capture table check error displayed if logging is enabled.
		ob_start();
		// I have the feeling that this is not suppose to be here after the user choose delete option, but additional checks are required
		$this->plugin->external_db_util->migrate_occurrence( $this->connection, 1 );
		$this->plugin->external_db_util->update_connection_as_external( $this->connection );
		ob_clean();

		wp_send_json_success(
			array(
				'title'   => $title,
				'content' => $content,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_target_database_connector() {
		return $this->plugin->get_connector( $this->db_connection );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function switch_connection_with_no_data_migration() {
		$this->delete_tables_and_update_connection(
			esc_html__( 'Switched to external database', 'wp-security-audit-log' ),
			'<p>' . sprintf(
				esc_html__( 'Plugin is now connected to an external database %s.', 'wp-security-audit-log' ), // phpcs:ignore
				'<strong>' . $this->connection . '</strong>'
			) . '</p>'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_decision_modal_context_data() {
		return array(
			'connection-name' => $this->connection,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function run_connectivity_checks() {
		// Check connection.
		$connection_ok = WSAL_Connector_ConnectorFactory::check_config( $this->db_connection );
		if ( ! $connection_ok ) {
			wp_send_json_error(
				esc_html__( 'Cannot connect to the selected database connection.', 'wp-security-audit-log' )
			);
		}
	}
}
