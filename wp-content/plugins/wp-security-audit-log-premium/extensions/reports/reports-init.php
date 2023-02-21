<?php
/**
 * Extension: Reports
 *
 * Reports extension for wsal.
 *
 * @since 1.0.0
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds the name of the cache key if cache available
 */
define( 'WSAL_CACHE_KEY_2', '__NOTIF_CACHE__' );

/**
 * Class WSAL_Rep_Plugin
 *
 * @package report-wsal
 */
class WSAL_Rep_Plugin {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $plugin = null;

	/**
	 * Method: Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		// Function to hook at `wsal_init`.
		add_action( 'wsal_init', array( $this, 'wsal_init' ) );
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @param WpSecurityAuditLog $plugin - Instance of WpSecurityAuditLog.
	 *
	 * @see WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $plugin ) {

		// Initialize utility classes.
		$plugin->reports_util = new WSAL_Rep_Common( $plugin );

		if ( isset( $plugin->views ) ) {
			$plugin->views->add_from_class( 'WSAL_Rep_Views_Main' );
		}

		// Register alert formatters for sms and email notifications.
		add_filter( 'wsal_alert_formatters', array( $this, 'register_alert_formatters' ), 10, 1 );

		if ( class_exists( '\S24WP' ) ) {
			\S24WP::init( WSAL_BASE_URL . 'vendor/wpwhitesecurity/select2-wpwhitesecurity' );
		}

		new WSAL_Rep_Settings( $plugin );
		new WSAL_Rep_WhiteLabellingSettings( $plugin );
	}

	/**
	 * Registers additional, reports specific alert formatters.
	 *
	 * @param array $formatters Formatter definition arrays.
	 *
	 * @return array
	 * @since 4.2.1
	 * @see WSAL_AlertFormatterFactory
	 */
	public function register_alert_formatters( $formatters ) {
		$html_report_configuration  = ( WSAL_AlertFormatterConfiguration::build_html_configuration() )
			->set_is_js_in_links_allowed( false );
		$formatters ['report-html'] = $html_report_configuration;

		$csv_report_configuration  = ( WSAL_AlertFormatterConfiguration::build_plain_text_configuration() )
			->set_use_html_markup_for_links( false );
		$formatters ['report-csv'] = $csv_report_configuration;

		return $formatters;
	}
}
