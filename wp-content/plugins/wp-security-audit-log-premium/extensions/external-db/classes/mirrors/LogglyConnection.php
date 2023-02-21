<?php
/**
 * Class WSAL_Ext_Mirrors_LogglyConnection.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */

namespace WSAL\Extensions\ExternalDB\Mirrors;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loggly connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
class WSAL_Ext_Mirrors_LogglyConnection extends \WSAL_Ext_AbstractConnection {

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return 'loggly';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return esc_html__( 'Loggly', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_config_definition() {
		return array(
			'desc'   => esc_html__( 'General mirror connection description.', 'wp-security-audit-log' ),
			'fields' => array(
				'token' => array(
					'label'    => esc_html__( 'Loggly token', 'wp-security-audit-log' ),
					'type'     => 'text',
					'required' => true,
					'desc'     => sprintf(
						esc_html__( 'The Loggly token required here is the "Customer token" and you can get it from the following URL: %s', 'wp-security-audit-log' ), // phpcs:ignore
						'https://[your_subdomain].loggly.com/tokens'
					),
				),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function add_extra_requirements() {
		if ( ! extension_loaded( 'curl' ) ) { 
			self::$error_message = \esc_html__( 'PHP extension curl is required', 'wp-security-audit-log' );
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_monolog_handler() {

		$token = array_key_exists( 'token', $this->connection ) ? $this->connection['token'] : '';
		if ( empty( $token ) ) {
			throw new Exception( 'Loggly token is missing.' );
		}

		return new \WSAL_Vendor\Monolog\Handler\LogglyHandler( $token );
	}
}
