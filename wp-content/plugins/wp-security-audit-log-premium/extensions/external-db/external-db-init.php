<?php
/**
 * Extension: External DB
 *
 * External DB extension for WSAL.
 *
 * @since 1.0.0
 * @package wsal
 */

use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Connections Prefix.
 */
define( 'WSAL_CONN_PREFIX', 'connection-' );
define( 'WSAL_MIRROR_PREFIX', 'mirror-' );

/**
 * Class WSAL_Ext_Plugin
 *
 * @package wsal
 */
class WSAL_Ext_Plugin {

	const SCHEDULED_HOOK_ARCHIVING = 'wsal_run_archiving';

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $wsal = null;

	/**
	 * Method: Constructor
	 */
	public function __construct() {
		// Function to hook at `wsal_init`.
		add_action( 'wsal_init', array( $this, 'wsal_init' ) );
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 *
	 * @see WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {
		$wsal->external_db_util = new WSAL_Ext_Common( $wsal, $this );
		new WSAL_Ext_Ajax( $wsal );

		if ( isset( $wsal->views ) ) {
			$wsal->views->add_from_class( 'WSAL_Ext_Settings' );
		}

		if ( isset( $wsal->alerts ) ) {
			$wsal->alerts->add_logger_instance( new WSAL_Ext_MirrorLogger( $wsal ) );
		}

		$this->wsal = $wsal;

		// Register alert formatter for Slack.
		add_filter( 'wsal_alert_formatters', array( $this, 'register_alert_formatters' ), 10, 1 );

		$this->check_schedules_setup();

		// Background job for the migration.
		new WSAL_Ext_DataMigration();
	}

	/**
	 * Checks current schedules setup and does any necessary scheduling or job cancellation.
	 *
	 * @since 4.2.1
	 */
	private function check_schedules_setup() {
		// Cron job archiving.
		if ( Settings_Helper::is_archiving_set_and_enabled() ) {
			add_action( self::SCHEDULED_HOOK_ARCHIVING, array( $this, 'archiving_alerts' ) );
			if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_ARCHIVING ) ) {
				$archiving_frequency = strtolower( $this->wsal->external_db_util->get_archiving_frequency() );
				wp_schedule_event( time(), $archiving_frequency, self::SCHEDULED_HOOK_ARCHIVING );
			}
		}
	}

	/**
	 * Remove External DB config and recreate DB tables on WP.
	 */
	public function remove_config() {
		$common = $this->wsal->external_db_util;
		$common->remove_external_storage_config();
		$common->recreate_tables();
	}

	/**
	 * Archiving alerts
	 */
	public function archiving_alerts() {
		$this->wsal->external_db_util->archiving_alerts();
	}

	/**
	 * Registers plain text alert formatter.
	 *
	 * @param array $formatters Formatter definition arrays.
	 *
	 * @return array
	 * @since 4.2.1
	 * @see WSAL_AlertFormatterFactory
	 */
	public function register_alert_formatters( $formatters ) {
		$formatters['plain'] = WSAL_AlertFormatterConfiguration::build_plain_text_configuration();

		return $formatters;
	}
}
