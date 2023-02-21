<?php
/**
 * View: Reports
 *
 * Generate reports view.
 *
 * @since      2.7.0
 * @package    wsal
 * @subpackage reports
 */

use WSAL\Helpers\User_Helper;
use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Class WSAL_Rep_Views_Main for the page Reporting.
 *
 * @package    wsal
 * @subpackage reports
 */
class WSAL_Rep_Views_Main extends WSAL_AbstractView {

	const REPORT_LIMIT = 1000;

	const REPORT_CRON_NAME = WSAL_PREFIX . 'statistic_report';

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
	 * {@inheritDoc}
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		// Call to parent class.
		parent::__construct( $plugin );

		// Ajax events for the report functions.
		add_action( 'wp_ajax_wsal_AjaxGenerateReport', array( $this, 'ajax_generate_report' ) );
		add_action( 'wp_ajax_wsal_AjaxCheckArchiveMatch', array( $this, 'ajax_check_archive_match' ) );
		add_action( 'wp_ajax_wsal_AjaxSummaryUniqueIPs', array( $this, 'ajax_summary_unique_ips' ) );
		add_action( 'wp_ajax_wsal_AjaxSendPeriodicReport', array( $this, 'ajax_send_periodic_report' ) );
		add_action( 'wp_ajax_wsal_report_download', array( $this, 'process_report_download' ) );

		add_action( self::REPORT_CRON_NAME, array( $this, 'ajax_generate_report' ) );

		// Set paths.
		$this->base_dir = WSAL_BASE_DIR . 'extensions/reports';
		$this->base_url = WSAL_BASE_URL . 'extensions/reports';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_title() {
		return esc_html__( 'Reporting', 'wp-security-audit-log' );
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
		return esc_html__( 'Reports', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_weight() {
		return 3;
	}

	/**
	 * {@inheritDoc}
	 */
	public function header() {

		wp_enqueue_style(
			'wsal-reporting-css',
			$this->base_url . '/css/styles.css',
			array(),
			WSAL_VERSION
		);

		wp_enqueue_script(
			'wsal-reporting-ui',
			$this->base_url . '/js/reports-ui.js',
			array( 'jquery' ),
			WSAL_VERSION,
			false
		);

		WSAL_Helpers_Assets::load_datepicker();
	}

	/**
	 * {@inheritDoc}
	 */
	public function footer() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				// tab handling code
				jQuery('#wsal-tabs>a').click(function(){
					jQuery('#wsal-tabs>a').removeClass('nav-tab-active');
					jQuery('div.wsal-tab').hide();
					jQuery(jQuery(this).addClass('nav-tab-active').attr('href')).show();
					return false;
				});

				//  jump to top if the settings were saved
				const urlParams = new URLSearchParams( window.location.search );
				if ( urlParams.has( 'settings_updated' ) ) {
					jQuery('#wsal-tabs>a[href="#tab-settings"]').click();
					if ( window.history.replaceState ) {
						urlParams.delete('settings_updated');
						let new_state = window.location.origin + window.location.pathname + '?' + urlParams.toString();
						window.history.replaceState( null, null,  new_state + '#tab-settings' );
					}
				} else {
					// show relevant tab
					var hashlink = jQuery('#wsal-tabs>a[href="' + location.hash + '"]');
					if (hashlink.length) {
						hashlink.click();
					} else {
						if (0 === jQuery('#wsal-tabs>a.nav-tab-active').length) {
							jQuery('#wsal-tabs>a:first').click();
						} else {
							jQuery('#wsal-tabs>a.nav-tab-active').click();
						}
					}
				}

				// Add required to Report email and name
				jQuery('input[name=wsal-periodic]').click(function(){
					var valid = true;
					jQuery('#wsal-notif-email').attr("required", true);
					jQuery('#wsal-notif-name').attr("required", true);
					var report_email = jQuery('#wsal-notif-email').val();
					var report_name = jQuery('#wsal-notif-name').val();

					if (!validateEmail(report_email)) {
						//The report_email is illegal
						jQuery('#wsal-notif-email').css('border-color', '#dd3d36');
						valid = false;
					} else {
						jQuery('#wsal-notif-email').css('border-color', '#aaa');
					}

					if (!report_name.match(/^[A-Za-z0-9_\-]{1,32}$/)) {
						//The report_name is illegal
						jQuery('#wsal-notif-name').css('border-color', '#dd3d36');
						valid = false;
						alert( '<?php esc_html_e( 'Report names can only include numbers, letters and underscore and hyphens.', 'wp-security-audit-log' ); ?>' );
					} else {
						jQuery('#wsal-notif-name').css('border-color', '#aaa');
					}
					return valid;
				});

				jQuery('input[name=wsal-reporting-submit]').click(function(){
					jQuery('#wsal-notif-email').removeAttr("required");
					jQuery('#wsal-notif-name').removeAttr("required");
				});
			});

			function validateEmail(email) {
				var atpos = email.indexOf("@");
				var dotpos = email.lastIndexOf(".");
				if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length) {
					return false;
				} else {
					return true;
				}
			}
		</script>
		<script type="text/javascript">
			var addArchive = false;
			var nextDate = null;

			function AjaxGenerateReport(filters) {
				var limit = <?php echo self::REPORT_LIMIT; // phpcs:ignore ?>;
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					async: true,
					dataType: 'json',
					data: {
						action: 'wsal_AjaxGenerateReport',
						filters: filters,
						nextDate: nextDate,
						limit: limit,
						addArchive: addArchive
					},
					success: function(response) {
						jQuery("#events-progress").hide();
						//jQuery("#events-progress").show();
						nextDate = response[0];
						if (nextDate != 0) {
							var current = parseInt( jQuery( "#events-progress-found" ).html() );
							jQuery( "#events-progress-found" ).html( current + parseInt( response['events_found'] ) );
							jQuery("#ajax-response").html("<?php esc_html_e( 'Your report will be generated in the background. We will send you an email once the report is ready. You can navigate away from this page.', 'wp-security-audit-log' ); ?>");
							//AjaxGenerateReport(filters);
						} else {
							if (response[1] !== null) {
								jQuery("#ajax-response").html("<?php esc_html_e( 'Process completed.', 'wp-security-audit-log' ); ?>");
								window.setTimeout(
									function() { 
										var win = window.open(response[1], '_blank');
										if (win) {
											//Browser has allowed it to be opened
											win.focus();
										} else {
											//Browser has blocked it
											alert('Please allow popups for this website');
										}
										//window.location.href = response[1]; 
									},
								300);
							} else if (("errors" in response)) {
								// Ensure we only have something unique.
								var errArray = jQuery.unique( jQuery.parseJSON(response['errors']) );                          
								jQuery("#ajax-response").html( errArray[0] );
							} else {
								jQuery("#ajax-response").html("<?php esc_html_e( 'There are no alerts that match your filtering criteria.', 'wp-security-audit-log' ); ?>");
							}
						}
					},
					error: function(xhr, textStatus, error) {
						console.log(xhr.statusText);
						console.log(textStatus);
						console.log(error);
					}
				});
			}

			function AjaxCheckArchiveMatch(filters) {
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					async: false,
					dataType: 'json',
					data: {
						action: 'wsal_AjaxCheckArchiveMatch',
						filters: filters
					},
					success: function(response) {
						if (response) {
							var r = confirm('There are alerts in the archive database that match your report criteria.\nShould these alerts be included in the report?');
							if (r == true) {
								addArchive = true;
							} else {
								addArchive = false;
							}
						}
					}
				});
			}

			function AjaxSummaryUniqueIPs(filters) {
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					async: true,
					dataType: 'json',
					data: {
						action: 'wsal_AjaxSummaryUniqueIPs',
						filters: filters
					},
					success: function(response) {
						if (response !== null) {
							jQuery("#ajax-response").html("<p>Process completed.</p>");
							window.setTimeout(function(){ window.location.href = response; }, 300);
						} else {
							jQuery("#ajax-response").html("<p>There are no alerts that match your filtering criteria.</p>");
						}
					}
				});
			}

			function AjaxSendPeriodicReport(name) {
				var limit = <?php echo self::REPORT_LIMIT; // phpcs:ignore ?>;
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					async: true,
					dataType: 'json',
					data: {
						action: 'wsal_AjaxSendPeriodicReport',
						name: name,
						nextDate: nextDate,
						limit: limit
					},
					success: function(response) {
						checkStatus = setInterval( function() {
							if ( response.data ) {
								jQuery("#events-progress").hide();
								jQuery("#response-message").html( "<p>" + response.data + "</p>" );
								if ( true === response.success ) {
									jQuery( "#response-message p" ).addClass( 'sent' )
								}
								clearInterval( checkStatus );
							}
						}, 100);
					},
					error: function(xhr, textStatus, error) {
						console.log(xhr.statusText);
						console.log(textStatus);
						console.log(error);
					}
				});
			}
		</script>
		<?php
	}

	/**
	 * Generate report through Ajax call.
	 */
	public function ajax_generate_report( $data = array() ) {
		$selected_db = get_transient( 'wsal_wp_selected_db' );
		if ( ! empty( $selected_db ) && 'archive' === $selected_db ) {
			$this->plugin->settings()->switch_to_archive_db();
		}

		if ( empty( $data ) && isset( $_POST['filters'] ) ) {
			$filters             = $_POST['filters'];
			$filters['nextDate'] = $_POST['nextDate'];
			$filters['limit']    = $_POST['limit'];
			$add_archive         = $_POST['addArchive'];
			$user_id             = get_current_user_id();
			$events_found        = isset( $_POST['events_found'] ) ? sanitize_text_field( wp_unslash( $_POST['events_found'] ) ) : null;
		} else {
			if ( isset( $data ) && ! empty( $data ) ) {
				$filters             = $data['filters'];
				$filters['nextDate'] = $data['response']['nextDate'];
				$filters['limit']    = $data['response']['limit'];
				$add_archive         = $data['addArchive'];
				$events_found        = $data['events_found'];
				$user_id             = $data['user_id'];
				$iterations          = $data['iterations'];
			}
		}

		$report = $this->plugin->reports_util->generate_report( $filters, false );

		$response = array();

		if ( $this->plugin->reports_util->has_errors() ) {
			$errors             = $this->plugin->reports_util->get_errors();
			$response['errors'] = json_encode( $errors );
		}

		// Append to the JSON file.
		$this->plugin->reports_util->generate_report_json_file( $report, $user_id );

		$response[0]              = ( ! empty( $report['lastDate'] ) ) ? $report['lastDate'] : 0;
		$response['events_found'] = ( isset( $report['events_found'] ) && ! empty( $report['events_found'] ) ) ? $report['events_found'] : 0;

		if ( 0 === $response[0] ) {
			// Switch to Archive DB.
			if ( isset( $add_archive ) && 'true' === $add_archive ) { // phpcs:ignore
				if ( 'archive' !== $selected_db ) {
					// First time.
					$this->plugin->settings()->switch_to_archive_db();
					$filters['nextDate'] = null;
					$report              = $this->plugin->reports_util->generate_report( $filters, false );
					// Append to the JSON file.
					$this->plugin->reports_util->generate_report_json_file( $report, $user_id );
					if ( ! empty( $report['lastDate'] ) ) {
						WpSecurityAuditLog::set_transient( 'wsal_wp_selected_db', 'archive' );
						$response[0]              = $report['lastDate'];
						$response['events_found'] = ( isset( $events_found ) && ! empty( $events_found ) ) ? $events_found : 0; // phpcs:ignore
					}
				} else {
					// Last time.
					WpSecurityAuditLog::delete_transient( 'wsal_wp_selected_db' );
				}
			}

			if ( 0 === $response[0] ) {
				$response[1] = $this->plugin->reports_util->download_report_file( $user_id );
				$this->plugin->settings()->close_archive_db();

				//if ( ! empty( $data ) ) {
        $reports = $this->plugin->reports_util->get_all_reports();

				if ( ! empty ($reports)) {

					$report = array_pop( $reports );

					$title   = $report->get_filename();
					$subject = sprintf(
						// translators: The filename of the report.
						esc_html__(
							'Your report %s is ready',
							'wp-security-audit-log'
						),
						$title
					);
					$content = sprintf(
						// translators: The filename of the report, The website name.
						__(
							'Hello,<br><br>Your activity log report %1$1s is ready.<br>Please log in to your website <strong>%2$1s</strong> and navigate to the Saved Reports tab in the WP Activity Log plugin <b>Reports</b> section to download the report.<br><br>Thank you',
							'wp-security-audit-log'
						),
						$title,
						get_bloginfo( 'name' )
					);
					WSAL_Utilities_Emailer::send_email( User_Helper::get_user_email( $user_id ), $subject, $content );
				}
			}
		}

		if ( ! isset( $response[1] ) && empty ( $response['errors'] ) ) {
			$data = array();

			$data[] = array(
				'filters'      => $filters,
				'response'     => array_merge(
					$response,
					array(
						'nextDate' => $response[0],
						'limit'    => $filters['limit'],
					)
				),
				'addArchive'   => $add_archive,
				'events_found' => $response['events_found'],
				'user_id'      => $user_id,
			);

			// Run 7 times quickly before setting the cron.
			// Every call gets only one day - poor design, that code tries to speed up the process with small reports.
			if ( ! isset( $iterations ) ) {
				$data[0]['iterations'] = 1;
			} else {
				$data[0]['iterations'] = $iterations;
				$data[0]['iterations']++;
			}

			if ( 7 > $data[0]['iterations'] ) {
				set_time_limit( 0 );
				$this->ajax_generate_report( $data[0] );
			} else {
				$data[0]['iterations'] = 0;
				wp_schedule_single_event( time(), $this::REPORT_CRON_NAME, $data );
			}
		}

		echo json_encode( $response ); // phpcs:ignore
		exit;
	}

	/**
	 * Send the periodic report email through Ajax call.
	 */
	public function ajax_send_periodic_report() {
		$report_name = \sanitize_text_field( \wp_unslash( $_POST['name'] ) );
		$limit       = \sanitize_text_field( \wp_unslash( $_POST['limit'] ) );
		$sent        = $this->plugin->reports_util->send_now_periodic( $report_name, $limit );

		if ( true === $sent ) {
			wp_send_json_success( esc_html__( 'Periodic report was successfully sent.', 'wp-security-audit-log' ) );
		}

		wp_send_json_error( esc_html__( 'No events to report.', 'wp-security-audit-log' ) );
	}

	/**
	 * Check if the Archive is matching the filters, through Ajax call.
	 */
	public function ajax_check_archive_match() {
		$response = false;
		if ( Settings_Helper::is_archiving_enabled() ) {
			$filters = $_POST['filters']; // phpcs:ignore
			$this->plugin->settings()->switch_to_archive_db();
			$response = $this->plugin->reports_util->is_matching_report_criteria( $filters );
		}
		echo json_encode( $response ); // phpcs:ignore
		exit;
	}

	/**
	 * Generate summary unique IP report through Ajax call.
	 */
	public function ajax_summary_unique_ips() {
		$filters  = $_POST['filters']; // phpcs:ignore
		$response = $this->plugin->reports_util->statistics_unique_ips( $filters );
		echo json_encode( $response ); // phpcs:ignore
		exit;
	}

	/**
	 * Add/Edit Periodic Report.
	 *
	 * @param array $post_data - Post data array.
	 */
	public function save_periodic_report( $post_data ) {
		if ( isset( $post_data ) ) {
			$data            = new stdClass();
			$data->title     = strtolower( str_replace( array( ' ', '_' ), '-', $post_data['name'] ) );
			$data->email     = $post_data['email'];
			$data->type      = $post_data['report_format'];
			$data->frequency = $post_data['frequency'];

			$option_name = WSAL_Rep_Common::WSAL_PR_PREFIX . $data->title;

			$data->sites = array();
			if ( ! empty( $post_data['sites'] ) ) {
				$data->sites = $post_data['sites'];
			}

			$data->sites_exluded = array();
			if ( ! empty( $post_data['sites-exclude'] ) ) {
				$data->sites_excluded = $post_data['sites-exclude'];
			}

			if ( ! empty( $post_data['users'] ) ) {
				$data->users = $post_data['users'];
			}

			if ( ! empty( $post_data['users-exclude'] ) ) {
				$data->users_excluded = $post_data['users-exclude'];
			}

			if ( ! empty( $post_data['roles'] ) ) {
				$data->roles = $post_data['roles'];
			}

			if ( ! empty( $post_data['roles-exclude'] ) ) {
				$data->roles_excluded = $post_data['roles-exclude'];
			}

			if ( ! empty( $post_data['ip-addresses'] ) ) {
				$data->ipAddresses = $post_data['ip-addresses']; // phpcs:ignore
			}

			if ( ! empty( $post_data['ip-addresses-exclude'] ) ) {
				$data->ipAddresses_excluded = $post_data['ip-addresses-exclude']; // phpcs:ignore
			}

			if ( ! empty( $post_data['objects'] ) ) {
				$data->objects = $post_data['objects'];
			}

			if ( ! empty( $post_data['objects-exclude'] ) ) {
				$data->objects_excluded = $post_data['objects-exclude'];
			}

			if ( ! empty( $post_data['event-types'] ) ) {
				$data->event_types = $post_data['event-types'];
			}

			if ( ! empty( $post_data['event-types-exclude'] ) ) {
				$data->event_types_excluded = $post_data['event-types-exclude'];
			}

			if ( ! empty( $post_data['post_ids'] ) ) {
				$data->post_ids = $post_data['post_ids'];
			}

			if ( ! empty( $post_data['post_ids-exclude'] ) ) {
				$data->post_ids_excluded = $post_data['post_ids-exclude'];
			}

			if ( ! empty( $post_data['post_types'] ) ) {
				$data->post_types = $post_data['post_types'];
			}

			if ( ! empty( $post_data['post_types-exclude'] ) ) {
				$data->post_types_excluded = $post_data['post_types-exclude'];
			}

			if ( ! empty( $post_data['post_statuses'] ) ) {
				$data->post_statuses = $post_data['post_statuses'];
			}

			if ( ! empty( $post_data['post_statuses-exclude'] ) ) {
				$data->post_statuses_excluded = $post_data['post_statuses-exclude'];
			}

			$data->owner     = get_current_user_id();
			$data->dateAdded = time(); // phpcs:ignore
			$data->status    = 1;
			if ( ! empty( $post_data['alert_codes']['alerts'] ) ) {
				$data->alert_ids = $post_data['alert_codes']['alerts'];
			}

			if ( ! empty( $post_data['alert_codes']['alerts-exclude'] ) ) {
				$data->alert_ids_excluded = $post_data['alert_codes']['alerts-exclude'];
			}

			if ( ! empty( $post_data['alert_codes']['groups'] ) ) {
				$data->alert_groups = $post_data['alert_codes']['groups'];
			}

			if ( ! empty( $post_data['alert_codes']['groups-exclude'] ) ) {
				$data->alert_groups_excluded = $post_data['alert_codes']['groups-exclude'];
			}

			if ( array_key_exists( 'custom_title', $post_data ) ) {
				$data->custom_title = $post_data['custom_title'];
			}

			if ( array_key_exists( 'comment', $post_data ) ) {
				$data->comment = $post_data['comment'];
			}

			$this->plugin->reports_util->add_global_setting( $option_name, $data );
		}
	}

	/**
	 * Generate Statistics Report.
	 *
	 * @param array $filters Report comments.
	 */
	private function generate_statistics_report( $filters ) {
		if ( isset( $_POST['wsal-criteria'] ) ) { // phpcs:ignore
			$field                      = intval( trim( $_POST['wsal-criteria'] ) ); // phpcs:ignore
			$filters['type_statistics'] = $field;

			if ( WSAL_Rep_Common::LOGIN_ALL === $field ) {
				$filters['alert_codes']['alerts'] = array( 1000 );
			} elseif ( WSAL_Rep_Common::VIEWS_ALL === $field ) {
				$filters['alert_codes']['alerts'] = array( 2101, 2103, 2105 );
			} elseif ( WSAL_Rep_Common::PUBLISHED_ALL === $field ) {
				$filters['alert_codes']['alerts'] = array( 2001, 2005, 2030, 9001 );
			} elseif ( WSAL_Rep_Common::PASSWORD_CHANGES === $field ) {
				$filters['alert_codes']['alerts'] = array( 1010, 4003, 4004, 4029 );
			} elseif ( WSAL_Rep_Common::NEW_USERS === $field ) {
				$filters['alert_codes']['alerts'] = array( 4000, 4001 );
			} elseif ( WSAL_Rep_Common::PROFILE_CHANGES_ALL === $field ) {
				$filters['alert_codes']['alerts'] = array(
					4000,
					4001,
					4002,
					// 4003,
					// 4004,
					4005,
					4006,
					4007,
					// 4014,
					4015,
					4016,
					4017,
					4018,
					4019,
					4020,
					4021,
					// 4025,
					// 4026,
					// 4027,
					// 4028,
					// 4029,
					4008,
					4009,
					4010,
					4011,
					4012,
					4013,
					4024,
				);
			}

			if ( isset( $_POST[ 'wsal-summary-field_' . $field ] ) ) { // phpcs:ignore
				switch ( $field ) {
					case WSAL_Rep_Common::LOGIN_BY_USER:
						$filters['users'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						$filters['alert_codes']['alerts'] = array( 1000 );
						break;
					case WSAL_Rep_Common::LOGIN_BY_ROLE:
						$filters['roles'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						$filters['alert_codes']['alerts'] = array( 1000 );
						break;
					case WSAL_Rep_Common::VIEWS_BY_USER:
						$filters['users'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Viewed content alerts.
						$filters['alert_codes']['alerts'] = array( 2101, 2103, 2105 );
						break;
					case WSAL_Rep_Common::VIEWS_BY_ROLE:
						$filters['roles'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Viewed content alerts.
						$filters['alert_codes']['alerts'] = array( 2101, 2103, 2105 );
						break;
					case WSAL_Rep_Common::VIEWS_BY_POST:
						$filters['post_ids'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Viewed content alerts.
						$filters['alert_codes']['alerts'] = array( 2101, 2103, 2105 );
						break;
					case WSAL_Rep_Common::PUBLISHED_BY_USER:
						$filters['users'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Published content alerts.
						$filters['alert_codes']['alerts'] = array( 2001, 2005, 2030, 9001 );
						break;
					case WSAL_Rep_Common::PUBLISHED_BY_ROLE:
						$filters['roles'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Published content alerts.
						$filters['alert_codes']['alerts'] = array( 2001, 2005, 2030, 9001 );
						break;
					case WSAL_Rep_Common::PROFILE_CHANGES_BY_USER:
						$filters['users'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Published content alerts.
						$filters['alert_codes']['alerts'] = array(
							4000,
							4001,
							4002,
							// 4003,
							// 4004,
							4005,
							4006,
							4007,
							// 4014,
							4015,
							4016,
							4017,
							4018,
							4019,
							4020,
							4021,
							// 4025,
							// 4026,
							// 4027,
							// 4028,
							// 4029,
							4008,
							4009,
							4010,
							4011,
							4012,
							4013,
							4024,
						);
						break;
					case WSAL_Rep_Common::PROFILE_CHANGES_BY_ROLE:
						$filters['roles'] = $_POST[ 'wsal-summary-field_' . $field ]; // phpcs:ignore
						// Published content alerts.
						$filters['alert_codes']['alerts'] = array(
							4000,
							4001,
							4002,
							// 4003,
							// 4004,
							4005,
							4006,
							4007,
							// 4014,
							4015,
							4016,
							4017,
							4018,
							4019,
							4020,
							4021,
							// 4025,
							// 4026,
							// 4027,
							// 4028,
							// 4029,
							4008,
							4009,
							4010,
							4011,
							4012,
							4013,
							4024,
						);
						break;
					default:
						// Fallback for any other fields would go here.
						break;
				}
			}

			if ( intval( $field ) === WSAL_Rep_Common::DIFFERENT_IP ) {
				if ( isset( $_POST['only_login'] ) ) { // phpcs:ignore
					$filters['alert_codes']['alerts'] = array( 1000 );
				}
			}

			// Process the time period grouping selection.
			$filters['grouping_period'] = array_key_exists( 'wsal-grouping-period', $_POST ) ? trim( wp_unslash( $_POST['wsal-grouping-period'] ) ) : 'day'; // phpcs:ignore
		}

		// @codingStandardsIgnoreStart
		if ( ! empty( $_POST['wsal-from-date'] ) ) {
			$filters['date_range']['start'] = trim( $_POST['wsal-from-date'] );
		}

		if ( ! empty( $_POST['wsal-to-date'] ) ) {
			$filters['date_range']['end'] = trim( $_POST['wsal-to-date'] );
		}

		if ( isset( $_POST['include-archive'] ) ) {
			$this->plugin->reports_util->add_global_setting( 'include-archive', true );
		} else {
			$this->plugin->reports_util->delete_global_setting( 'include-archive' );
		}
		// @codingStandardsIgnoreEnd
		?>
		<script type="text/javascript">
			var filters = <?php echo json_encode( $filters ); // phpcs:ignore ?>;
		</script>
		<?php if ( ! empty( $field ) && WSAL_Rep_Common::is_report_ip_based( $field ) ) : ?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					AjaxSummaryUniqueIPs(filters);
				});
			</script>
		<?php else : ?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					AjaxCheckArchiveMatch(filters);
					AjaxGenerateReport(filters);
				});
			</script>
		<?php endif; ?>
		<div class="updated">
			<p id="ajax-response">
				<img src="<?php echo esc_url( $this->base_url ); ?>/css/loading.gif">
				<?php esc_html_e( ' Generating reports. Please do not close this window.', 'wp-security-audit-log' ); ?>
				<span id="ajax-response-counter"></span>
			</p>
		</div>
		<?php
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			$network_admin = get_site_option( 'admin_email' );
			$message       = esc_html__( 'To generate a report or configure automated scheduled report please contact the administrator of this multisite network on ', 'wp-security-audit-log' );
			$message      .= '<a href="mailto:' . esc_attr( $network_admin ) . '" target="_blank">' . esc_html( $network_admin ) . '</a>';
			wp_die( $message ); // phpcs:ignore
		}

		// Verify the uploads directory.
		$reports_working_dir = \WSAL_Settings::get_working_dir_path_static( 'reports' );

		if ( ! is_wp_error( $reports_working_dir ) && $this->plugin->reports_util->check_directory( $reports_working_dir ) ) {
			$plugin_dir = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR );
			include $plugin_dir . '/inc/wsal-reporting-view.inc.php';

			return;
		}

		// Skip creation this time to get the path for the prompt even if the folder cannot be created.
		$reports_working_dir = \WSAL_Settings::get_working_dir_path_static( 'reports', true );
		?>
			<div class="error">
				<p><?php printf( __( 'The %s directory which the Reports plugin uses to create reports in was either not found or is not accessible.', 'wp-security-audit-log' ), 'uploads' ); // phpcs:ignore ?></p>
				<p>
					<?php
					// @codingStandardsIgnoreStart
					printf(
						esc_html__( 'In order for the plugin to function, the directory %1$s must be created and the plugin should have access to write to this directory, so please configure the following permissions: 0755. If you have any questions or need further assistance please %2$s', 'wp-security-audit-log' ),
						'<strong>' . $reports_working_dir . '</strong>',
						'<a href="mailto:support@wpwhitesecurity.com">contact us</a>'
					);
					// @codingStandardsIgnoreEnd
					?>
				</p>
			</div>
		<?php

	}

	/**
	 * Handles AJAX call that triggers report file download.
	 *
	 * @since 4.3.2
	 */
	public function process_report_download() {
		// #! No  cache
		if ( ! headers_sent() ) {
			header( 'Expires: Mon, 26 Jul 1990 05:00:00 GMT' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );
		}

		$strm = '[WSAL Reporting Plugin] Requesting download';

		// @codingStandardsIgnoreStart
		// Validate nonce.
		// if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'wpsal_reporting_nonce_action' ) ) {
		// 	wp_die( $strm . ' with a missing or invalid nonce [code: 1000]' );
		// }

		// Missing f param from url.
		if ( ! isset( $_GET['f'] ) ) {
			wp_die( $strm . ' without the "f" parameter [code: 2000]' );
		}

		// Missing ctype param from url.
		if ( ! isset( $_GET['ctype'] ) ) {
			wp_die( $strm . ' without the "ctype" parameter [code: 3000]' );
		}

		// Invalid fn provided in the url.
		$fn = base64_decode( $_GET['f'] ); // @codingStandardsIgnoreLine
		if ( false === $fn ) {
			wp_die( $strm . ' without a valid base64 encoded file name [code: 4000]' );
		}

		$dir       = \WSAL_Settings::get_working_dir_path_static( 'reports', true );
		$file_path = $dir . $fn;

		// Directory traversal attacks won't work here.
		if ( preg_match( '/\.\./', $file_path ) ) {
			wp_die( $strm . ' with an invalid file name (' . $fn . ') [code: 6000]' );
		}
		if ( ! is_file( $file_path ) ) {
			wp_die( $strm . ' with an invalid file name (' . $fn . ') [code: 7000]' );
		}

		$data_format = intval( wp_unslash( $_GET['ctype'] ) );
		if ( ! WSAL_Rep_DataFormat::is_valid( $data_format ) ) {
			// Content type is not valid.
			wp_die( $strm . ' with an invalid content type [code: 7000]' );
		}

		$content_type = WSAL_Rep_DataFormat::get_content_type( $data_format );
		$file_size	= filesize( $file_path );
		$file		 = fopen( $file_path, 'rb' );

		// - turn off compression on the server - that is, if we can...
		ini_set( 'zlib.output_compression', 'Off' );
		// set the headers, prevent caching + IE fixes.
		header( 'Pragma: public' );
		header( 'Expires: -1' );
		header( 'Cache-Control: public, must-revalidate, post-check=0, pre-check=0' );
		if ('text/html'!==$content_type)
		header( 'Content-Disposition: attachment; filename="' . $fn . '"' );
		header( "Content-Length: $file_size" );
		header( "Content-Type: {$content_type}" );
		set_time_limit( 0 );
		while ( ! feof( $file ) ) {
			print( fread( $file, 1024 * 8 ) );
			ob_flush();
			flush();
			if ( connection_status() != 0 ) {
				fclose( $file );
				exit;
			}
		}
		// File save was a success.
		fclose( $file );
		// @codingStandardsIgnoreEnd
		exit;
	}

	/**
	 * Renders data format selection.
	 *
	 * @param string $field_name       Input field name. Also used as prefix for the field IDs and labels.
	 * @param string $selected_format  Pre-selected data format.
	 * @param int    $step_number      Step number for the section title.
	 * @param int[]  $excluded_formats Data formats to exclude.
	 *
	 * @since 4.4.0
	 */
	private function render_data_format_select( $field_name, $selected_format, $step_number, $excluded_formats = array() ) {
		$heading = sprintf(
			/* translators: step number for the section title */
			esc_html__( 'Step %d: Select Report Format', 'wp-security-audit-log' ),
			$step_number
		);
		?>
		<h4 class="wsal-reporting-subheading"><?php echo $heading; // phpcs:ignore ?></h4>
		<div class="wsal-rep-form-wrapper">
			<div class="wsal-rep-section">
				<div class="wsal-rep-section-fl">
					<?php
					$format_counter = 1;
					foreach ( WSAL_Rep_DataFormat::get_all() as $data_format ) {
						if ( in_array( $data_format, $excluded_formats, true ) ) {
							continue;
						}
						$is_disabled = WSAL_Rep_DataFormat::PDF === $data_format && ! \WSAL_Extension_Manager::is_pdf_reporting_available();
						// @codingStandardsIgnoreStart
						?>
						<p class="wsal-rep-clear">
							<input type="radio" name="<?php echo $field_name; ?>" value="<?php echo $data_format; ?>"
								   id="<?php echo $field_name; ?>-<?php echo $format_counter; ?>"
								<?php checked( $selected_format, $data_format ); ?> <?php disabled( $is_disabled ); ?>
							/>
							<label for="<?php echo $field_name; ?>-<?php echo $format_counter; ?>"><?php echo WSAL_Rep_DataFormat::get_label( $data_format ); ?></label>
						</p>
						<?php
						// @codingStandardsIgnoreEnd
						if ( $is_disabled ) {
							\WSAL_Extension_Manager::render_helper_plugin_notice( esc_html__( 'To generate PDF reports you need to install an extension. Please click the button below to automatically install and activate the plugin extension so you can generate the reports you need.', 'wp-security-audit-log' ) );
						}
						$format_counter ++;
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders grouping time period selection.
	 *
	 * @param string $field_name       Input field name. Also used as prefix for the field IDs and labels.
	 * @param string $selected         Pre-selected data format.
	 *
	 * @since 4.4.0
	 */
	private function render_grouping_period_select( $field_name, $selected ) {
		$values = array(
			'day'   => esc_html__( 'Per day', 'wp-security-audit-log' ),
			'week'  => esc_html__( 'Per week', 'wp-security-audit-log' ),
			'month' => esc_html__( 'Per month', 'wp-security-audit-log' ),
		);
		// @codingStandardsIgnoreStart
		?>
		<fieldset>
			<?php foreach ( $values as $value => $label ) : ?>
				<p class="wsal-rep-clear">
					<input type="radio" name="<?php echo $field_name; ?>" value="<?php echo $value; ?>"
							id="<?php echo $field_name; ?>-<?php echo $value; ?>"
							<?php checked( $selected, $value ); ?>
					/>
					<label for="<?php echo $field_name; ?>-<?php echo $value; ?>"><?php echo $label; ?></label>
				</p>
			<?php endforeach; ?>
		</fieldset>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Helper method to build the select UI control attributes.
	 *
	 * @param string $placeholder   Field placeholder.
	 * @param string $field_name    Field name.
	 * @param array  $extras        Any extra options.
	 * @param object $report_object Report object.
	 * @param string $key           Data filter key.
	 *
	 * @return array
	 */
	private static function build_selection_params( $placeholder, $field_name, $extras = array(), $report_object = null, $key = null ) {
		$result = array(
			'placeholder'       => $placeholder,
			'id'                => $field_name,
			'name'              => $field_name . '[]',
			'width'             => 500,
			'multiple'          => true,
			'extra_js_callback' => function ( $element_id ) {
				echo 'window.wsal_reporting.append_select2_events( s2 );';
			},
		);

		if ( ! is_null( $report_object ) && ! is_null( $key ) && property_exists( $report_object, $key ) && ! empty( $report_object->$key ) ) {
			$result['selected'] = $report_object->$key;
		}

		if ( ! empty( $extras ) ) {
			$result = array_merge( $result, $extras );
		}

		return $result;
	}

	/**
	 * Renders a generic selection field.
	 *
	 * @param string $placeholder   Field placeholder.
	 * @param array  $options       Data options to display.
	 * @param string $field_name    Field name.
	 * @param object $report_object Report object.
	 * @param string $key           Data filter key.
	 *
	 * @since 4.4.0
	 */
	private function render_generic_selection_field( $placeholder, $options, $field_name, $report_object = null, $key = null ) {
		\S24WP::insert(
			self::build_selection_params(
				$placeholder,
				$field_name,
				array(
					'data' => $options,
				),
				$report_object,
				$key
			)
		);
	}

	/**
	 * Renders a user selection field.
	 *
	 * @param string $field_name    Field name.
	 * @param object $report_object Report object.
	 * @param string $key           Data filter key.
	 *
	 * @since 4.4.0
	 */
	private function render_user_selection_field( $field_name, $report_object = null, $key = null ) {
		\S24WP::insert(
			self::build_selection_params(
				esc_html__( 'Select user(s)', 'wp-security-audit-log' ),
				$field_name,
				array(
					'data-type'               => 'user',
					'remote_source_threshold' => 100,
				),
				$report_object,
				$key
			)
		);
	}

	/**
	 * Renders a role selection field.
	 *
	 * @param string $field_name    Field name.
	 * @param object $report_object Report object.
	 * @param string $key           Data filter key.
	 *
	 * @since 4.4.0
	 */
	private function render_role_selection_field( $field_name, $report_object = null, $key = null ) {
		\S24WP::insert(
			self::build_selection_params(
				esc_html__( 'Select role(s)', 'wp-security-audit-log' ),
				$field_name,
				array(
					'data-type' => 'role',
				),
				$report_object,
				$key
			)
		);
	}

	/**
	 * Renders a post selection field.
	 *
	 * @param string $field_name    Field name.
	 * @param bool   $allow_multi   Allows multiple values.
	 * @param object $report_object Report object.
	 * @param string $key           Data filter key.
	 *
	 * @since 4.4.0
	 */
	private function render_post_selection_field( $field_name, $allow_multi, $report_object = null, $key = null ) {
		$placeholder = $allow_multi ? esc_html__( 'Select post(s)', 'wp-security-audit-log' ) : esc_html__( 'Select post', 'wp-security-audit-log' );
		\S24WP::insert(
			self::build_selection_params(
				$placeholder,
				$field_name,
				array(
					'data-type' => 'post',
					'multi'     => $allow_multi,
				),
				$report_object,
				$key
			)
		);
	}
}
