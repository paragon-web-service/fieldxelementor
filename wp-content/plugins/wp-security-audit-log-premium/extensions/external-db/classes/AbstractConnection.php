<?php
/**
 * Class WSAL_Ext_AbstractConnection.
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
 * Abstract connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
abstract class WSAL_Ext_AbstractConnection implements WSAL_Ext_ConnectionInterface {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $plugin;

	/**
	 * Raw connection configuration data.
	 *
	 * @var array
	 */
	protected $connection;

	/**
	 * Holds the error message
	 *
	 * @var string
	 *
	 * @since 4.4.2.1
	 */
	protected static $error_message;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin     – Instance of WSAL.
	 * @param array              $connection Connection data.
	 */
	public function __construct( $plugin, $connection ) {
		$this->plugin     = $plugin;
		$this->connection = $connection;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function check_requirements() {
		if ( version_compare( phpversion(), '7.2', '<' ) ) {
			static::$error_message = \esc_html__( 'PHP version is not 7.2 or above', 'wp-security-audit-log' );
			return false;
		}

		return static::add_extra_requirements();
	}

	/**
	 * Optionally add extra requirements in a subclass.
	 *
	 */
	protected static function add_extra_requirements() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_alternative_error_message() {
		return self::$error_message;
	}

	/**
	 * {@inheritDoc}
	 */
	public function pre_process_message( $message, $metadata ) {
		return $message;
	}

	/**
	 * {@inheritDoc}
	 */
	public function pre_process_metadata( $metadata, $mirror ) {
		return $metadata;
	}
}
