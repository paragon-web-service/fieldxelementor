<?php
/**
 * Class WSAL_Ext_StorageSwitchToLocal.
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
final class WSAL_Ext_StorageSwitchToLocal extends WSAL_Ext_StorageSwitch {

	/**
	 * {@inheritDoc}
	 */
	protected function get_ajax_action() {
		return 'wsal_MigrateBackOccurrence';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_migration_direction() {
		return 'from_external';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function switch_connection_after_data_deleted() {
		$this->delete_tables_and_remove_config(
			esc_html__( 'Activity log events deleted from local database', 'wp-security-audit-log' ),
			'<p>' . esc_html__( 'The plugin has successfully deleted the activity log events from the external database. Now the plugin is connected and will save the activity log events using the local database.', 'wp-security-audit-log' ) . '</p>'
		);
	}

	/**
	 * Deletes database tables, removes config and sends a success JSON response to the client.
	 *
	 * @param string $title   Pop-up title.
	 * @param string $content Pop-up content.
	 */
	private function delete_tables_and_remove_config( $title, $content ) {
		// This will cause the tables to be deleted, output buffering is here to capture table check error displayed if logging is enabled.
		ob_start();
		$this->plugin->external_db_util->migrate_back_occurrence( 1 );
		$this->plugin->external_db_util->remove_external_storage_config();
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
		return $this->plugin->get_connector( 'local' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function switch_connection_with_no_data_migration() {
		$this->delete_tables_and_remove_config(
			esc_html__( 'Switched to local database', 'wp-security-audit-log' ),
			'<p>' . esc_html__( 'Plugin is now connected to the local database.', 'wp-security-audit-log' ) . '</p>'
		);
	}
}
