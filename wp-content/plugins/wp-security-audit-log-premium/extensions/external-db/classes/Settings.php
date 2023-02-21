<?php
/**
 * View: Settings
 *
 * External DB settings view.
 *
 * @package wsal
 * @subpackage external-db
 */

use WSAL\Helpers\Options;
use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * Class WSAL_Ext_Settings for the plugin view.
 *
 * @package wsal
 * @subpackage external-db
 */
class WSAL_Ext_Settings extends WSAL_AbstractView {

	const QUERY_LIMIT = 200;

	/**
	 * Extension directory path.
	 *
	 * @var string
	 */
	public $base_dir;

	/**
	 * Extension directory url.
	 *
	 * @var string
	 */
	public $base_url;

	/**
	 * WSAL Database Tabs.
	 *
	 * @since 3.2.5
	 *
	 * @var array
	 */
	private $wsal_db_tabs = array();

	/**
	 * Current Database Tab.
	 *
	 * @since 3.2.5
	 *
	 * @var string
	 */
	private $current_tab = '';

	/**
	 * Current Database Tab Object.
	 *
	 * @since 3.2.5
	 *
	 * @var object
	 */
	private $current_tab_obj;

	/**
	 * Method: Constructor
	 *
	 * @param WpSecurityAuditLog $plugin - Instance of WpSecurityAuditLog.
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		// Call to parent class.
		parent::__construct( $plugin );

		// Ajax events for external tables of WSAL.
		add_action( 'wp_ajax_wsal_test_connection', array( $this, 'test_external_db_connection' ), 10 );

		// Ajax events for mirror and archive events.
		add_action( 'wp_ajax_wsal_archive_now', array( $this, 'archiving_now' ) );
		add_action( 'wp_ajax_wsal_reset_archiving', array( $this, 'reset_archiving' ) );
		add_action( 'wp_ajax_wsal_toggle_db_logging', array( $this, 'toggle_db_logging' ) );
		add_action( 'wp_ajax_wsal_toggle_mirror_state', array( 'WSAL_Ext_Mirroring', 'toggle_mirror_state' ) );
		add_action( 'wp_ajax_wsal_delete_mirror', array( 'WSAL_Ext_Mirroring', 'delete_mirror' ) );

		// Set the paths.
		$this->base_dir = trailingslashit( WSAL_BASE_DIR ) . 'extensions/external-db';
		$this->base_url = trailingslashit( WSAL_BASE_URL ) . 'extensions/external-db';

		// Tab links.
		$wsal_db_tabs = array(
			'connections'      => array(
				'name'   => esc_html__( 'Connections', 'wp-security-audit-log' ),
				'link'   => add_query_arg( 'tab', 'connections', $this->get_url() ),
				'render' => array( $this, 'tab_connections' ),
			),
			'external-storage' => array(
				'name'   => esc_html__( 'External Storage', 'wp-security-audit-log' ),
				'link'   => add_query_arg( 'tab', 'external-storage', $this->get_url() ),
				'render' => array( $this, 'tab_external_storage' ),
			),
			'archiving'        => array(
				'name'   => esc_html__( 'Archiving', 'wp-security-audit-log' ),
				'link'   => add_query_arg( 'tab', 'archiving', $this->get_url() ),
				'render' => array( $this, 'tab_archiving' ),
				'save'   => array( $this, 'tab_archiving_save' ),
			),
			'mirroring'        => array(
				'name'   => esc_html__( 'Mirroring', 'wp-security-audit-log' ),
				'link'   => add_query_arg( 'tab', 'mirroring', $this->get_url() ),
				'render' => array( $this, 'tab_mirroring' ),
			),
		);

		/**
		 * Filter: `wsal_db_tabs`
		 *
		 * This filter is used to filter the tabs of WSAL external db page.
		 *
		 * DB tabs structure:
		 *     $wsal_db_tabs['unique-tab-id'] = array(
		 *         'name'              => Name of the tab,
		 *         'link'              => Link of the tab,
		 *         'render'            => This function is used to render HTML elements in the tab,
		 *         'save' — Optional — => This function is used to save the related setting of the tab.
		 *     );
		 *
		 * @since 3.3
		 *
		 * @param array $wsal_db_tabs – Array of WSAL DB Tabs.
		 */
		$this->wsal_db_tabs = apply_filters( 'wsal_db_tabs', $wsal_db_tabs );

		// Get the current tab.
		// @codingStandardsIgnoreStart
		$current_tab       = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false;
		$this->current_tab = empty( $current_tab ) ? 'connections' : $current_tab;
		// @codingStandardsIgnoreEnd

		if ( 'connections' === $this->current_tab ) {
			$this->current_tab_obj = new WSAL_Ext_Connections( $this->plugin );
		} elseif ( 'mirroring' === $this->current_tab ) {
			$this->current_tab_obj = new WSAL_Ext_Mirroring( $this->plugin );
		} elseif ( 'external-storage' === $this->current_tab ) {
			$this->current_tab_obj = new WSAL_Ext_ExternalStorageTab( $this->plugin, $this );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_title() {
		return esc_html__( 'Integrations - external databases & third party services configuration', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_icon() {
		return 'dashicons-admin-generic';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'Integrations', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_weight() {
		return 7;
	}

	/**
	 * Method: Get View Header.
	 */
	public function header() {
		wp_enqueue_style(
			'wsal-external-css',
			$this->base_url . '/css/styles.css',
			array(),
			WSAL_VERSION
		);

		do_action( 'wsal_ext_db_header' );
	}

	/**
	 * Method: Return URL based prefix for DB.
	 *
	 * @return string - URL based prefix.
	 *
	 * @param string $name - Name of the DB type.
	 *
	 * @deprecated 4.4.3.2 - Use self::get_extension_base_url() function instead.
	 */
	public function get_url_base_prefix( $name = '' ) {
		_deprecated_function( __FUNCTION__, '4.4.3.2', 'WSAL_Ext_Common::get_url_for_db()' );

		return WSAL_Ext_Common::get_url_for_db( $name );
	}

	/**
	 * Archiving alerts Now.
	 */
	public function archiving_now() {
		$this->plugin->external_db_util->archiving_alerts();
		exit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
		}

		WSAL_Connector_ConnectorFactory::display_notice_if_connection_not_available( $this->plugin );

		// Get $_POST global array.
		$post_array = filter_input_array( INPUT_POST );

		if ( isset( $post_array['submit'] ) ) :
			try {
				if ( ! empty( $this->current_tab ) && ! empty( $this->wsal_db_tabs[ $this->current_tab ]['save'] ) ) :
					call_user_func( $this->wsal_db_tabs[ $this->current_tab ]['save'] );
					?>
					<div class="updated"><p><?php esc_html_e( 'Settings have been saved.', 'wp-security-audit-log' ); ?></p></div>
					<?php
				endif;
				?>
			<?php } catch ( \Exception $ex ) { ?>
				<div class="error"><p><?php esc_html_e( 'Error: ', 'wp-security-audit-log' ); ?><?php echo esc_html( $ex->getMessage() ); ?></p></div>
				<?php
			}
		endif;
		?>
		<div id="ajax-response" class="notice hidden">
			<img src="<?php echo esc_url( $this->base_url ); ?>/css/default.gif" />
			<p><?php esc_html_e( 'Please do not close this window while migrating events.', 'wp-security-audit-log' ); ?><span id="ajax-response-counter"></span></p>
		</div>
		<div id="wsal-external-db">
			<nav id="wsal-tabs" class="nav-tab-wrapper">
				<?php foreach ( $this->wsal_db_tabs as $tab_id => $tab ) : ?>
					<?php if ( empty( $this->current_tab ) ) : ?>
						<a href="<?php echo esc_url( $tab['link'] ); ?>" class="nav-tab<?php echo ( 'connections' === $tab_id ) ? ' nav-tab-active' : false; ?>"><?php echo esc_html( $tab['name'] ); ?></a>
					<?php else : ?>
						<a href="<?php echo esc_url( $tab['link'] ); ?>" class="nav-tab<?php echo ( $tab_id === $this->current_tab ) ? ' nav-tab-active' : false; ?>"><?php echo esc_html( $tab['name'] ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</nav>
			<div class="nav-tabs">
				<?php
				if ( ! empty( $this->current_tab ) && ! empty( $this->wsal_db_tabs[ $this->current_tab ]['render'] ) ) {
					call_user_func( $this->wsal_db_tabs[ $this->current_tab ]['render'] );
				} else {
					call_user_func( $this->wsal_db_tabs['connections']['render'] );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Tab: `Connections`.
	 */
	public function tab_connections() {
		$this->current_tab_obj->render();
	}

	/**
	 * Tab: `External Storage`.
	 */
	public function tab_external_storage() {
		$this->current_tab_obj->render();
	}

	/**
	 * Tab: `Mirroring`.
	 */
	public function tab_mirroring() {
		$this->current_tab_obj->render();
	}

	/**
	 * Tab: `Archiving`.
	 */
	public function tab_archiving() {
		$allowed_tags = array(
			'a' => array(
				'href'   => true,
				'target' => true,
			),
		);
		$help_link    = sprintf(
			/* Translators: 1 is the help type being linked */
			__( 'Read more on %1$s.', 'wp-security-audit-log' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( 'https://wpactivitylog.com/support/kb/archive-activity-log-events/' ),
				__( 'archiving activity log data', 'wp-security-audit-log' )
			)
		);
		$pruning_unit_options = array(
			'days'   => esc_html__( 'Days', 'wp-security-audit-log' ),
			'months' => esc_html__( 'Months', 'wp-security-audit-log' ),
			'years'  => esc_html__( 'Years', 'wp-security-audit-log' ),
		);
		?>
		<p><?php esc_html_e( 'In this section you can configure the archiving of old events to an archive database. Archives events can still be accessed and are included in search results and reports.', 'wp-security-audit-log' ); ?>  <?php echo wp_kses( $help_link, $allowed_tags ); ?></p>
		<form method="post" autocomplete="off">
			<input type="hidden" name="Archiving" value="1" />
			<input type="hidden" name="SetArchiving" value="1" id="archiving_status" />
			<?php wp_nonce_field( 'archive-db-form', 'wsal_archive_db' ); ?>
			<div class="wsal-setting-option">
				<h3><?php esc_html_e( 'Archive the WordPress Activity Log to this Database', 'wp-security-audit-log' ); ?></h3>
				<table class="form-table">
					<th><label for="ArchiveConnection"><?php esc_html_e( 'Connection', 'wp-security-audit-log' ); ?></label></th>
					<td>
						<fieldset>
							<?php $this->get_connection_field( 'archive' ); ?>
						</fieldset>
					</td>
				</table>
			</div>
			<div class="wsal-setting-option">
				<h3><?php esc_html_e( 'Archive events that are older than', 'wp-security-audit-log' ); ?></h3>
				<table class="form-table">
					<th><label for="ArchivingDate"><?php esc_html_e( 'Archiving Options', 'wp-security-audit-log' ); ?></label></th>
					<td>
						<fieldset>
							<?php
							$date_type = strtolower( $this->plugin->external_db_util->get_archiving_date_type() );

							// If date type is weeks then update the date.
							if ( 'weeks' === $date_type ) {
								$this->plugin->external_db_util->set_archiving_date( '1' );
								$this->plugin->external_db_util->set_archiving_date_type( 'years' );
								$date_type = 'years';
							}
							?>
							<label for="ArchivingDate">
								<?php esc_html_e( 'Archive events older than', 'wp-security-audit-log' ); ?>
								<input type="number" id="ArchivingDate" name="ArchivingDate" min="1" value="<?php echo esc_attr( $this->plugin->external_db_util->get_archiving_date() ); ?>" />
								<select name="DateType" class="age-type">
									<?php
									foreach ( $pruning_unit_options as $option => $label ) {
										echo '<option value="' . esc_attr( $option ) . '" ' . selected( $date_type, $option ) . '>' . ucwords( $label ) . '</option>'; // phpcs:ignore
									}
									?>
								</select>
							</label>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'The configured archiving options will override the Security Events Pruning settings configured in the plugin’s settings.', 'wp-security-audit-log' ); ?>
						</p>
					</td>
				</table>
			</div>
			<?php
			if ( Settings_Helper::is_archiving_set_and_enabled() ) {
				?>
			<div class="wsal-setting-option">
				<h3><?php esc_html_e( 'WordPress Activity Log Data Retention', 'wp-security-audit-log' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Once you configure archiving these data retention settings will be used instead of the ones configured in the plugin\'s general settings.', 'wp-security-audit-log' ); ?></p>
				<?php
				$settings_view = $this->plugin->views->find_by_class_name( 'WSAL_Views_Settings' );
				if ( null !== $settings_view ) {
					$settings_view->render_retention_settings_table();
				}
				?>
			</div>
			<?php } ?>
			<div class="wsal-setting-option">
				<?php $this->get_schedule_fields( 'archiving' ); ?>
			</div>
			<div class="wsal-setting-option">
				<?php
				if ( ! Settings_Helper::is_archiving_enabled() ) {
					$disabled = 'disabled';
				} else {
					$disabled = '';
				}
				?>
				<input type="submit" name="submit" class="button button-primary" value="Save Changes" />
				<input type="hidden" id="archive-test-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wsal_archive-test' ) ); ?>" />
				<?php
				if ( Settings_Helper::get_default_connection_for_type( 'archive' ) ) {
					?>
				<input type="button" data-connection="archive" id="archive-test" class="button button-primary" value="<?php esc_attr_e( 'Test Connection', 'wp-security-audit-log' ); ?>" />
				<?php } ?>
				<?php
				if ( Settings_Helper::is_archiving_set_and_enabled() ) {
					?>
				<input type="button" id="wsal-archiving" class="button button-primary" value="<?php esc_attr_e( 'Execute Archiving Now', 'wp-security-audit-log' ); ?>" <?php echo esc_attr( $disabled ); ?> />
				<?php } ?>
			</div>
			<div class="wsal-setting-option">
				<h3><?php esc_html_e( 'Reset Archiving Settings', 'wp-security-audit-log' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Click the button below to disable archiving and reset the settings to no archiving. Note that the archived data will not be deleted.', 'wp-security-audit-log' ); ?></p>
				<p><input type="button" id="wsal-reset-archiving" class="button button-primary" value="<?php esc_attr_e( 'Disable Archiving & Reset Settings', 'wp-security-audit-log' ); ?>" /></p>
			</div>
		</form>
		<!-- Tab Archiving -->
		<?php
	}

	/**
	 * Tab Save: `Archiving`
	 *
	 * @throws \Exception - When no connection is found.
	 */
	public function tab_archiving_save() {
		// Verify nonce.
		check_admin_referer( 'archive-db-form', 'wsal_archive_db' );

		// Save Archiving.
		$this->plugin->external_db_util->set_archiving_enabled( isset( $_POST['SetArchiving'] ) );
		$this->plugin->external_db_util->set_archiving_stop( isset( $_POST['StopArchiving'] ) );

		if ( isset( $_POST['RunArchiving'] ) ) {
			$this->plugin->external_db_util->set_archiving_run_every( sanitize_text_field( wp_unslash( $_POST['RunArchiving'] ) ) );

			// Reset old archiving cron job(s).
			wp_clear_scheduled_hook( WSAL_Ext_Plugin::SCHEDULED_HOOK_ARCHIVING );
		}

		// Set archiving date and type.
		$archive_date = isset( $_POST['ArchivingDate'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['ArchivingDate'] ) ) : false;
		if ( $archive_date < 1 ) {
			$archive_date = 1;
		}
		$archive_type = isset( $_POST['DateType'] ) ? sanitize_text_field( wp_unslash( $_POST['DateType'] ) ) : false;
		$this->plugin->external_db_util->set_archiving_date( $archive_date );
		$this->plugin->external_db_util->set_archiving_date_type( $archive_type );

		// Get pruning date.
		$pruning_date = isset( $_POST['PruningDate'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['PruningDate'] ) ) : '';
		if ( $pruning_date < 1 ) {
			$pruning_date = 1;
		}
		$pruning_unit = isset( $_POST['pruning-unit'] ) ? sanitize_text_field( wp_unslash( $_POST['pruning-unit'] ) ) : false;

		$this->check_period_collision( $archive_date, $archive_type, $pruning_date, $pruning_unit );
		$pruning_date = ( ! empty( $pruning_date ) ) ? $pruning_date . ' ' . $pruning_unit : '';

		$pruning_enabled = isset( $_POST['PruneBy'] ) && 'date' === $_POST['PruneBy'];
		$this->plugin->settings()->set_pruning_date_settings( $pruning_enabled, $pruning_date, $pruning_unit );

		// Get connection name.
		$connection = isset( $_POST['ArchiveConnection'] ) ? sanitize_text_field( wp_unslash( $_POST['ArchiveConnection'] ) ) : false;

		if ( ! empty( $connection ) ) {
			// Get old archive connection name.
			$old_conn_name = $this->plugin->external_db_util->get_setting_by_name( 'archive-connection', false );

			if ( $old_conn_name && $connection !== $old_conn_name ) {
				// Get old connection object.
				$old_connection = $this->plugin->external_db_util->get_connection( $old_conn_name );

				// Clear old connection used for.
				$old_connection['used_for'] = '';

				// Save the old connection object.
				$this->plugin->external_db_util->save_connection( $old_connection );
			}

			// Get connection option.
			$db_connection = $this->plugin->external_db_util->get_connection( $connection );

			// Error handling.
			if ( empty( $db_connection ) ) {
				throw new \Exception( 'No connection found.' );
			}

			// Set connection's used_for attribute.
			$db_connection['used_for'] = esc_html__( 'Archiving', 'wp-security-audit-log' );

			// Check archive DB connection.
			$archive_connection = WSAL_Connector_ConnectorFactory::check_config( $db_connection );

			// If connection is stable, then enable archiving.
			if ( $archive_connection ) {
				$this->plugin->external_db_util->set_archiving_enabled( true );
			}

			/* Setting Archive DB config */
			$this->plugin->external_db_util->add_global_setting( 'archive-connection', $connection );
			$this->plugin->external_db_util->save_connection( $db_connection );

			// Create tables in the database.
			$connector = $this->plugin->get_connector( $db_connection );
			$connector->install_all( true );
			$connector->get_adapter( 'Occurrence' )->create_indexes();
			$connector->get_adapter( 'Meta' )->create_indexes();
		}
	}

	/**
	 * Common function to schedule cron job.
	 *
	 * @param string $name - Name of DB Type.
	 */
	private function get_schedule_fields( $name ) {
		$label_name  = ucfirst( $name );
		$option_name = strtolower( $name );
		$config_name = 'is_' . $name . '_stopped';

		if ( 'is_archiving_stopped' === $config_name ) {
			$json_setting = Settings_Helper::is_archiving_stopped();
		} else {
			$json_setting = $this->plugin->external_db_util->$config_name();
		}
		?>
		<h3><?php esc_html_e( 'Run the Archiving Process Every', 'wp-security-audit-log' ); ?></h3>
		<table class="form-table">
			<th><label for="Run<?php echo esc_attr( $label_name ); ?>">Run <?php echo esc_html( $option_name ); ?> process every</label></th>
			<td>
				<fieldset>
					<?php
					$name  = 'get_' . strtolower( $label_name ) . '_frequency';
					$every = strtolower( $this->plugin->external_db_util->$name() );
					?>
					<select name="Run<?php echo esc_attr( $label_name ); ?>" id="Run<?php echo esc_attr( $label_name ); ?>">
						<option value="fifteenminutes" <?php selected( $every, 'fifteenminutes' ); ?>>
							<?php esc_html_e( '15 minutes', 'wp-security-audit-log' ); ?>
						</option>
						<option value="hourly" <?php selected( $every, 'hourly' ); ?>>
							<?php esc_html_e( '1 hour', 'wp-security-audit-log' ); ?>
						</option>
						<option value="sixhours" <?php selected( $every, 'sixhours' ); ?>>
							<?php esc_html_e( '6 hours', 'wp-security-audit-log' ); ?>
						</option>
						<option value="twicedaily" <?php selected( $every, 'twicedaily' ); ?>>
							<?php esc_html_e( '12 hours', 'wp-security-audit-log' ); ?>
						</option>
						<option value="daily" <?php selected( $every, 'daily' ); ?>>
							<?php esc_html_e( '24 hours', 'wp-security-audit-log' ); ?>
						</option>
					</select>
				</fieldset>
			</td>
		</table>
		<h3><?php esc_html_e( 'Stop Archiving', 'wp-security-audit-log' ); ?></h3>
		<table class="form-table">
			<th><label for="Stop<?php echo esc_attr( $label_name ); ?>">Stop <?php echo esc_html( $label_name ); ?></label></th>
			<td>
				<fieldset>
					<label for="Stop<?php echo esc_attr( $label_name ); ?>" class="no-margin">
						<span class="f-container">
							<span class="f-left">
								<input type="checkbox" name="Stop<?php echo esc_attr( $label_name ); ?>" value="1" class="switch" id="<?php echo esc_attr( $option_name ); ?>_stop"/>
								<label for="<?php echo esc_attr( $option_name ); ?>_stop" class="no-margin orange"></label>
							</span>
						</span>
					</label>
					<span class="description"><?php esc_html_e( 'Current status:', 'wp-security-audit-log' ); ?> <strong><span id="<?php echo esc_attr( $option_name ); ?>_stop_text"></span></strong></span>
				</fieldset>
			</td>
		</table>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				var <?php echo esc_attr( $option_name ); ?>Stop   = <?php echo wp_json_encode( $json_setting ); ?>;
				var <?php echo esc_attr( $option_name ); ?>_stop  = jQuery('#<?php echo esc_attr( $option_name ); ?>_stop');
				var <?php echo esc_attr( $option_name ); ?>TxtNot = jQuery('#<?php echo esc_attr( $option_name ); ?>_stop_text');

				function wsal<?php echo esc_attr( $label_name ); ?>Stop(checkbox, label){
					if (checkbox.prop('checked')) {
						label.text('<?php esc_html_e( 'Stopped', 'wp-security-audit-log' ); ?>');
					} else {
						label.text('<?php esc_html_e( 'Running', 'wp-security-audit-log' ); ?>');
					}
				}
				// Set On
				if (<?php echo esc_attr( $option_name ); ?>Stop) {
					<?php echo esc_attr( $option_name ); ?>_stop.prop('checked', true);
				}
				wsal<?php echo esc_attr( $label_name ); ?>Stop(<?php echo esc_attr( $option_name ); ?>_stop, <?php echo esc_attr( $option_name ); ?>TxtNot);

				<?php echo esc_attr( $option_name ); ?>_stop.on('change', function() {
					wsal<?php echo esc_attr( $label_name ); ?>Stop(<?php echo esc_attr( $option_name ); ?>_stop, <?php echo esc_attr( $option_name ); ?>TxtNot);
				});
			});
		</script>
		<?php
	}

	/**
	 * Check to see if archive and retention time periods are colliding
	 * with each other.
	 *
	 * @since 3.2.3
	 *
	 * @param string $archive_date – Archive date.
	 * @param string $archive_type – Archive date type.
	 * @param string $pruning_date – Pruning/Retention date.
	 * @param string $pruning_type – Pruning/Retention date type.
	 */
	private function check_period_collision( $archive_date, $archive_type = 'months', $pruning_date = null, $pruning_type = 'months' ) {
		// Check the parameters.
		if ( empty( $archive_date ) || empty( $archive_type ) || empty( $pruning_date ) || empty( $pruning_type ) ) {
			return false;
		}

		// Turn string into time for comparison.
		$archive_time = strtotime( $archive_date . ' ' . $archive_type );
		$pruning_time = strtotime( $pruning_date . ' ' . $pruning_type );

		// Show popup.
		$show_popup = ( $pruning_time < $archive_time );

		if ( $show_popup ) :
			$this->enqueue_remodal();
			?>
			<div class="remodal" data-remodal-id="wsal-pruning-collision" style="display: none;">
				<h3><?php esc_html_e( 'Attention!', 'wp-security-audit-log' ); ?></h3>
				<p class="description">
					<?php
					/* translators: %1$s: Alerts Pruning Period, %2$s: Alerts Archiving Period */
					echo sprintf( esc_html__( 'The activity log retention setting is configured to delete events older than %1$s. This period should be longer than the configured %2$s archiving period otherwise events will be deleted and not archived.', 'wp-security-audit-log' ), esc_html( $pruning_date . ' ' . $pruning_type ), esc_html( $archive_date . ' ' . $archive_type ) );
					?>
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					var options = {hashTracking: false};
					var pruningModal = jQuery( '[data-remodal-id="wsal-pruning-collision"]' );
					var modalInstance = pruningModal.remodal( options );
					modalInstance.open();
					pruningModal.removeAttr( 'style' );
				});
			</script>
			<?php
		endif;
	}

	/**
	 * Ajax request handler to test external DB connections.
	 *
	 * @since 3.2.3
	 */
	public function test_external_db_connection() {
		// Check request permissions.
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		$nonce   = \sanitize_text_field( \wp_unslash( $_POST['nonce'] ) );
		$db_type = \sanitize_text_field( \wp_unslash( $_POST['connectionType'] ) );

		if ( empty( $db_type ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wsal_' . $db_type . '-test' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		$connection_name = \WSAL\Helpers\Settings_Helper::get_option_value( $db_type . '-connection' );
		if ( empty( $connection_name ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'No connection found.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		$connection = $this->plugin->external_db_util->get_connection( $connection_name );
		if ( ! is_array( $connection ) || empty( $connection ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'No connection found.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		try {
			WSAL_Connector_ConnectorFactory::check_config( $connection );

			echo wp_json_encode(
				array(
					'success' => true,
					'message' => esc_html__( 'Successfully connected to database.', 'wp-security-audit-log' ),
				)
			);
		} catch ( \Exception $ex ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => $ex->getMessage(),
				)
			);
		}
		exit();
	}

	/**
	 * Get WSAL Connection Select Field.
	 *
	 * @since 3.3
	 *
	 * @param string $name            – Name of the DB type.
	 */
	public function get_connection_field( $name = 'adapter' ) {
		// Get connections.
		$connections = Settings_Helper::get_mysql_connections_exclude_by_type( $name );
		$label_name  = ucfirst( $name );

		// Get selected connection.
		echo '<select name="' . esc_attr( $label_name ) . 'Connection" id="' . esc_attr( $label_name ) . 'Connection">';
		echo '<option value="0" disabled selected>' . esc_html__( 'Select a connection', 'wp-security-audit-log' ) . '</option>';

		$selected = Settings_Helper::get_default_connection_for_type( $name );

		if ( ! empty( $connections ) ) {
			foreach ( $connections as $connection ) {
				echo '<option value="' . esc_attr( $connection['option_value']['name'] ) . '" ' . selected( $connection['option_value']['name'], ( isset( $selected['option_value'] ) ) ? $selected['option_value'] : '', false ) . '>' . esc_html( $connection['option_value']['name'] ) . '</option>';
			}
		}
		echo '</select>';
	}

	/**
	 * Method: Get View Footer.
	 */
	public function footer() {
		?>
		<script type="text/javascript">
			var query_limit = <?php echo esc_html( self::QUERY_LIMIT ); ?>;
			jQuery(document).ready(function() {
				var archivingConfig = <?php echo json_encode( Settings_Helper::is_archiving_enabled() ); // phpcs:disable ?>;
				var archiving_status = jQuery('#archiving_status');
				var archivingTxtNot = jQuery('#archiving_status_text');

				function wsalArchivingStatus(checkbox, label){
					if (checkbox.prop('checked')) {
						label.text('On');
						jQuery('#ArchiveName').prop('required', true);
						jQuery('#ArchiveUser').prop('required', true);
						jQuery('#ArchiveHostname').prop('required', true);
					} else {
						label.text('Off');
						jQuery('#ArchiveName').prop('required', false);
						jQuery('#ArchiveUser').prop('required', false);
						jQuery('#ArchiveHostname').prop('required', false);
					}
				}
				// Set On.
				if ( archivingConfig ) {
					archiving_status.prop('checked', true);
				}
				wsalArchivingStatus(archiving_status, archivingTxtNot);

				archiving_status.on('change', function() {
					wsalArchivingStatus(archiving_status, archivingTxtNot);
				});
			});
		</script>
		<?php
		// Extension script file.
		wp_register_script(
			'wsal-external-js',
			$this->base_url . '/js/wsal-external.js',
			array( 'jquery' ),
			WSAL_VERSION,
			true
		);

		$external_data = array(
			'archivingComplete'                  => esc_html__( 'Archiving complete!', 'wp-security-audit-log' ),
			'archivingProgress'                  => esc_html__( 'Archiving...', 'wp-security-audit-log' ),
			'cancelMigration'                    => esc_html__( 'Cancel migration', 'wp-security-audit-log' ),
			'continue'                           => esc_html__( 'Continue...', 'wp-security-audit-log' ),
			'connectionFailed'                   => esc_html__( 'Connection failed!', 'wp-security-audit-log' ),
			'connectionSuccess'                  => esc_html__( 'Connected!', 'wp-security-audit-log' ),
			'done'                               => esc_html__( 'Done!', 'wp-security-audit-log' ),
			/* Translators: %d: Number of events. */
			'eventsMigrated'                     => esc_html__( ' So far %d events have been migrated.', 'wp-security-audit-log' ),
			'migrationComplete'                  => esc_html__( 'Migration complete', 'wp-security-audit-log' ),
			'migrationPassed'                    => esc_html__( 'WordPress security events successfully migrated to the external database.', 'wp-security-audit-log' ),
			'noEventsToMigrate'                  => esc_html__( 'No events to migrate.', 'wp-security-audit-log' ),
			'resetFailed'                        => esc_html__( 'Resetting failed!', 'wp-security-audit-log' ),
			'resetProgress'                      => esc_html__( 'Resetting...', 'wp-security-audit-log' ),
			'reverseMigrationPassed'             => esc_html__( 'WordPress security events successfully migrated to WordPress database.', 'wp-security-audit-log' ),
			'selectConnectionForExternalStorage' => esc_html__( 'Please select connection to be used for external storage.', 'wp-security-audit-log' ),
			'switchConnection'                   => esc_html__( 'Switch connection', 'wp-security-audit-log' ),
			'testingProgress'                    => esc_html__( 'Testing...', 'wp-security-audit-log' ),
			'workingProgress'                    => esc_html__( 'Working...', 'wp-security-audit-log' ),
		);
		wp_localize_script( 'wsal-external-js', 'externalData', $external_data );
		wp_enqueue_script( 'wsal-external-js' );

		do_action( 'wsal_ext_db_footer' );
	}

	/**
	 * Reset Archive Settings Handler.
	 *
	 * @since 3.3
	 */
	public function reset_archiving() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		if ( isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpnonce'] ) ), 'archive-db-form' ) ) {
			// Remove archiving configuration.
			$this->plugin->external_db_util->set_archiving_enabled( false );

			// Response.
			echo wp_json_encode( array( 'success' => true ) );
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
		}
		exit();
	}

	/**
	 * Toggle database logging.
	 *
	 * @since 4.3.2
	 */
	public function toggle_db_logging() {
		//  permissions check
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			wp_send_json_error( esc_html__( 'Access Denied.', 'wp-security-audit-log' ) );
		}

		//  nonce check
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'db-logging-toggle' ) ) {
			wp_send_json_error( esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ) );
		}

		//  state attribute check
		if ( ! isset( $_POST['state'] ) ) {
			wp_send_json_error( esc_html__( 'Bad request. Target state attribute is missing.', 'wp-security-audit-log' ) );
		}

		//  update the state and send success response
		$state = Options::string_to_bool( $_POST['state'] );
		$this->plugin->settings()->set_database_logging_disabled( ! $state );
		wp_send_json_success();
	}

	/**
	 * Enqueues remodal CSS and JS.
     *
     * @since 4.3.2
	 */
	public function enqueue_remodal() {
		// Remodal styles.
		wp_enqueue_style( 'wsal-remodal', WSAL_BASE_URL . '/css/remodal.css', array(), WSAL_VERSION );
		wp_enqueue_style( 'wsal-remodal-theme', WSAL_BASE_URL . '/css/remodal-default-theme.css', array(), WSAL_VERSION );

		// Remodal script.
		wp_enqueue_script(
			'wsal-remodal-js',
			WSAL_BASE_URL . '/js/remodal.min.js',
			array(),
			WSAL_VERSION,
			true
		);
	}
}
