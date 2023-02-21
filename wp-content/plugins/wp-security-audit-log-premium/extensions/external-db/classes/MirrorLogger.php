<?php
/**
 * Mirror logger class.
 *
 * @package    wsal
 * @subpackage external-db
 */

use WSAL\Helpers\Logger;
use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger handling writing to all mirrors defined in the external DB extension.
 *
 * @since      4.3.0
 * @package    wsal
 * @subpackage external-db
 */
class WSAL_Ext_MirrorLogger extends WSAL_AbstractLogger {

	const FILE_NAME_FAILED_LOGS = 'non_mirrored_logs';

	/**
	 * The plugin keeps non logged events in the separate log file for every mirror - that is the base name of the file.
	 *
	 * @since 4.4.3.2
	 */
	const FILE_NAME_MIRROR_FAILED_LOGS = 'non_mirrored_events_';

	/**
	 * Flag which is used to determine if the scheduled mirrors logging must be executed or direct ones.
	 * There could be 2 types of mirrors - ones are logging directly to the given mirror and the others are called on later stage via Action Scheduler.
	 *
	 * @var boolean
	 *
	 * @since 4.4.3
	 */
	public $scheduled = false;

	/**
	 * Handles potential fatal errors. We don't do anything with the error at the moment. This is to stop the fatal
	 * errors from stopping the web application.
	 *
	 * @since 4.4.0
	 */
	public function exception_error_handler() {
		$error = error_get_last();
		if ( null !== $error ) { // phpcs:ignore
			// TODO handle fatal error, the array contains "type", "message", "file" and "line".
		}
	}

	/**
	 * Log alert via Action Scheduler.
	 *
	 * @param integer $type    - Alert code.
	 * @param array   $data    - Metadata.
	 * @param integer $date    (Optional) - Created on.
	 * @param integer $site_id (Optional) - Site id.
	 */
	public function log_schedule( $type, $data = array(), $date = null, $site_id = null ) {

		$this->scheduled = true;

		$this->log( $type, $data, $date, $site_id );

	}

	/**
	 * {@inheritDoc}
	 */
	public function log( $type, $data = array(), $date = null, $site_id = null ) {

		$mirrors = Settings_Helper::get_all_mirrors();
		if ( empty( $mirrors ) ) {
			return;
		}

		// Register error handler to capture fatal errors.
		register_shutdown_function( array( $this, 'exception_error_handler' ) );

		// Add event code to metadata otherwise we lose it.
		$data = array( 'Code' => $type ) + $data;

		// Prepare the log message.
		$alert_obj = $this->plugin->alerts->get_alert( $type );
		$message   = $alert_obj->get_message( $data, null, 0, 'plain' );

		// Bail if the libraries necessary for mirroring functionality are not available.
		if ( ! \WSAL_Extension_Manager::is_mirroring_available() ) {
			self::handle_failed_attempt( $data, $message, new \WP_Error( 'wsal_missing_mirroring_libraries' ) );

			Logger::log( 'Mirroring libraries are not available - the error is logged in the ' . self::FILE_NAME_FAILED_LOGS );

			return;
		}

		$monolog_helper = $this->plugin->external_db_util->get_monolog_helper();

		foreach ( $mirrors as $mirror ) {
			// Skip disabled mirror.
			if ( true !== $mirror['state'] ) {
				continue;
			}

			if ( ! isset( $mirror['direct'] ) ) {
				$mirror['direct'] = false;
			}

			if ( $this->scheduled && ! $mirror['direct'] ) {

				try {
					$connection = $this->plugin->external_db_util->get_connection( $mirror['connection'] );
					$monolog_helper->log( $connection, $mirror, $type, $message, $data );
				} catch ( Exception $exception ) {
					self::handle_failed_attempt( $data, $message, new \WP_Error( 'wsal_mirror_failed', $exception->getMessage() ), $mirror );

					Logger::log( 'Exception when logging mirror. ' . print_r( $mirror, true ) . ' ' . $exception->getMessage() );
				}

				continue;
			}

			if ( ! $this->scheduled && $mirror['direct'] ) {
				try {
					$connection = $this->plugin->external_db_util->get_connection( $mirror['connection'] );
					$monolog_helper->log( $connection, $mirror, $type, $message, $data );
				} catch ( Exception $exception ) {
					self::handle_failed_attempt( $data, $message, new \WP_Error( 'wsal_mirror_failed', $exception->getMessage() ), $mirror );

					Logger::log( 'Exception when logging mirror. ' . print_r( $mirror, true ) . ' ' . $exception->getMessage() );
				}
			}
		}
	}

	/**
	 * Handle failed attempt to send an event data to a monolog handler.
	 *
	 * This could be:
	 * - a failure to instantiate a handler using given configuration,
	 * - a communication error with the logging service
	 * - or missing software libraries necessary to communicate with external logging services.
	 *
	 * @param array     $data    Raw event data (doesn't contain message at this point).
	 * @param string    $message Event message formatted using a plain text formatter.
	 * @param \WP_Error $error   WordPress error object.
	 * @param array     $mirror  Optional. Not available when handling missing software libraries.
	 */
	public static function handle_failed_attempt( $data, $message, $error, $mirror = null ) {

		if ( null !== $mirror && isset( $mirror['failed'] ) && false === $mirror['failed']) {
			return;
		}

		// Get the custom logging path from settings.
		$custom_logging_path = \WSAL_Settings::get_working_dir_path_static();
		if ( is_wp_error( $custom_logging_path ) ) {
			return;
		}

		// Append message to the raw data.
		$data['message'] = $message;

		// Log error info.
		$error_info    = array();
		$error_code    = $error->get_error_code();
		$error_message = $error->get_error_message();
		if ( strlen( $error_code ) > 0 ) {
			array_push( $error_info, $error_code );
		}

		if ( strlen( $error_message ) > 0 ) {
			array_push( $error_info, $error_message );
		}

		if ( empty( $error_info ) ) {
			array_push( $error_info, 'Unknown error' );
		}

		$entry = implode(
			' ',
			array(
				'[' . date( 'Y-m-d H:i:s' ) . ']', // phpcs:ignore
				implode( ' ', $error_info ),
			)
		);

		if ( ! is_null( $mirror ) ) {
			$entry .= ', MIRROR: ' . $mirror['connection'];
		}
		$entry .= PHP_EOL;

		// Log raw event data itself.
		$entry .= json_encode( $data, JSON_PRETTY_PRINT ); // phpcs:ignore
		$entry .= PHP_EOL;
		$entry .= PHP_EOL;

		$file = $custom_logging_path . self::FILE_NAME_FAILED_LOGS . '.php';
		if ( null !== $mirror && ( ! isset( $mirror['failed'] ) || true === $mirror['failed'] ) ) {
			$file = $custom_logging_path . self::FILE_NAME_MIRROR_FAILED_LOGS . $mirror['name'] . '.php';
		}

		if ( file_exists( $file ) ) {
			$line = fgets( fopen( $file, 'r' ) );
			if ( false === strpos( $line, '<?php' ) ) {
				$fp_source = fopen( $file, 'r' );
				$fp_dest   = fopen( $file . '.tmp', 'w' ); // better to generate a real temp filename
				fwrite( $fp_dest, '<?php' . "\n" );
				while ( ! feof( $fp_source ) ) {
					fwrite( $fp_dest, fread( $fp_source, 8192 ) );
				}
				fclose( $fp_source );
				fclose( $fp_dest );
				unlink( $file );
				rename( $file . '.tmp', $file );
			}
		} else {
			$entry = '<?php' . "\n" . $entry;
		}

		@file_put_contents( $file, $entry, FILE_APPEND | LOCK_EX ); // phpcs:ignore
	}
}
