<?php
/**
 * Class WSAL_Ext_ConnectionInterface.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External connection interface.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
interface WSAL_Ext_ConnectionInterface {

	/**
	 * Gets the connection type.
	 *
	 * @return string
	 */
	public static function get_type();

	/**
	 * Gets human-readable connection name.
	 *
	 * Note: the rest of the plugin uses "name" to refer to the connection type.
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Build and return the connection configuration definition. This is effectively a list of labels and fields to
	 * display connection settings and validate settings data.
	 *
	 * @return array
	 */
	public static function get_config_definition();

	/**
	 * Checks the requirements for the connection.
	 *
	 * @return array Associative array of error messages if some of the requirements are not met. Keys are error IDs and values are localised error messages. Empty if all requirements are met.
	 */
	public static function check_requirements();

	/**
	 * Initializes the Monolog handler.
	 *
	 * @return \WSAL_Vendor\Monolog\Handler\Handler
	 * @throws Exception Thrown if there was a problem creating the Monolog handler.
	 */
	public function get_monolog_handler();

	/**
	 * Allows message pre-processing. Use this method to alter the message prior to sending to the logger.
	 *
	 * @param string $message  Message.
	 * @param array  $metadata Metadata.
	 *
	 * @return string
	 */
	public function pre_process_message( $message, $metadata );

	/**
	 * Allows metadata pre-processing. Use this method to alter the metadata prior to sending to the logger.
	 *
	 * @param array $metadata Event metadata.
	 * @param array $mirror Mirror configuration data. This can be empty. For example when a connection test runs.
	 *
	 * @return array
	 */
	public function pre_process_metadata( $metadata, $mirror );

	/**
	 * This function provides a way to supply an alternative error message for a missing software requirement.
	 *
	 * @param string $original_error_message Error message produced by the php-requirements-checker library.
	 *
	 * @return string Alternative error message. Can include HTML markup.
	 * @since 4.3.2
	 */
	public static function get_alternative_error_message();
}
