<?php
/**
 * Extensions Manager Class
 *
 * Class file for extensions management.
 *
 * @since 3.0.0
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Extension_Manager' ) ) :

	/**
	 * WSAL_Extension_Manager.
	 *
	 * Extension manager class.
	 */
	class WSAL_Extension_Manager {

		/**
		 * Extensions.
		 *
		 * @var array
		 */
		public $extensions;

		/**
		 * WSAL Instance.
		 *
		 * @var WpSecurityAuditLog
		 */
		public $plugin;

		/**
		 * Method: Constructor.
		 */
		public function __construct() {
			// Include extension files.
			$this->includes();

			// Initialize the extensions.
			$this->init();
		}

		/**
		 * Include extension manually.
		 *
		 * @param string $extension - Extension.
		 */
		public static function include_extension( $extension ) {
			switch ( $extension ) {
				case 'search':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/search/search-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/search/search-init.php';
						new WSAL_SearchExtension();
					}
					break;
				case 'notifications':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php';
						new WSAL_NP_Plugin();
					}
					break;
				case 'reports':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/reports/reports-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/reports/reports-init.php';
						new WSAL_Rep_Plugin();
					}
					break;
				case 'sessions':
				case 'usersessions':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/users-sessions/user-sessions.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/users-sessions/user-sessions.php';
						new WSAL_UserSessions_Plugin();
					}
					break;
				case 'external-db':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php';
						new WSAL_Ext_Plugin();
					}
					break;
				case 'logs-management':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/logs-management/logs-management.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/logs-management/logs-management.php';
						new WSAL_LogsManagement();
					}
					break;

				case 'settings-import-export':
					if ( file_exists( WSAL_BASE_DIR . '/extensions/settings-import-export/settings-import-export.php' ) ) {
						require_once WSAL_BASE_DIR . '/extensions/settings-import-export/settings-import-export.php';
						new WSAL_SettingsExporter();
					}
					break;
				default:
					break;
			}
		}

		/**
		 * Method: Include extensions.
		 */
		protected function includes() {
			// Extensions for BASIC and above plans.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'starter' ) ) {
				/**
				 * Search.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/search/search-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/search/search-init.php';
				}

				/**
				 * Email Notifications.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/email-notifications/email-notifications.php';
				}
			}

			// Extensions for PROFESSIONAL and above plans.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'professional' ) ) {
				/**
				 * Reports
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/reports/reports-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/reports/reports-init.php';
				}

				/**
				 * Users Sessions Management.
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/user-sessions/user-sessions.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/user-sessions/user-sessions.php';
				}
			}

			// Extensions for BUSINESS and above plans.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'business' ) ) {
				/**
				 * External DB
				 */
				if ( file_exists( WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/external-db/external-db-init.php';
				}

				if ( file_exists( WSAL_BASE_DIR . '/extensions/logs-management/logs-management.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/logs-management/logs-management.php';
				}

				if ( file_exists( WSAL_BASE_DIR . '/extensions/settings-import-export/settings-import-export.php' ) ) {
					require_once WSAL_BASE_DIR . '/extensions/settings-import-export/settings-import-export.php';
				}
			}
		}

		/**
		 * Method: Initialize the extensions.
		 */
		protected function init() {
			// Basic package extensions.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'starter' ) ) {
				// Search filters.
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$this->extensions[] = new WSAL_SearchExtension();
				}

				// Email Notifications.
				if ( class_exists( 'WSAL_NP_Plugin' ) ) {
					$this->extensions[] = new WSAL_NP_Plugin();
				}
			}

			// Professional package extensions.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'professional' ) ) {
				// Reports.
				if ( class_exists( 'WSAL_Rep_Plugin' ) ) {
					$this->extensions[] = new WSAL_Rep_Plugin();
				}

				// Users Sessions Management.
				if ( class_exists( 'WSAL_UserSessions_Plugin' ) ) {
					$this->extensions[] = new WSAL_UserSessions_Plugin();
				}
			}

			// Business package extensions.
			if ( wsal_freemius()->is_plan_or_trial__premium_only( 'business' ) ) {
				// External DB.
				if ( class_exists( 'WSAL_Ext_Plugin' ) ) {
					$this->extensions[] = new WSAL_Ext_Plugin();
				}

				if ( class_exists( 'WSAL_LogsManagement' ) ) {
					$this->extensions[] = new WSAL_LogsManagement();
				}

				if ( class_exists( 'WSAL_SettingsExporter' ) ) {
					$this->extensions[] = new WSAL_SettingsExporter();
				}
			}
		}

		/**
		 * Checks if libraries needed for PDF reporting are available.
		 *
		 * @return bool True if libraries needed for PDF reporting are available.
		 */
		public static function is_pdf_reporting_available() {
			return class_exists( '\Spipu\Html2Pdf\Html2Pdf' );
		}

		/**
		 * Checks if libraries needed for mirroring are available.
		 *
		 * @return bool True if libraries needed for mirroring are available.
		 */
		public static function is_mirroring_available() {
			return class_exists( '\WSAL_Vendor\Monolog\Logger' );
		}

		/**
		 * Checks if libraries needed for SMS messaging are available.
		 *
		 * @return bool True if libraries needed for SMS messaging are available.
		 */
		public static function is_messaging_available() {
			return class_exists( '\WSAL_Vendor\Twilio\Rest\Client' );
		}

		/**
		 * Displays a notice with given text and a button to install the helper plugin containing external libraries.
		 *
		 * @param string $text               Text.
		 * @param string $extra_notice_style Extra notice styling.
		 * @param bool   $force_onclick      If true, the onclick event is forced.
		 *
		 * @since 4.4.0
		 */
		public static function render_helper_plugin_notice( $text, $extra_notice_style = '', $force_onclick = false ) {
			?>
			<div class="notice notice-info inline"
				 style="margin-top: 15px; padding: 6px 12px 12px; <?php echo $extra_notice_style; // phpcs:ignore ?>">
				<p><?php echo $text; // phpcs:ignore ?></p>
				<?php self::render_install_helper_plugin_button( $force_onclick ); ?>
			</div>
			<?php
		}

		/**
		 * Displays a button that trigger installation of the helper plugin containing external libraries.
		 *
		 * @param bool $force_onclick If true, the onclick event is forced.
		 *
		 * @since 4.4.0
		 */
		private static function render_install_helper_plugin_button( $force_onclick = false ) {
			?>
			<button class="install-addon button button-primary"
				<?php if ( $force_onclick ) : ?>
					onclick="return wsalCommonData.install_addon( event, this );"
				<?php endif; ?>
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'wsal-install-addon' ) ); ?>"
					data-plugin-slug="wsal-external-libraries/wsal-external-libraries.php"
					data-plugin-download-url="https://proxytron.wpwhitesecurity.com/download/wsal-external-libraries.latest-stable.zip"><?php esc_html_e( 'Install the helper plugin', 'wp-security-audit-log' ); ?></button>
			<?php
		}

		/**
		 * Displays a notice about existing features not working without a helper plugin.
		 *
		 * @param WpSecurityAuditLog $plugin Plugin instance.
		 *
		 * @since 4.4.0
		 */
		public static function display_helper_plugin_needed_notice( WpSecurityAuditLog $plugin ) {
			$should_notice_be_displayed = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'show-helper-plugin-needed-nudge', false );
			if ( ! $should_notice_be_displayed ) {
				return;
			}

			echo '<style type="text/css">';
			?>
			.notice-helper-plugin {
				padding-bottom: .8em;
			}
			.notice-helper-plugin .message {
				display: flex;
				align-items: center;
			}

			.notice-helper-plugin .log-type {
				cursor: default !important;
			}

			.notice-helper-plugin .log-type:after {
				height: 30px;
				width: 30px;
				margin-left: 0 !important;
				margin-top: 0 !important;
				margin-right: 10px;
			}
			<?php
			echo '</style>';
			echo '<div class="notice notice-error notice-helper-plugin is-dismissible" data-dismiss-action="wsal_dismiss_helper_plugin_needed_nudge" data-nonce="' . wp_create_nonce( 'dismiss_helper_plugin_needed_nudge' ) . '">'; // phpcs:ignore
			echo '<div class="message">';
			echo '<span class="log-type log-type-500 log-type-wsal_critical"></span>';
			echo '<div>';
			echo '<p>';
			echo esc_html__( 'Some features that you use in WP Activity Log (such as mirroring of logs) need a helper plugin to work from version 4.4.0 onward. Please install the helper plugin ASAP to ensure normal operation of the activity logs & the plugin.', 'wp-security-audit-log' );
			echo '</p>';
			self::render_install_helper_plugin_button();
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

endif;
