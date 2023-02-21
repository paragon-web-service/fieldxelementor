<?php
/**
 * Sensor: Request.
 *
 * Request sensor class file.
 *
 * @since      1.0.0
 *
 * @package    wsal
 * @subpackage sensors
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes the Request.log.php file.
 *
 * @package    wsal
 * @subpackage sensors
 */
class WSAL_Sensors_Request extends WSAL_AbstractSensor {
	/**
	 * Environment Variables.
	 *
	 * @var array
	 */
	protected static $env_vars = array();

	/**
	 * {@inheritDoc}
	 */
	public function hook_events() {
		if ( method_exists( $this->plugin->settings(), 'is_request_logging_enabled' ) ) {
			if ( $this->plugin->settings()->is_request_logging_enabled() ) {
				add_action( 'shutdown', array( $this, 'event_shutdown' ) );
			}
		}
	}

	/**
	 * Fires just before PHP shuts down execution.
	 */
	public function event_shutdown() {
		// Filter global arrays for security.
		$post_array   = filter_input_array( INPUT_POST );
		$server_array = filter_input_array( INPUT_SERVER );

		// get the custom logging path from settings.
		$custom_logging_path = \WSAL_Settings::get_working_dir_path_static();
		if ( is_wp_error( $custom_logging_path ) ) {
			return;
		}

		$file = $custom_logging_path . 'Request.log.php';

		$request_method = isset( $server_array['REQUEST_METHOD'] ) ? $server_array['REQUEST_METHOD'] : false;
		$request_uri    = isset( $server_array['REQUEST_URI'] ) ? $server_array['REQUEST_URI'] : false;

		$line = '[' . date( 'Y-m-d H:i:s' ) . '] ' // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			. $request_method . ' '
			. $request_uri . ' '
			. ( ! empty( $post_array ) ? str_pad( PHP_EOL, 24 ) . json_encode( $post_array ) : '' ) // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			. ( ! empty( self::$env_vars ) ? str_pad( PHP_EOL, 24 ) . json_encode( self::$env_vars ) : '' ) // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			. PHP_EOL;

		if ( ! file_exists( $file ) && ! file_put_contents( $file, '<' . '?php die(\'Access Denied\'); ?>' . PHP_EOL ) ) { // phpcs:ignore
			$this->log_error(
				'Could not initialize request log file',
				array(
					'file' => $file,
				)
			);

			return;
		}

		$f = fopen( $file, 'a' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( $f ) {
			if ( ! fwrite( $f, $line ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
				$this->log_warn(
					'Could not write to log file',
					array(
						'file' => $file,
					)
				);
			}
			if ( ! fclose( $f ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				$this->log_warn(
					'Could not close log file',
					array(
						'file' => $file,
					)
				);
			}
		} else {
			$this->log_warn(
				'Could not open log file',
				array(
					'file' => $file,
				)
			);
		}
	}

	/**
	 * Sets $envvars element with key and value.
	 *
	 * @param mixed $name  - Key name of the variable.
	 * @param mixed $value - Value of the variable.
	 */
	public static function SetVar( $name, $value ) {
		self::$env_vars[ $name ] = $value;
	}

	/**
	 * Copy data array into $envvars array.
	 *
	 * @param array $data - Data array.
	 */
	public static function SetVars( $data ) {
		foreach ( $data as $name => $value ) {
			self::SetVar( $name, $value );
		}
	}
}
