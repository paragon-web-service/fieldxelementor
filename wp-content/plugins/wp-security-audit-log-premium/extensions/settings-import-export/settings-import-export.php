<?php
/**
 * Class WSAL_SettingsExporter.
 *
 * @since   4.3.3
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extension: Logs Management
 *
 * Log management extension for wsal.
 *
 * @since   4.3.3
 * @package wsal
 */
class WSAL_SettingsExporter {

	/**
	 * Settings import/export library object.
	 *
	 * @var SettingsImportExport
	 */
	private $settings_importer;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wsal_setting_tabs', array( $this, 'add_logs_management_tab' ), 10, 1 );

		$plugin = WpSecurityAuditLog::get_instance();
		if ( class_exists( 'SettingsImportExport' ) ) {

			// Link to build in contact form.
			if ( WpSecurityAuditLog::is_multisite() ) {
				$help_page = add_query_arg( 'page', 'wsal-help&tab=contact', network_admin_url( 'admin.php' ) );
			} else {
				$help_page = add_query_arg( 'page', 'wsal-help&tab=contact', admin_url( 'admin.php' ) );
			}

			$this->settings_importer = new SettingsImportExport(
				dirname( $plugin->get_base_name() ) . '/vendor/wpwhitesecurity/import-export-plugin-settings/',
				WSAL_BASE_URL . '/vendor/wpwhitesecurity/import-export-plugin-settings/',
				'wsal',
				array( $this, 'get_option_list' ),
				array( $this, 'get_option_type' ),
				array( $this, 'run_pre_import_check' ),
				array( $this, 'can_enqueue_scripts' ),
				$help_page
			);
		}
	}

	/**
	 * Retrieves a list of options to export.
	 *
	 * @return array
	 */
	public function get_option_list() {
		global $wpdb;
		$prepared_query = $wpdb->prepare(
			"SELECT `option_name`, `option_value` FROM `{$wpdb->options}` WHERE `option_name` LIKE %s ORDER BY `option_name` ASC",
			WSAL_PREFIX . '%'
		);

		return $wpdb->get_results( $prepared_query ); // phpcs:ignore
	}

	/**
	 * Determines the option type for known options.
	 *
	 * @param string $setting_name Setting name.
	 *
	 * @return string|null
	 */
	public function get_option_type( $setting_name ) {
		if ( 'wsal_custom-post-types' === $setting_name ) {
			return 'post_type';

		}

		if ( 'wsal_excluded-roles' === $setting_name ) {
			return 'role';
		}

		if ( 'wsal_excluded-users' === $setting_name ) {
			return 'user';
		}

		if ( 0 === strpos( $setting_name, 'wsal_usersessions_policy_' ) ) {
			return 'role';
		}

		return null;
	}

	/**
	 * Runs a pre-import check for settings that require extra handling.
	 *
	 * @param string $setting_name  Setting name.
	 * @param mixed  $setting_value Setting value.
	 *
	 * @return void|WP_Error Error object if the setting needs special handling.
	 */
	public function run_pre_import_check( $setting_name, $setting_value ) {
		if ( strpos( $setting_name, 'wsal_notification-' ) === 0 && strpos( $setting_name, 'built-in' ) === false ) {
			return new WP_Error( 'not_supported', esc_html__( 'Custom notifications are not supported', 'wp-security-audit-log' ) );
		}

		if ( 'wsal_restrict-plugin-settings' === $setting_name && 'only_me' === $setting_value ) {
			return new WP_Error( 'check_restrict_access', esc_html__( 'Settings access', 'wp-security-audit-log' ) );
		}
	}

	/**
	 * Checks if the scripts can be enqueued on current screen.
	 *
	 * @return bool True if scripts and styles should be loaded.
	 *
	 * phpcs:disable
	 */
	public function can_enqueue_scripts() {
		if ( ! array_key_exists( 'page', $_GET ) ) {
			return false;
		}

		$current_page = ( isset( $_GET['page'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['page'] ) ) : '';
		if ( 'wsal-settings' !== $current_page ) {
			return false;
		}

		if ( ! array_key_exists( 'tab', $_GET ) ) {
			return false;
		}

		$current_tab = ( isset( $_GET['tab'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['tab'] ) ) : '';

		return ( 'settings-export-import' === $current_tab );
	}

	/**
	 * Add log management tab to WSAL settings.
	 *
	 * @param array $wsal_setting_tabs WSAL settings tab.
	 *
	 * @return array - Tabs, plus our tab.
	 */
	public function add_logs_management_tab( $wsal_setting_tabs ) {
		$wsal_setting_tabs['settings-export-import'] = array(
			'name'     => esc_html__( 'Export/import settings', 'wp-security-audit-log' ),
			'link'     => add_query_arg( 'tab', 'settings-export-import' ),
			'render'   => array( $this, 'logs_management_tab' ),
			'save'     => false,
			'priority' => 100,
		);

		return $wsal_setting_tabs;
	}

	/**
	 * Handle content.
	 */
	public function logs_management_tab() {
		$this->tab_content();
	}

	/**
	 * The actual settings/tab content.
	 */
	private function tab_content() {
		$disabled  = ! $this->is_active() ? 'disabled' : '';
		$admin_url = ! is_multisite() ? 'admin_url' : 'network_admin_url';
		$buy_now   = add_query_arg( 'page', 'wsal-auditlog-pricing', $admin_url( 'admin.php' ) );
		$html_tags = WpSecurityAuditLog::get_instance()->allowed_html_tags;

		$tab_info_msg = esc_html__( 'From here you can export the plugin\'s settings configuration and also import them from an export file. Use the export file to keep a backup of the plugin\'s configuration or to import the same settings configuration to another website.', 'wp-security-audit-log' );
		if ( $disabled ) {
			/* Translators: Upgrade now hyperlink. */
			$tab_info_msg = sprintf( esc_html__( 'Settings import/export is available in the Professional and Business Plans. %s to configure and receive this feature.', 'wp-security-audit-log' ), '<a href="' . $buy_now . '">' . esc_html__( 'Upgrade now', 'wp-security-audit-log' ) . '</a>' );
		}

		echo '<p class="description">' . wp_kses( $tab_info_msg, $html_tags ) . '</p>';
		$this->settings_importer->render( $disabled );
	}

	/**
	 * Checks if the extension is active.
	 *
	 * @return bool True if the extension is active.
	 */
	public function is_active() {
		return wsal_freemius()->is_plan_or_trial__premium_only( 'professional' );
	}
}
