<?php
/**
 * WSAL_Ext_DataMigration class.
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
 * Background process for handling the migration of activity log data to and from an external database.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
class WSAL_Ext_DataMigration extends WSAL_Vendor\WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'wsal_ext_db_data_migration';

	/**
	 * {@inheritDoc}
	 */
	protected function task( $item ) {

		$plugin = WpSecurityAuditLog::get_instance();

		// Check if the migration should be cancelled.
		$should_be_cancelled = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'migration_job_cancel_pending', false );
		if ( $should_be_cancelled ) {
			\WSAL\Helpers\Settings_Helper::delete_option_value( 'migration_job_cancel_pending' );
			\WSAL\Helpers\Settings_Helper::delete_option_value( 'migration_job' );
			$this->cancel_process();
			return false;
		}

		$direction = $item['direction'];

		// Migrate next batch of events while keeping the direction of migration in mind.
		$items_migrated = 0;
		if ( 'to_external' === $direction ) {
			$items_migrated = $plugin->external_db_util->migrate_occurrence( $item['connection'], $item['batch_size'] );
		} elseif ( 'from_external' === $direction ) {
			$items_migrated = $plugin->external_db_util->migrate_back_occurrence( $item['batch_size'] );
		}

		if ( 0 === $items_migrated ) {
			// All the data has been migrated.
			try {
				// Delete the migration job info to indicate that the migration is done.
				\WSAL\Helpers\Settings_Helper::delete_option_value( 'migration_job' );

				if ( 'to_external' === $direction ) {
					// Update the connection details.
					$plugin->external_db_util->update_connection_as_external( $item['connection'], $plugin );
				} elseif ( 'from_external' === $direction ) {
					$plugin->external_db_util->remove_external_storage_config();
				}
			} catch ( Exception $exception ) {
				$this->handle_error( $exception );
			}

			return false;
		}

		$item['events_migrated_count'] += $items_migrated;
		\WSAL\Helpers\Settings_Helper::set_option_value( 'migration_job', $item );

		return $item;
	}

	/**
	 * Handles given error.
	 *
	 * @param Exception $exception Exception.
	 */
	private function handle_error( $exception ) {
		// @todo handle migration error
		// -   maybe add the error to the database and show it in a dismissible notice
		// -   and give the user option to either cancel or retry
	}
}
