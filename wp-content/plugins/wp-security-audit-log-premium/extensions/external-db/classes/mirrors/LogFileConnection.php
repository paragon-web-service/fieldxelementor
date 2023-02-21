<?php
/**
 * Class WSAL_Ext_Mirrors_LogFileConnection.
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
 * Log file connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
class WSAL_Ext_Mirrors_LogFileConnection extends \WSAL_Ext_AbstractConnection {

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return 'log_file';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return esc_html__( 'Log file(s)', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_config_definition() {
		return array(
			'desc'   => esc_html__( 'WP Activity Log can write the WordPress activity log to a log file..', 'wp-security-audit-log' ),
			'fields' => array(
				'rotation' => array(
					'label'   => esc_html__( 'Log file(s) rotation', 'wp-security-audit-log' ),
					'type'    => 'select',
					'options' => array(
						'daily'   => esc_html__( 'daily', 'wp-security-audit-log' ),
						'monthly' => esc_html__( 'monthly', 'wp-security-audit-log' ),
						'yearly'  => esc_html__( 'yearly', 'wp-security-audit-log' ),
					),
				),
				'prefix'   => array(
					'label' => esc_html__( 'Log file prefix', 'wp-security-audit-log' ),
					'type'  => 'text',
					'desc'  => sprintf(
						esc_html__( 'Optional. Default prefix is %s.', 'wp-security-audit-log' ), // phpcs:ignore
						'"wsal"'
					),
				),

			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_monolog_handler() {

		$prefix = 'wsal';
		if ( array_key_exists( 'prefix', $this->connection ) && ! empty( trim( $this->connection['prefix'] ) ) ) {
			$prefix = trim( $this->connection['prefix'] );
		}

		$filename = $prefix . '.log';
		$dir_path = \WSAL_Settings::get_working_dir_path_static( 'logs' );
		$result   = new \WSAL_Vendor\Monolog\Handler\RotatingFileHandler( $dir_path . $filename );

		$date_format = \WSAL_Vendor\Monolog\Handler\RotatingFileHandler::FILE_PER_DAY;
		switch ( $this->connection['rotation'] ) {
			case 'monthly':
				$date_format = \WSAL_Vendor\Monolog\Handler\RotatingFileHandler::FILE_PER_MONTH;
				break;

			case 'yearly':
				$date_format = \WSAL_Vendor\Monolog\Handler\RotatingFileHandler::FILE_PER_YEAR;
				break;
		}

		$result->setFilenameFormat( '{filename}-{date}', $date_format );

		return $result;
	}
}
