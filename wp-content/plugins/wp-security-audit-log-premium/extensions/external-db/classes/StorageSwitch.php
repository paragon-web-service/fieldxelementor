<?php
/**
 * Class WSAL_Ext_StorageSwitch.
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

use WSAL\Adapter\WSAL_Adapters_MySQL_Occurrence;
use WSAL\Adapter\WSAL_Adapters_MySQL_Meta;
/**
 * Abstract handler class for AJAX plugin storage switching.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
abstract class WSAL_Ext_StorageSwitch {

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
		$ajax_action  = $this->get_ajax_action();
		add_action( 'wp_ajax_' . $ajax_action, array( $this, 'handle_ajax_call' ) );
	}

	/**
	 * Figures out AJAX action for particular switch.
	 *
	 * @return string
	 */
	abstract protected function get_ajax_action();

	/**
	 * Handles AJAX call.
	 */
	public function handle_ajax_call() {
		// Verify nonce.
		$this->check_nonce();

		// Check the existing connection config.
		$this->check_existing_connection();

		// Run any further connection checks.
		$this->run_additional_connection_checks();

		// Check if we need to process any decision concerning the target database first.
		$target_db_check_needed = $this->process_target_database_decision();

		// Check if we need to process any decision concerning the source database next.
		$this->process_source_database_decision();

		$this->run_connectivity_checks();

		// Use output buffer in case error logging is enabled as non-existing table check will produce an error below.
		ob_start();

		// Check if the tables in the target database already exist.
		$connector = $this->get_target_database_connector();
		if ( $target_db_check_needed && $connector->is_installed() ) {
			/**
			 * Adapter returns an instance of WSAL_Adapters_MySQL_Occurrence.
			 *
			 * @var WSAL_Adapters_MySQL_Occurrence $target_occurrence_adapter
			 */
			$target_occurrence_adapter = $connector->get_adapter( 'Occurrence' );
			$target_db_events_count    = $target_occurrence_adapter->count();
			if ( $target_db_events_count > 0 ) {
				wp_send_json_error(
					array(
						'show_modal'   => 'wsal-external-db-target-data-choice-modal',
						'context_data' => $this->get_decision_modal_context_data(),
					)
				);
			}
		} else {
			// Create tables in the target database.
			$connector->install_all( true );
			$connector->get_adapter( 'Occurrence' )->create_indexes();
			$connector->get_adapter( 'Meta' )->create_indexes();
		}
		ob_clean();

		// Check if there are any events to migrate.
		$occurrence_model       = new WSAL_Models_Occurrence();
		$source_db_events_count = $occurrence_model->count();
		if ( $source_db_events_count > 0 ) {
			wp_send_json_error(
				array(
					'show_modal'   => 'wsal-external-db-source-data-choice-modal',
					'context_data' => $this->get_decision_modal_context_data(),
				)
			);
		}

		// No data to migrate, switch the storage.
		$this->switch_connection_with_no_data_migration();
	}

	/**
	 * Checks the nonce.
	 */
	protected function check_nonce() {
		if ( false === wp_verify_nonce( $_POST['nonce'], 'wsal-external-storage-switch' ) ) { // phpcs:ignore
			wp_send_json_error( esc_html__( 'Insecure request.', 'wp-security-audit-log' ) );
		}
	}

	/**
	 * Checks if the plugin uses the connection user wants to switch to and responds with wp_send_json_error if it does.
	 */
	protected function check_existing_connection() {
		// No checks by default.
	}

	/**
	 * Runs additional connection checks before the "decision" processing block. This should not do the actual
	 * connectivity test. Responds with wp_send_json_error if there are any problems.
	 */
	protected function run_additional_connection_checks() {
		// No checks by default.
	}

	/**
	 * Retrieves the migration direction.
	 *
	 * @return string Migration direction.
	 */
	abstract protected function get_migration_direction();

	/**
	 * Function is intended to add extra data to the data passed to the migration background.
	 *
	 * @param array $args Backgroung job arguments.
	 *
	 * @return array Updated list arguments that are passed to the migration background job.
	 */
	protected function get_data_migration_bg_job_args( $args ) {
		return $args;
	}

	/**
	 * Switches connection after the data in source database has been deleted. It must respond with wp_send_json_success
	 * that contains return "title" and "content".
	 *
	 * @return mixed
	 */
	abstract protected function switch_connection_after_data_deleted();

	/**
	 * Runs connectivity check after the "decision" processing block. Responds with wp_send_json_error if there are any
	 * problems.
	 */
	protected function run_connectivity_checks() {
		// No check by default.
	}

	/**
	 * Gets the database connector for the target database.
	 *
	 * @return WSAL_Connector_ConnectorInterface Connector.
	 */
	abstract protected function get_target_database_connector();

	/**
	 * Gets context data for the decision modals.
	 *
	 * @return array Context data for the decision modals
	 */
	protected function get_decision_modal_context_data() {
		return array();
	}

	/**
	 * Switches connection with no data migration.
	 */
	abstract protected function switch_connection_with_no_data_migration();

	/**
	 * Checks if there is a decision concerning the source database that needs to be processed.
	 *
	 * @since 4.4.0
	 */
	private function process_source_database_decision() {
		$decision = array_key_exists( 'decision', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['decision'] ) ) : null; // @codingStandardsIgnoreLine
		if ( ! is_null( $decision ) ) {
			/*
			 * If we received a decision attribute, all the checks already ran, and we should either start the data
			 * migration or delete the data and switch the storage.
			 */
			if ( 'migrate' === $decision ) {
				$job = new WSAL_Ext_DataMigration();

				$occurrence_model       = new WSAL_Models_Occurrence();
				$source_db_events_count = $occurrence_model->count();

				$direction   = $this->get_migration_direction();
				$bg_job_args = $this->get_data_migration_bg_job_args(
					array(
						'start_time'            => current_time( 'timestamp' ), // @codingStandardsIgnoreLine
						'events_migrated_count' => 0,
						'total_events_count'    => $source_db_events_count,
						'batch_size'            => WSAL_Ext_Settings::QUERY_LIMIT,
						'direction'             => $direction,
					)
				);
				$job->push_to_queue( $bg_job_args );

				$job->save()->dispatch();

				$success_message  = ( 'to_external' === $direction ) ? esc_html__( 'The migration of the activity log data from the local database to the external database has started.', 'wp-security-audit-log' ) : esc_html__( 'The migration of the activity log data from the external database to the local database has started.', 'wp-security-audit-log' );
				$success_message .= ' ' . sprintf(
					esc_html__( 'Click %s to close this prompt and see the progress.', 'wp-security-audit-log' ), // phpcs:ignore
					'<strong>' . esc_html__( 'Continue', 'wp-security-audit-log' ) . '</strong>'
				);
				wp_send_json_success(
					array(
						'title'   => esc_html__( 'Activity log migration has started', 'wp-security-audit-log' ),
						'content' => '<p>' . $success_message . '</p>',
					)
				);
			} elseif ( 'delete' === $decision ) {
				// Delete data in the source database and switch to target storage.
				$connector = $this->plugin::get_connector();
				$this->delete_data_and_tables( $connector );

				$this->switch_connection_after_data_deleted();
			}
		}
	}

	/**
	 * Checks if there is a decision concerning the target database that needs to be processed.
	 *
	 * If the user decided to merge the existing data in the target database, we don's need to do anything here.
	 *
	 * On the other hand, if the user decided to delete the existing data in the target database, we go ahead and delete
	 * the data and the tables.
	 *
	 * Correct next step and the response to the browser is determined in the caller function.
	 *
	 * @return bool True if target database check is still needed.
	 *
	 * @since 4.4.0
	 */
	private function process_target_database_decision() {
		$decision = array_key_exists( 'decision_target', $_POST ) ? \sanitize_text_field( \wp_unslash( $_POST['decision_target'] ) ) : null; // phpcs:ignore
		if ( is_null( $decision ) ) {
			return true;
		}

		if ( 'delete' === $decision ) {
			// User has decided to delete data in the target database.
			$connector = $this->get_target_database_connector();
			$this->delete_data_and_tables( $connector );
		}

		return false;
	}

	/**
	 * Deletes data and occurrences and meta tables using given connector.
	 *
	 * @param WSAL_Connector_ConnectorInterface $connector Database connector.
	 *
	 * @since 4.4.0
	 */
	private function delete_data_and_tables( $connector ) {
		$connector->purge_activity();
		$occ = new WSAL_Adapters_MySQL_Occurrence( $connector->get_connection() );
		$occ->uninstall();
		$meta = new WSAL_Adapters_MySQL_Meta( $connector->get_connection() );
		$meta->uninstall();
	}
}
