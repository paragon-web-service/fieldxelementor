<?php
/**
 * Class: Daily Notification Class
 *
 * Daily email notification class for plugin activity.
 *
 * @since 3.2.4
 *
 * @package wsal
 * @subpackage email-notifications
 */

use WSAL\Helpers\User_Helper;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_NP_DailyNotification
 *
 * Daily email notification class for plugin activity.
 *
 * @package wsal
 * @subpackage email-notifications
 */
class WSAL_NP_DailyNotification {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	public $wsal = null;

	/**
	 * Media Links.
	 *
	 * @var array
	 */
	private $media = array();

	/**
	 * Type Name Setting.
	 *
	 * @since 3.3
	 *
	 * @var array
	 */
	private $type_name = array();

	/**
	 * User Data.
	 *
	 * @since 3.3
	 *
	 * @var array
	 */
	private $user_data = array();

	/**
	 * Daily Report Events
	 *
	 * Events to be included in the daily report summary.
	 *
	 * @var array
	 */
	public static $daily_report_events = array( 1000, 1002, 1003, 2001, 2008, 2012, 2065, 4000, 4001, 4002, 4003, 4004, 4007, 4010, 4011, 5000, 5001, 5002, 5003, 5004, 6028, 6029, 6030, 7000, 7001, 7002, 7003, 7004, 7005 );

	/**
	 * Method: Constructor.
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 */
	public function __construct( WpSecurityAuditLog $wsal ) {
		$this->wsal = $wsal;
		$this->set_media();
		$this->type_name = $wsal->settings()->get_type_username(); // Get the data to display.
	}

	/**
	 * Set Media Links.
	 */
	private function set_media() {
		$this->media['table-bg']          = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/table-bg.jpg';
		$this->media['box-shadow-up']     = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/box-shadow-up.png';
		$this->media['box-shadow-left']   = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/box-shadow-left.png';
		$this->media['logo']              = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/logo.png';
		$this->media['documentation']     = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/documentation.png';
		$this->media['get-support']       = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/get-support.png';
		$this->media['box-shadow-right']  = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/box-shadow-right.png';
		$this->media['box-shadow-bottom'] = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/box-shadow-bottom.png';
		$this->media['check']             = trailingslashit( WSAL_BASE_URL ) . 'img/mails/daily-notification/alert-icon.png';
	}

	/**
	 * Returns report email body.
	 *
	 * @param boolean $test - Test report (Sends current date's report).
	 * @return stdClass
	 */
	public function get_report( $test = false ) {
		$date_format = $this->wsal->settings()->get_date_format(); // Get date format.
		$date_obj    = new DateTime();
		$date_obj->setTime( 0, 0 ); // Set time of the object to 00:00:00.
		$date_string      = $date_obj->format( 'U' ); // Get the date in UNIX timestamp.
		$disable_if_empty = \WSAL\Helpers\Settings_Helper::get_option_value( 'disable-daily-summary-if-no-activity' ); // Option to disable if no alerts found.

		if ( ! $test ) {
			$start = strtotime( '-1 day +1 second', $date_string ); // Get yesterday's starting timestamp.
			$end   = strtotime( '-1 second', $date_string ); // Get yesterday's ending timestamp.
		} else {
			// If test then set the start and end timestamps to today's date.
			$start = strtotime( '+1 second', $date_string );
			$end   = strtotime( '+1 day -1 second', $date_string );
		}

		// Setup the query ready to run it.
		$query = $this->query_report_data_by_time( $start, $end );

		// count the events and execute the query.
		$total_events = $query->get_adapter()->count( $query );
		/** @var WSAL_Models_Occurrence $events */
		$events = $query->get_adapter()->execute_query( $query );

		if ( ! $test && $disable_if_empty && empty( $events ) ) {
			return false;
		}

		$home_url = home_url();
		$safe_url = str_replace( array( 'http://', 'https://' ), '', $home_url );

		// the date displayed in daily reports.
		$display_date = date( $date_format, $start ); // phpcs:ignore

		// Report object.
		$report          = new stdClass();
		$report->subject = 'Activity Log Highlight from ' . $safe_url . ' on ' . $display_date; // Email subject.
		$report->body    = $this->generate_report_body( $events, $display_date, $total_events ); // Email body.

		return $report;
	}

	/**
	 * Gets a query of reportable events based on a start time and end time.
	 *
	 * @method query_report_data_by_time
	 * @since
	 * @param  string|int $start unix timestamp when to start looking at events from.
	 * @param  string|int $end   unix timestamp when to end looking at events from.
	 * @return WSAL_Models_OccurrenceQuery
	 */
	public function query_report_data_by_time( $start, $end ) {
		$query   = new WSAL_Models_OccurrenceQuery();
		$site_id = $this->wsal->settings()->get_view_site_id();
		// if we have a site ID then add it as condition.
		if ( $site_id ) {
			$query->add_condition( 'site_id = %s ', $site_id );
		}
		// add condition to check only alerts that are daily report events.
		$query->add_condition( 'find_in_set( alert_id, %s ) > 0 ', implode( ',', self::$daily_report_events ) );
		// from this time.
		$query->add_condition( 'created_on >= %s', $start ); // From the hour 00:00:01.
		// till this time.
		$query->add_condition( 'created_on <= %s', $end ); // To the hour 23:59:59.
		// order results by date and return the query.
		$query->add_order_by( 'created_on', false );
		return $query;
	}

	/**
	 * Generate Report Body.
	 *
	 * @param WSAL_Models_Occurrence[] $events       - Array of events.
	 * @param string                   $report_date  - Date of report.
	 * @param integer                  $total_events - Number of events.
	 *
	 * @return string
	 */
	private function generate_report_body( $events, $report_date, $total_events ) {
		$body  = $this->get_report_head();
		$body .= $this->get_report_body( $events, $report_date, $total_events );
		$body .= $this->get_report_footer();

		return $body;
	}

	/**
	 * Returns Report Head.
	 *
	 * @return string
	 */
	private function get_report_head() {
		return '<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /><title>WP Activity Log</title></head><body style="margin: 0; padding: 0">';
	}

	/**
	 * Returns Report Footer.
	 *
	 * @return string
	 */
	private function get_report_footer() {
		return '</body></html>';
	}

	/**
	 * Get User Details for Email.
	 *
	 * Get Username/First Name/Last Name/Public Name to display in the emails.
	 *
	 * @since 3.3
	 *
	 * @param string $username – Username.
	 * @return string
	 */
	private function get_user_for_email( $username ) {
		if ( 'username' === $this->type_name ) {
			return $username;
		} else {
			// Check if user details are already set.
			if ( isset( $this->user_data[ $username ] ) && ! empty( $this->user_data[ $username ] ) ) {
				$user = $this->user_data[ $username ];
			} else {
				// If not set, then get user details.
				$user = get_user_by( 'login', $username );

				// Set user details.
				$this->user_data[ $username ] = $user;
			}

			// Type of detail to display.
			$display_name = '';

			if ( $user ) {
				// Check for the type of name to display.
				if ( 'display_name' === $this->type_name && ! empty( $user->display_name ) ) {
					$display_name = $user->display_name;
				} elseif ( 'first_last_name' === $this->type_name && ( ! empty( $user->first_name ) || ! empty( $user->last_name ) ) ) {
					$display_name = $user->first_name . ' ' . $user->last_name;
				} else {
					$display_name = $user->user_login;
				}
			} else {
				$display_name = $username;
			}
			return ( null !== $display_name ) ? $display_name : esc_html__( 'System', 'wp-security-audit-log' );
		}
	}

	/**
	 * Get User Details.
	 *
	 * Get Username/First Name/Last Name/Public Name to display in the emails.
	 *
	 * @since 4.4.3.1
	 *
	 * @param string $user_id – The user ID.
	 * @return string
	 */
	private function get_user_name( $user_id ) {
		if ( null === $user_id ) {
			return esc_html__( 'System', 'wp-security-audit-log' );
		}
		$user = User_Helper::get_user_object( $user_id );
		if ( 'username' === $this->type_name ) {
			return $user->user_login;
		} else {
			// Type of detail to display.
			$display_name = '';

			if ( $user ) {
				// Check for the type of name to display.
				if ( 'display_name' === $this->type_name && ! empty( $user->display_name ) ) {
					$display_name = $user->display_name;
				} elseif ( 'first_last_name' === $this->type_name && ( ! empty( $user->first_name ) || ! empty( $user->last_name ) ) ) {
					$display_name = $user->first_name . ' ' . $user->last_name;
				} else {
					$display_name = $user->user_login;
				}
			} else {
				$display_name = null;
			}
			return ( null !== $display_name ) ? $display_name : esc_html__( 'System', 'wp-security-audit-log' );
		}
	}

	/**
	 * Returns Report Body.
	 *
	 * @param WSAL_Models_Occurrence[] $events       - Array of events.
	 * @param string                   $report_date  - Date of report.
	 * @param integer                  $total_events - Number of events.
	 *
	 * @return string
	 */
	private function get_report_body( $events, $report_date, $total_events ) {
		$home_url = home_url();
		$safe_url = str_replace( array( 'http://', 'https://' ), '', $home_url );

		$number_of_logins         = 0;       // Number of logins.
		$login_events             = array(); // Login events.
		$failed_logins_wrong_pass = array(); // Failed logins wrong pass.
		$failed_logins_wrong_user = array(); // Failed logins wrong user.
		$password_changes         = array(); // Password changes.
		$forced_password_changes  = array(); // Forced password changes.
		$user_profile_changes     = array(); // User profile changes.
		$multisite_activity       = array(); // Multisite network activity.
		$plugin_activity          = array(); // Plugin activity.
		$posts_published          = array(); // Posts published.
		$posts_trashed            = array(); // Posts trashed.
		$posts_deleted            = array(); // Posts deleted.
		$posts_modified           = array(); // Posts modified.
		$files_added              = array(); // Files added.
		$files_modified           = array(); // Files modified.
		$files_deleted            = array(); // Files deleted.
		$plugin_events            = array( 5000, 5001, 5002, 5003, 5004 ); // Plugin events.
		$user_profile_events      = array( 4000, 4001, 4002, 4007 ); // Multisite events.
		$multisite_events         = array( 4010, 4011, 7000, 7001, 7002, 7003, 7004, 7005 ); // Multisite events.

		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				if ( 1000 === $event['alert_id'] ) {
					$number_of_logins++;
					$login_events[] = $event;
				} elseif ( 1002 === $event['alert_id'] ) {
					$failed_logins_wrong_pass[] = $event;
				} elseif ( 1003 === $event['alert_id'] ) {
					$failed_logins_wrong_user[] = $event;
				} elseif ( 4003 === $event['alert_id'] ) {
					$password_changes[] = $event;
				} elseif ( 4004 === $event['alert_id'] ) {
					$forced_password_changes[] = $event;
				} elseif ( in_array( $event['alert_id'], $plugin_events, true ) ) {
					$plugin_activity[] = $event;
				} elseif ( 2001 === $event['alert_id'] ) {
					$posts_published[] = $event;
				} elseif ( 2012 === $event['alert_id'] ) {
					$posts_trashed[] = $event;
				} elseif ( 2008 === $event['alert_id'] ) {
					$posts_deleted[] = $event;
				} elseif ( 2065 === $event['alert_id'] ) {
					$posts_modified[] = $event;
				} elseif ( in_array( $event['alert_id'], $user_profile_events, true ) ) {
					$user_profile_changes[] = $event;
				} elseif ( in_array( $event['alert_id'], $multisite_events, true ) ) {
					$multisite_activity[] = $event;
				} elseif ( 6028 === $event['alert_id'] ) {
					$files_modified[] = $event;
				} elseif ( 6029 === $event['alert_id'] ) {
					$files_added[] = $event;
				} elseif ( 6030 === $event['alert_id'] ) {
					$files_deleted[] = $event;
				}
			}
		}

		$body  = '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-image: url(' . $this->media['table-bg'] . '); background-repeat: repeat-x; background-color: #ffffff; padding-top: 20px;">';
		$body .= '<tr><td align="center"><div style="width: 100%; max-width: 630px; margin: 0 auto;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 630px; margin: 0 auto;"><tbody><tr><td colspan="3" height="11" style="background-image: url(' . $this->media['box-shadow-up'] . '); height: 20px; background-repeat: repeat-x;"></td></tr><tr><td width="13" style=" background-image: url(' . $this->media['box-shadow-left'] . ');background-repeat: repeat-y;"></td><td><table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 620px; margin: 0 auto; background: #ffffff;"><tbody><!-- Header Strat --><tr><td style="background-color: #ffffff;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding-top: 50px; padding-bottom: 43px; background-color: #eeede8;"><tbody><!-- Logo Strat --><tr><td style="text-align: center;"><a href="#" target="_blank" style="display: inline-block;"><img src="' . $this->media['logo'] . '" alt="WSAL Logo"></a></td></tr><!-- Logo End --><!-- Tag Line Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 20px; line-height: 28px; color: #3e3e3e; text-align: center; padding-top: 22px; padding-bottom: 5px; padding-right: 10px; padding-left: 10px;">Your daily WordPress activity log highlight</td></tr><!-- Tag Line Start --></tbody></table></td></tr><!-- Header End --><!-- Mailer Content Start --><tr><td style="background-color: #ffffff; padding-left: 40px; padding-right: 40px; padding-top: 34px;"><table width="100%" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 620px; margin: 0 auto; background: #ffffff;"><!-- Hello Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Hello,</td></tr><!-- Title End --><!-- Desc Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 34px;">';
		$body .= sprintf( 'This email was sent from your <a href="%1$s" target="_blank" style="color: #404040; text-decoration: none; display: inline-block;">%2$s</a>. It is a summary generated by the <a href="https://wpactivitylog.com" target="_blank" style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #149247; text-decoration: underline; display: inline-block;">WP Activity Log plugin</a> about what happened on %3$s.', $home_url, $safe_url, $report_date );
		$body .= '</td></tr><!-- Desc End -->';

		if ( empty( $events ) ) {
			$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 34px;">No events so far.</td></tr><!-- Desc End -->';
		}

		$body .= '</table></td></tr><!-- Hello End -->';

		if ( ! empty( $events ) ) {
			// User logins.
			if ( $number_of_logins && ! empty( $login_events ) ) {
				$user_logins = array();
				foreach ( $login_events as $login_event ) {
					$username = $login_event['username'];
					$ipaddr   = $login_event['client_ip'];

					if ( ! empty( $username ) && ! empty( $ipaddr ) ) {
						$user_logins[ $username ][] = $ipaddr;
					}
				}
				$login_count_string = sprintf(
					// translators: singular or plural form of a login total count.
					_n( 'was %d login', 'were %d logins', $number_of_logins, 'wp-security-audit-log' ),
					$number_of_logins
				);
				$users_logged_count = ( is_array( $user_logins ) && ! empty( $user_logins ) ) ? count( $user_logins ) : '1';
				$user_count_string  = sprintf(
					// translators: a number that is total count of unique users in a login group.
					_n( '%d unique user', '%d unique users', $users_logged_count, 'wp-security-audit-log' ),
					$users_logged_count
				);

				$body .= '<!-- User Logins Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">User Logins</td></tr><!-- Title End --><!-- Desc Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; text-align: left;">';
				$body .= sprintf(
					/* Translators: 1 - number of logins. 2 - total unique users */
					__( 'There %1$s on your site today from %2$s. Below is a list of the users and the IP addresses they logged in from:', 'wp-security-audit-log' ),
					$login_count_string,
					$user_count_string
				);
				$body .= '</td></tr><!-- Table Border Start --><tr><td style="padding-top: 20px; padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">';

				if ( ! empty( $user_logins ) ) {
					foreach ( $user_logins as $username => $ipaddrs ) {
						$ipaddr = array_unique( $ipaddrs );
						$ipaddr = implode( ',', $ipaddr );

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						/* 1. Username 2. IP Address */
						$body .= sprintf( 'User %1$s from %2$s', '<span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $username ) . '</span>', '<span style="display: inline-block; color: #149247;">' . $ipaddr . '</span>' );
						$body .= '</td></tr>';
					}
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- User Logins End -->';
			}

			// Failed user logins.
			if ( ! empty( $failed_logins_wrong_pass ) || ! empty( $failed_logins_wrong_user ) ) {
				$body .= '<!-- Failed Logins Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Failed Logins</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-top: 10px; padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">';

				/*
				 * Logs when logins were attempted that used the wrong password.
				 * Displays a message and a <table> of IPs.
				 */
				if ( ! empty( $failed_logins_wrong_pass ) ) {
					$body .= '<tr><td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
					$body .= esc_html__( 'There were failed logins due to a wrong password from the following IP addresses:', 'wp-security-audit-log' );

					$previous_ips = array();
					foreach ( $failed_logins_wrong_pass as $event ) {
						$current_ip = $event['client_ip'];
						$body      .= '<table><tbody>';
						if ( ! in_array( $current_ip, $previous_ips, true ) ) {
							$body          .= '<tr><td><span style="display: block; color: #149247;">' . $current_ip . '</span></td></tr>';
							$previous_ips[] = $current_ip;
						}
						$body .= '</tbody></table>';
					}

					$body .= '</td></tr>';
				}

				/*
				 * Logs when logins were attempted that used the wrong username.
				 * Displays a message and a <table> of IPs.
				 */
				if ( ! empty( $failed_logins_wrong_user ) ) {
					$body .= '<tr><td style="border-bottom: 1px solid #b2b2ad; border-left: 1px solid #b2b2ad; border-right: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
					$body .= esc_html__( 'There were failed logins due to a wrong username from the following IP addresses:', 'wp-security-audit-log' );

					$previous_ips = array();
					foreach ( $failed_logins_wrong_user as $event ) {
						$current_ip = $event['client_ip'];
						$body      .= '<table><tbody>';
						if ( ! in_array( $current_ip, $previous_ips, true ) ) {
							$body          .= '<tr><td><span style="display: block; color: #149247;">' . $current_ip . '</span></td></tr>';
							$previous_ips[] = $current_ip;
						}
						$body .= '</tbody></table>';
					}

					$body .= '</td></tr>';
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Failed Logins End -->';
			}

			// Password changes.
			if ( ! empty( $password_changes ) || ! empty( $forced_password_changes ) ) {
				$body .= '<!-- Password Changes Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Password Changes</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">';

				if ( ! empty( $password_changes ) ) {
					$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px;">These users changed their password:</td></tr>';

					foreach ( $password_changes as $event ) {
						$user_data = ( ( isset( $event['meta_values']['TargetUserData'] ) ) ? $event['meta_values']['TargetUserData'] : false );
						if ( ! $user_data ) {
							continue;
						}

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= '<span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $user_data->Username ) . '</span> from <span style="display: inline-block; color: #149247;">' . $event['client_ip'] . '</span>';
						$body .= '</td></tr>';
					}
				}

				if ( ! empty( $forced_password_changes ) ) {
					$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px; padding-top: 20px;">These users had their password changed:</td></tr>';

					foreach ( $forced_password_changes as $event ) {
						$user_data = ( ( isset( $event['meta_values']['TargetUserData'] ) ) ? $event['meta_values']['TargetUserData'] : false );
						if ( ! $user_data ) {
							continue;
						}

						$body .= '<tr>';
						$body .= '<td style="border-top: 1px solid #b2b2ad; border-bottom: 1px solid #b2b2ad; border-left: 1px solid #b2b2ad; border-right: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= '<span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $user_data->Username ) . '</span> — password changed by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> from <span style="display: inline-block; color: #149247;">' . $event['clientip'] . '</span>';
						$body .= '</td></tr>';
					}
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Password Changes End -->';
			}

			// User profile changes.
			if ( ! empty( $user_profile_changes ) ) {
				$body .= '<!-- User Profile Changes Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">User Profile Changes</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px;">Below is a list of important user profile changes that happened on your website:</td></tr>';

				foreach ( $user_profile_changes as $event ) {
					if ( 4000 === $event['alert_id'] ) {
						$user_data = ( ( isset( $event['meta_values']['NewUserData'] ) ) ? $event['meta_values']['NewUserData'] : false );
						if ( $user_data ) {
							$body .= '<tr>';
							$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							$body .= 'User <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $user_data->Username ) . '</span> has registered on your website from <span style="display: inline-block; color: #149247;">' . $event['client_ip'] . '</span>';
							$body .= '</td>';
							$body .= '</tr>';
						}
					} elseif ( 4001 === $event['alert_id'] ) {
						$user_data = ( ( isset( $event['meta_values']['NewUserData'] ) ) ? $event['meta_values']['NewUserData'] : false );
						if ( $user_data ) {
							$body .= '<tr>';
							$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has created the user <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $user_data->Username ) . '</span> with the role <span style="display: inline-block; color: #149247;">' . $user_data->Roles . '</span>';
							$body .= '</td>';
							$body .= '</tr>';
						}
					} elseif ( 4002 === $event['alert_id'] ) {
						$username = ( ( isset( $event['meta_values']['TargetUsername'] ) ) ? $event['meta_values']['TargetUsername'] : false );
						$userrole = ( ( isset( $event['meta_values']['NewRole'] ) ) ? $event['meta_values']['NewRole'] : false );
						if ( $username ) {
							$body .= '<tr>';
							$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has changed the role of the user <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $username ) . '</span> with the role <span style="display: inline-block; color: #149247;">' . $userrole . '</span>';
							$body .= '</td>';
							$body .= '</tr>';
						}
					} elseif ( 4007 === $event['alert_id'] ) {
						$user_data = ( ( isset( $event['meta_values']['TargetUserData'] ) ) ? $event['meta_values']['TargetUserData'] : false );
						if ( $user_data ) {
							$body .= '<tr>';
							$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has deleted the user <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $user_data->Username ) . '</span> with the role <span style="display: inline-block; color: #149247;">' . $user_data->Roles . '</span>';
							$body .= '</td>';
							$body .= '</tr>';
						}
					}
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- User Profile Changes End -->';
			}

			// Multisite activity.
			if ( ! empty( $multisite_activity ) ) {
				$body .= '<!-- Multisite Network Activity Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Multisite Network Activity</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px;">Below is a list of important events that occurred on your multisite network:</td></tr>';

				foreach ( $multisite_activity as $event ) {
					$sitename = ( ( isset( $event['meta_values']['SiteName'] ) ) ? $event['meta_values']['SiteName'] : false );
					if ( 7000 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has added site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 7001 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has archived site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 7002 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has unarchived site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 7003 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has activated site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 7004 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has deactivated site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 7005 === $event['alert_id'] ) {
						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> has deleted site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 4010 === $event['alert_id'] ) {
						$username = ( ( isset( $event['meta_values']['TargetUsername'] ) ) ? $event['meta_values']['TargetUsername'] : false );
						$userrole = ( ( isset( $event['meta_values']['TargetUserRole'] ) ) ? $event['meta_values']['TargetUserRole'] : false );

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> added the user <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $username ) . '</span> to the site <span style="display: inline-block; color: #149247;">' . $sitename . '</span> with the role of <span style="display: inline-block; color: #149247;">' . $userrole . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					} elseif ( 4011 === $event['alert_id'] ) {
						$username = ( ( isset( $event['meta_values']['TargetUsername'] ) ) ? $event['meta_values']['TargetUsername'] : false );

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> removed the user <span style="display: inline-block; color: #149247;">' . $this->get_user_for_email( $username ) . '</span> from the site <span style="display: inline-block; color: #149247;">' . $sitename . '</span>';
						$body .= '</td>';
						$body .= '</tr>';
					}
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Multisite Network Activity End -->';
			}

			// Plugin activity.
			if ( ! empty( $plugin_activity ) ) {
				$body .= '<!-- Plugins Activity Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Plugins Activity</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px;">Below is a list of plugin changes that happened on your website:</td></tr>';

				foreach ( $plugin_activity as $event ) {
					$plugin_data = false;
					if ( 5000 === $event['alert_id'] ) {
						$plugin_data = ( ( isset( $event['meta_values']['Plugin'] ) ) ? $event['meta_values']['Plugin'] : false );
					} else {
						$plugin_data = ( ( isset( $event['meta_values']['PluginData'] ) ) ? $event['meta_values']['PluginData'] : false );
					}

					if ( ! $plugin_data ) {
						continue;
					}

					if ( ! ( $plugin_data instanceof stdClass ) || ! property_exists( $plugin_data, 'Name' ) ) {
						continue;
					}

					$body .= '<tr>';
					$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
					if ( 5000 === $event['alert_id'] ) {
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> installed the plugin <span style="display: inline-block; color: #149247;">' . $plugin_data->Name . '</span>';
					} elseif ( 5001 === $event['alert_id'] ) {
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> activated the plugin <span style="display: inline-block; color: #149247;">' . $plugin_data->Name . '</span>';
					} elseif ( 5002 === $event['alert_id'] ) {
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> deactivated the plugin <span style="display: inline-block; color: #149247;">' . $plugin_data->Name . '</span>';
					} elseif ( 5003 === $event['alert_id'] ) {
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> uninstalled the plugin <span style="display: inline-block; color: #149247;">' . $plugin_data->Name . '</span>';
					} elseif ( 5004 === $event['alert_id'] ) {
						$body .= 'User <span style="display: inline-block; color: #149247;">' . ( ! is_null( $event['username'] ) ? $this->get_user_for_email( $event['username'] ) : $this->get_user_name( $event['user_id'] ) ) . '</span> upgraded the plugin <span style="display: inline-block; color: #149247;">' . $plugin_data->Name . '</span>';
					}
					$body .= '</td>';
					$body .= '</tr>';
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Plugins Activity End -->';
			}

			// Content changes.
			if (
				! empty( $posts_published )
				|| ! empty( $posts_trashed )
				|| ! empty( $posts_deleted )
				|| ! empty( $posts_modified )
			) {
				$body .= '<!-- Content Changes Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Title Start --><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: left; padding-bottom: 13px;">Content Changes</td></tr><!-- Title End --><!-- Desc Start --><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0">';

				if ( ! empty( $posts_published ) ) {
					$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px;">These posts were published:</td></tr>';

					foreach ( $posts_published as $post_event ) {
						$post_title = ( ( isset( $post_event['meta_values']['PostTitle'] ) ) ? $post_event['meta_values']['PostTitle'] : false );
						$post_id    = ( ( isset( $post_event['meta_values']['PostID'] ) ) ? $post_event['meta_values']['PostID'] : false );
						if ( ! $post_title || ! $post_id ) {
							continue;
						}

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						if ( WpSecurityAuditLog::is_multisite() ) {
							$site_url = ( ( isset( $post_event['meta_values']['SiteURL'] ) ) ? $post_event['meta_values']['SiteURL'] : $safe_url );
							$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . ' on site <span style="display: inline-block; color: #149247;">' . $site_url . '</span>';
						} else {
							$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span>';
						}
						$body .= '</td>';
						$body .= '</tr>';
					}
				}

				if ( ! empty( $posts_trashed ) || ! empty( $posts_deleted ) ) {
					$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px; padding-top: 20px;">These posts were moved to trash or permanently deleted:</td></tr>';

					if ( ! empty( $posts_trashed ) ) {
						foreach ( $posts_trashed as $post_event ) {
							$post_title = ( ( isset( $post_event['meta_values']['PostTitle'] ) ) ? $post_event['meta_values']['PostTitle'] : false );
							$post_id    = ( ( isset( $post_event['meta_values']['PostID'] ) ) ? $post_event['meta_values']['PostID'] : false );
							if ( ! $post_title || ! $post_id ) {
								continue;
							}

							$body .= '<tr>';
							$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							if ( WpSecurityAuditLog::is_multisite() ) {
								$site_url = ( ( isset( $post_event['meta_values']['SiteURL'] ) ) ? $post_event['meta_values']['SiteURL'] : $safe_url );
								/* Translators: 1. Post Title 2. Username 3. Site URL */
								$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> sent to trash by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span> on site <span style="display: inline-block; color: #149247;">' . $site_url . '</span>';
							} else {
								/* Translators: 1. Post Title 2. Username */
								$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> sent to trash by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span>';
							}
							$body .= '</td>';
							$body .= '</tr>';
						}
					}

					if ( ! empty( $posts_deleted ) ) {
						foreach ( $posts_deleted as $post_event ) {
							$post_title = ( ( isset( $post_event['meta_values']['PostTitle'] ) ) ? $post_event['meta_values']['PostTitle'] : false );
							$post_id    = ( ( isset( $post_event['meta_values']['PostID'] ) ) ? $post_event['meta_values']['PostID'] : false );
							if ( ! $post_title || ! $post_id ) {
								continue;
							}

							$body .= '<tr>';
							$body .= '<td style="border-bottom: 1px solid #b2b2ad; border-left: 1px solid #b2b2ad; border-right: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
							if ( WpSecurityAuditLog::is_multisite() ) {
								$site_url = ( ( isset( $post_event['meta_values']['SiteURL'] ) ) ? $post_event['meta_values']['SiteURL'] : $safe_url );
								/* Translators: 1. Post Title 2. Username 3. Site URL */
								$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> deleted permanently by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span> on site <span style="display: inline-block; color: #149247;">' . $site_url . '</span>';
							} else {
								/* Translators: 1. Post Title 2. Username */
								$body .= '<a style="display: inline-block;" href="' . get_permalink( $post_id ) . '" target="_blank">' . $post_title . '</a> deleted permanently by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span>';
							}
							$body .= '</td>';
							$body .= '</tr>';
						}
					}
				}

				if ( ! empty( $posts_modified ) ) {
					$body .= '<tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;padding-bottom: 20px; padding-top: 20px;">The content of these posts was changed:</td></tr>';

					foreach ( $posts_modified as $post_event ) {
						$post_title = ( ( isset( $post_event['meta_values']['PostTitle'] ) ) ? $post_event['meta_values']['PostTitle'] : false );
						if ( ! $post_title ) {
							continue;
						}

						$body .= '<tr>';
						$body .= '<td style="border: 1px solid #b2b2ad; font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;">';
						if ( WpSecurityAuditLog::is_multisite() ) {
							$site_url = ( ( isset( $post_event['meta_values']['SiteURL'] ) ) ? $post_event['meta_values']['SiteURL'] : $safe_url );
							$body .= '<span style="display: inline-block; color: #149247;">' . $post_title . '</span> by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span> on site <span style="display: inline-block; color: #149247;">' . $site_url . '</span>';
						} else {
							$body .= '<span style="display: inline-block; color: #149247;">' . $post_title . '</span> by <span style="display: inline-block; color: #149247;">' . ( ! is_null( $post_event['username'] ) ? $this->get_user_for_email( $post_event['username'] ) : $this->get_user_name( $post_event['user_id'] ) ) . '</span>';
						}
						$body .= '</td>';
						$body .= '</tr>';
					}
				}

				$body .= '</table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Content Changes End -->';
			}

			$body = apply_filters( 'wsal_append_dailynotification_email_content', $body, $events );

		}

		// Total events.
		$body .= '<!-- Total Events Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0"><!-- Table Border Start --><tr><td style="padding-bottom: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040;">Yesterday the WP Activity Log plugin logged ' . $total_events . ' events in the WordPress activity log. <a href="' . add_query_arg( 'page', 'wsal-auditlog', admin_url( 'admin.php' ) ) . '" target="_blank" style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #149247;">Visit the activity log</a></td></tr></table></td></tr><!-- Table Border Start --><!-- Desc End --></table></td></tr><!-- Total Events End -->';

		// Close content table.
		$body .= '</table></td></tr><!-- Mailer Content End -->';

		// CTA.
		$body .= '<!-- Cta Start --><tr><td><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eeede8;"><tbody><tr><td style="padding-top: 44px; padding-bottom: 30px; margin: 0 auto; "><table width="100%" cellpadding="0" cellspacing="0" border="0"><tbody><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 20px; line-height: 28px; color: #404040; text-align: center; padding-bottom: 15px;">Help & Support</td></tr><tr><td style="text-align: center;"><table align="center" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;"><tbody><tr><td style="text-align: center; padding-top: 20px; width: 285px; display: inline-block;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;"><tbody><tr><td><img src="' . $this->media['documentation'] . '" alt="Documentation"></td></tr><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 16px; line-height: 26px; color: #404040; text-align: center; padding-bottom: 10px; padding-top: 7px;">Documentation</td></tr><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; text-align: center; padding-bottom: 15px;">Refer to our <a href="https://wpactivitylog.com/support/kb/" style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #149247; text-decoration: underline; display: inline-block;" target="_blank">knowledge base</a> for plugin documentation</td></tr></tbody></table></td><td style="text-align: center; padding-top: 20px; width: 285px; display: inline-block;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;"><tbody><tr><td><img src="' . $this->media['get-support'] . '" alt="Get Support"></td></tr><tr><td style="font-family: Verdana, sans-serif; font-weight: bold; font-size: 16px; line-height: 26px; color: #404040; text-align: center; padding-bottom: 10px; padding-top: 7px;">Get Support</td></tr><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #404040; text-align: center; padding-bottom: 15px;">Need help? Email us on<a href="mailto:info@wpwhitesecurity.com"  style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 16px; line-height: 28px; color: #149247; text-decoration: underline; display: inline-block;"> info@wpwhitesecurity.com</a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><!-- Cta End -->';

		$body .= '</tbody></table></td><td width="15" style="background-image: url(' . $this->media['box-shadow-right'] . ');     background-repeat: repeat-y;"></td></tr><tr><td colspan="3" height="17" style="background-image: url(' . $this->media['box-shadow-bottom'] . '); height: 17px; background-repeat: repeat-x;"></td></tr></tbody></table></div></td></tr><tr><td align="center" style="padding-left: 40px; padding-right: 40px;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 520px; margin: 0 auto;"><tr><td style="text-align: center; padding-top: 35px; padding-bottom: 20px;"><a href="#" style="display: inline-block; text-decoration: none;"><img src="' . $this->media['logo'] . '"></a></td></tr><tr><td style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 14px; line-height: 28px; color: #404040; padding-bottom: 40px; text-align: center;">This email is generated by WP Activity Log. To disable this daily overview navigate to the <a href="' . add_query_arg( 'page', 'wsal-np-notifications', admin_url( 'admin.php' ) ) . '#tab-built-in" target="_blank" style="font-family: Verdana, sans-serif; font-weight: normal; font-size: 14px; line-height: 28px; color: #149247;">email notifications settings</a></td></tr></table></td></tr></table>';

		return $body;
	}
}
