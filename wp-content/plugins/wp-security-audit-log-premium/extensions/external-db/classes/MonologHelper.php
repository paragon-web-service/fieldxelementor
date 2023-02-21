<?php
/**
 * Class WSAL_Ext_MonologHelper.
 *
 * @since      4.3.0
 * @package    wsal
 * @subpackage external-db
 */

use WSAL\Helpers\PHP_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper object that optimizes logging using monolog handlers.
 *
 * @since      4.3.0
 * @package    wsal
 * @subpackage external-db
 */
class WSAL_Ext_MonologHelper {

	/**
	 * Plugin instance.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * WSAL_Ext_MonologHelper constructor.
	 *
	 * @param WpSecurityAuditLog $plugin Plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Logs and event.
	 *
	 * @param array  $connection Connection config data.
	 * @param array  $mirror     Raw mirror data. This is not guaranteed as the function is sometimes called in context
	 *                           where mirror is not available.
	 * @param int    $code       Event code.
	 * @param string $message Message.
	 * @param array  $metadata Metadata.
	 *
	 * @return bool
	 * @throws Exception Some sort of error occurred.
	 */
	public function log( $connection, $mirror, $code, $message, $metadata ) {
		$mirror_types = $this->plugin->external_db_util->get_mirror_types();
		if ( ! is_array( $connection ) || empty( $connection ) ) {
			$connection = $this->plugin->external_db_util->get_connection( $mirror['connection'] );
		}

		if ( ! is_array( $connection ) ) {
			// Connection not valid.
			throw new Exception( 'Invalid connection data. The link between the mirror and the connection is probably broken.' );
		}

		$connection_type = $connection['type'];
		if ( ! array_key_exists( $connection_type, $mirror_types ) ) {
			// Rnrecognized mirror type.
			throw new Exception( 'Unrecognized mirror type: ' . $connection_type );
		}

		$mirror_type = $mirror_types[ $connection_type ];
		/** @var WSAL_Ext_ConnectionInterface $mirror_instance */
		$mirror_instance = new $mirror_type['class']( $this->plugin, $connection );

		// Check if event is allowed to be logged to this mirror.
		if ( is_array( $mirror ) && array_key_exists( 'filter', $mirror ) && ! empty( $mirror['filter'] ) ) {

			if ( 'event-codes' === $mirror['filter'] && ! empty( $mirror['event_codes'] ) ) {
				if ( ! in_array( $code, $mirror['event_codes'] ) ) { // phpcs:ignore
					return false;
				}
			} elseif ( 'except-codes' === $mirror['filter'] && ! empty( $mirror['exception_codes'] ) ) {
				if ( in_array( $code, $mirror['exception_codes'] ) ) { // phpcs:ignore
					return false;
				}
			}
		}

		if ( ! empty( $mirror['severity_levels'] ) ) {
			if ( ! in_array( $metadata['Severity'], $mirror['severity_levels'] ) ) { // phpcs:ignore
				return false;
			}
		}

		// Create a log channel.
		$logger_name = is_array( $mirror ) && array_key_exists( 'source', $mirror ) ? $mirror['source'] : sanitize_title( get_bloginfo( 'url' ) );
		$logger      = new \WSAL_Vendor\Monolog\Logger(
			$logger_name,
			array(),
			array(
				new WSAL_Ext_MetadataTimestampProcessor(),
			),
			new DateTimeZone( 'GMT' )
		);
		$logger->useMicrosecondTimestamps( false );

		$username = WSAL_Utilities_UsersUtils::get_username( $metadata );
		if ( ! empty( $username ) ) {
			$user = get_user_by( 'login', $username );
			if ( $user instanceof \WP_User ) {
				$metadata['User'] = WSAL_Utilities_UsersUtils::get_display_label( $this->plugin, $user );
			}
		}

		global $mirror_tags;

		if ( isset( $mirror['tags'] ) && ! empty( $mirror['tags'] ) ) {
			$mirror_tags = $mirror['tags'];
        }

		$handler = $mirror_instance->get_monolog_handler();

		if ( is_array( $mirror ) && array_key_exists( 'tags', $mirror ) && ! empty( $mirror['tags'] ) && method_exists( $handler, 'addTag' ) ) {
			$handler->addTag( PHP_Helper::string_to_array( $mirror['tags'] ) );
		}
		if ( is_null( $handler ) ) {
			throw new Exception( 'Failed to create Monolog handler for mirror ' . $mirror['name'] );
		}

		$handler_successfully_added_to_logger = false;
		$logger->pushHandler( $handler );
		$handler_successfully_added_to_logger = true;

		try {
			// Work out the log level from the severity.
			$severity           = array_key_exists( 'Severity', $metadata ) ? $metadata['Severity'] : \WSAL_Vendor\Monolog\Logger::DEBUG;
			$log_level          = $this->get_log_level( $severity );
			$processed_metadata = $mirror_instance->pre_process_metadata( $metadata, $mirror );
			$processed_message  = $mirror_instance->pre_process_message( $message, $processed_metadata );

			/**
			 * WSAL Filter: `wsal_event_data_before_mirror`
			 *
			 * Filters event data before logging it to a mirror service.
			 *
			 * @param array $processed_metadata Event metadata.
			 * @param integer $connection_type Type of mirror connection, e.g. "slack".
			 *
			 * @since 4.3.4
			 */
			$filtered_metadata = apply_filters( 'wsal_event_data_before_mirror', $processed_metadata, $connection_type );

			$logger->log( $log_level, $processed_message, $filtered_metadata );
		} catch ( Exception $exception ) {
			throw $exception;
		} finally {
			if ( $handler_successfully_added_to_logger ) {
				$logger->popHandler();
			}
		}

		return true;
	}


	/**
	 * Translates severity used by the plugin to the monolog log levels. Supports legacy severity number used prior to version 4.3.
	 *
	 * @param int $severity Event severity as defined by the plugin.
	 *
	 * @return int Monolog log level.
	 */
	public function get_log_level( $severity ) {
		switch ( $severity ) {
			// WSAL_CRITICAL, E_CRITICAL.
			case 500:
			case 1:
			case 400:
			case 6:
				return \WSAL_Vendor\Monolog\Logger::ALERT;

			// WSAL_MEDIUM, E_WARNING.
			case 300:
			case 10:
				return \WSAL_Vendor\Monolog\Logger::WARNING;

			// WSAL_LOW, E_NOTICE.
			case 250:
			case 15:
				return \WSAL_Vendor\Monolog\Logger::NOTICE;

			// WSAL_INFORMATIONAL.
			case 200:
			case 20:
			default:
				return \WSAL_Vendor\Monolog\Logger::INFO;
		}
	}
}
