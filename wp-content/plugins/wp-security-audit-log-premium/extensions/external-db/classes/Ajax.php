<?php
/**
 * Class WSAL_Ext_Ajax.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * Ajax handler for the External DB extension.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
final class WSAL_Ext_Ajax {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin – Instance of WSAL.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		new WSAL_Ext_StorageSwitchToLocal( $plugin );
		new WSAL_Ext_StorageSwitchToExternal( $plugin );
		new WSAL_Ext_MigrationCancellation( $plugin );
	}
}
