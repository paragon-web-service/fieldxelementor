<?php
/**
 * Class WSAL_Ext_Mirrors_SyslogConnection.
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
 * Syslog connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
class WSAL_Ext_Mirrors_SyslogConnection extends \WSAL_Ext_AbstractConnection {

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return 'syslog';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return esc_html__( 'Syslog Server', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_config_definition() {
		return array(
			'desc'   => esc_html__( 'General mirror connection description.', 'wp-security-audit-log' ),
			'fields' => array(
				'destination' => array(
					'label'    => esc_html__( 'Syslog Location', 'wp-security-audit-log' ),
					'type'     => 'radio',
					'required' => true,
					'options'  => array(
						'local'  => array(
							'label'   => esc_html__( 'Write to local syslog file', 'wp-security-audit-log' ),
							'checked' => true,
						),
						'remote' => array(
							'label'     => esc_html__( 'Send messages to remote syslog server', 'wp-security-audit-log' ),
							'subfields' => array(
								'host'    => array(
									'label'      => esc_html__( 'IP Address / Hostname', 'wp-security-audit-log' ),
									'type'       => 'text',
									'required'   => true,
									'validation' => 'ipAddress',
									'error'      => esc_html__( 'Invalid IP address or hostname', 'wp-security-audit-log' ),
								),
								'port'    => array(
									'label'      => esc_html__( 'Port', 'wp-security-audit-log' ),
									'type'       => 'text',
									'required'   => true,
									'validation' => 'port',
									'error'      => esc_html__( 'Invalid Port', 'wp-security-audit-log' ),
								),
								'tcp-udp' => array(
									'label'   => esc_html__( 'Select TCP or UDP connection', 'wp-security' ),
									'type'    => 'radio',
									'options' => array(
										'tcp' => array(
											'label'   => esc_html__( 'TCP', 'wp-security' ),
											'checked' => true,
										),
										'udp' => array(
											'label'   => esc_html__( 'UDP', 'wp-security' ),
											'checked' => false,
										),
									),
								),
								'tls'     => array(
									'label' => esc_html__( 'Enable to use SSL/TLS to connect', 'wp-security-audit-log' ),
									'type'  => 'checkbox',
								),
							),
						),
					),
				),

			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_monolog_handler() {
		$destination = array_key_exists( 'destination', $this->connection ) ? $this->connection['destination'] : 'local';
		if ( array_key_exists( 'location', $this->connection ) ) {
			// Legacy settings support.
			$destination = $this->connection['location'];
		}
		if ( 'local' === $destination ) {
			return new \WSAL_Vendor\Monolog\Handler\SyslogHandler( 'Security_Audit_Log' );
		} elseif ( 'remote' === $destination ) {
			if ( isset( $this->connection['remote-tls'] ) ) {
				return new \WSAL_Vendor\Monolog\Handler\SocketHandler( 'tls://' . $this->connection['remote-host'] . ':' . $this->connection['remote-port'] );
			} else {
				if ( isset( $this->connection['remote-tcp-udp'] ) && 'tcp' === $this->connection['remote-tcp-udp'] ) {
					return new \WSAL_Vendor\Monolog\Handler\SocketHandler( $this->connection['remote-host'] . ':' . $this->connection['remote-port'] );
				}

				return new \WSAL_Vendor\Monolog\Handler\SyslogUdpHandler( $this->connection['remote-host'], $this->connection['remote-port'] );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function add_extra_requirements() {

		if ( ! extension_loaded( 'sockets' ) ) {
			self::$error_message = \esc_html__( 'PHP extension sockets is required', 'wp-security-audit-log' );
			return false;
		}

		return true;
	}
}
