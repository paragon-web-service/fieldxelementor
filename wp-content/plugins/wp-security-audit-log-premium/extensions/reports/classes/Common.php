<?php
/**
 * Reports Utility Class
 *
 * Provides utility methods to generate reports.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage reports
 */

use WSAL\Helpers\File_Helper;
use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Class WSAL_Rep_Common
 * Provides utility methods to generate reports.
 *
 * @package wsal
 * @subpackage reports
 */
class WSAL_Rep_Common {

	// Periodic report frequencies.
	const REPORT_DAILY = 'Daily';

	const REPORT_WEEKLY = 'Weekly';

	const REPORT_MONTHLY = 'Monthly';

	const REPORT_QUARTERLY = 'Quarterly';

	const WSAL_PR_PREFIX = 'periodic-report-';

	// Statistics reports criteria.
	const LOGIN_ALL = 10;

	const LOGIN_BY_USER = 1;

	const LOGIN_BY_ROLE = 2;

	/**
	 * Profile changes reports criteria
	 */
	const PROFILE_CHANGES_ALL = 70;

	const PROFILE_CHANGES_BY_USER = 71;

	const PROFILE_CHANGES_BY_ROLE = 72;

	const VIEWS_ALL = 20;

	const VIEWS_BY_USER = 3;

	const VIEWS_BY_ROLE = 4;

	const VIEWS_BY_POST = 25;

	const PUBLISHED_ALL = 30;

	const PUBLISHED_BY_USER = 5;

	const PUBLISHED_BY_ROLE = 6;

	const DIFFERENT_IP = 7;

	const ALL_IPS = 40;

	const ALL_USERS = 50;

	const PASSWORD_CHANGES = 60;

	const NEW_USERS = 75;

	/**
	 * Name of cron job for sending the periodic reports.
	 */
	const SCHEDULED_HOOK_SUMMARY_EMAILS = 'wsal_summary_email_reports';

	/**
	 * Name of cron job for deleting stale reports.
	 */
	const SCHEDULED_HOOK_REPORTS_PRUNING = 'wsal_reports_pruning';

	/**
	 * Option name storing a list of manually generated reports.
	 *
	 * @var string
	 */
	const SAVED_REPORTS_OPTION_NAME = 'generated_reports';

	/**
	 * Is multisite?
	 *
	 * @var boolean
	 */
	private static $is_multisite = false;

	/**
	 * Frequency daily hour
	 * For testing change hour here [0 to 23]
	 *
	 * @var int
	 */
	private static $daily_hour = 8;

	/**
	 * Frequency monthly date
	 * For testing change date here [01 to 31]
	 *
	 * @var string
	 */
	private static $monthly_day = '01';

	/**
	 * Frequency weekly date
	 * For testing change date here [1 (for Monday) through 7 (for Sunday)]
	 *
	 * @var string
	 */
	private static $weekly_day = '1';

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
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $plugin = null;

	/**
	 * Instance of WSAL_Models_Occurrence.
	 *
	 * @var WSAL_Models_Occurrence
	 */
	protected $occurrence = null;

	/**
	 * Instance of WSAL_Models_Meta.
	 *
	 * @var WSAL_Models_Meta
	 */
	protected $meta = null;

	/**
	 * Sanitized date format. Used for filter and datepicker dates.
	 *
	 * @var string
	 */
	protected $date_format = null;

	/**
	 * Time format.
	 *
	 * @var string
	 */
	protected $time_format = null;

	/**
	 * Reports directory path.
	 *
	 * @var string
	 * @see check_directory()
	 */
	protected $reports_dir_path = null;

	/**
	 * Attachments.
	 *
	 * @var null
	 */
	protected $attachments = null;

	/**
	 * Holds the alert groups
	 *
	 * @var array
	 */
	private $alert_groups = array();

	/**
	 * Errors array.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin Plugin.
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		$this->plugin     = $plugin;
		$this->occurrence = new WSAL_Models_Occurrence();
		$this->meta       = new WSAL_Models_Meta();

		// Get DateTime Format from WordPress General Settings.
		$this->date_format = $this->plugin->settings()->get_date_format( true );
		$this->time_format = $this->plugin->settings()->get_time_format( true );

		self::$daily_hour   = $this->plugin->settings()->get_periodic_reports_hour_of_day();
		self::$is_multisite = WpSecurityAuditLog::is_multisite();

		// Cron job for sending periodic reports.
		add_action( self::SCHEDULED_HOOK_SUMMARY_EMAILS, array( $this, 'send_pending_periodic_reports' ) );
		if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_SUMMARY_EMAILS ) ) {
			wp_schedule_event( time(), 'hourly', self::SCHEDULED_HOOK_SUMMARY_EMAILS );
		}

		// Cron job for deleting stale reports.
		if ( $this->plugin->settings()->is_report_pruning_enabled() ) {
			add_action( self::SCHEDULED_HOOK_REPORTS_PRUNING, array( $this, 'delete_stale_reports' ) );
			if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_REPORTS_PRUNING ) ) {
				wp_schedule_event( time(), 'daily', self::SCHEDULED_HOOK_REPORTS_PRUNING );
			}
		}

		// Set paths.
		$this->base_dir = WSAL_BASE_DIR . 'extensions/reports';
		$this->base_url = WSAL_BASE_URL . 'extensions/reports';

		add_action( 'user_register', array( $this, 'reset_users_counter' ) );
	}

	/**
	 * Method: Return Sites.
	 *
	 * @param int|null $limit Maximum number of sites to return (null = no limit).
	 *
	 * @return object Object with keys: blog_id, blogname, domain
	 */
	final public static function get_sites( $limit = null ) {
		global $wpdb;
		if ( self::$is_multisite ) {
			$sql = 'SELECT blog_id, domain FROM ' . $wpdb->blogs;
			if ( ! is_null( $limit ) ) {
				$sql .= ' LIMIT ' . $limit;
			}
			$res = $wpdb->get_results( $sql ); // phpcs:ignore
			foreach ( $res as $row ) {
				$row->blogname = get_blog_option( $row->blog_id, 'blogname' );
			}
		} else {
			$res           = new stdClass();
			$res->blog_id  = get_current_blog_id();
			$res->blogname = esc_html( get_bloginfo( 'name' ) );
			$res           = array( $res );
		}

		return $res;
	}

	/**
	 * Method: Get site users.
	 *
	 * @param int|null $limit Maximum number of sites to return (null = no limit).
	 */
	final public static function get_users( $limit = null ) {
		global $wpdb;
		$t   = $wpdb->users;
		$sql = "SELECT ID, user_login FROM {$t}";
		if ( ! is_null( $limit ) ) {
			$sql .= ' LIMIT ' . $limit;
		}

		return $wpdb->get_results( $sql ); // phpcs:ignore
	}

	/**
	 * Get distinct values of IPs.
	 *
	 * @param int $limit - (Optional) Limit.
	 *
	 * @return array distinct values of IPs
	 */
	final public static function get_ip_addresses( $limit = null ) {
		$tmp = new WSAL_Models_Occurrence();

		return $tmp->get_adapter()->get_matching_ips( $limit );
	}

	/**
	 * Generates a report filename in format "YYYYMMDD-{01234567}".
	 *
	 * @return string
	 */
	public static function generate_report_filename() {
		$random_number = wp_rand( 1, 99999999 );
		$number_padded = str_pad( $random_number, 8, '0', STR_PAD_LEFT );

		return date( 'Ymd' ) . '-' . str_shuffle( $number_padded ); // @codingStandardsIgnoreLine
	}

	/**
	 * Generates a filename for the periodic report in format "YYYYMMDD-{report name}-{period}". For example:
	 * - 20190430-Joes-Lists-Weekly-32
	 * - 20190430-Joes Lists Updated-Monthly-11
	 *
	 * @param string $attach_key Report name.
	 * @param string $frequency  Report frequency.
	 *
	 * @return string
	 *
	 * @since 4.4.0
	 */
	private function generate_periodic_report_filename( $attach_key, $frequency ) {
		return date( 'Ymd' ) . '-' . self::secure_filename( $attach_key ) . '-' . $frequency . '-' . str_replace( ' ', '-', $this->get_schedule_number( $frequency ) ); // @codingStandardsIgnoreLine
	}

	/**
	 * Delete the setting by name.
	 *
	 * @param string $option - Option name.
	 *
	 * @return boolean result
	 */
	public function delete_global_setting( $option ) {
		$this->delete_cache_notif();

		return Settings_Helper::delete_option_value( $option );
	}

	/**
	 * Delete cache.
	 */
	public function delete_cache_notif() {
		if ( function_exists( 'wp_cache_delete' ) ) {
			wp_cache_delete( WSAL_CACHE_KEY_2 );
		}
	}

	/**
	 * Retrieve list of role names.
	 *
	 * @return array List of role names.
	 */
	public function get_roles() {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles(); // phpcs:ignore
		}

		return $wp_roles->get_names();
	}

	/**
	 * Retrieve the information about the current blog.
	 *
	 * @return mixed
	 */
	final public static function get_current_blog_info() {
		global $wpdb;
		$blog_id            = get_current_blog_id();
		$blog_data          = new stdClass();
		$blog_data->blog_id = $blog_id;

		if ( is_multisite() ) {
			$blog_data->blogname = get_blog_option( $blog_id, 'blogname' );
			$blog_data->domain   = $wpdb->get_var( 'SELECT domain FROM ' . $wpdb->blogs . ' WHERE blog_id=' . $blog_id ); // phpcs:ignore
		}

		return $blog_data;
	}

	/**
	 * Reset users count transient.
	 */
	public function reset_users_counter() {
		$is_multisite = WpSecurityAuditLog::is_multisite(); // Check for multisite.
		$blog_info    = self::get_current_blog_info();

		// Delete transient.
		$count_transient = $is_multisite ? 'wsal_users_count_' . $blog_info->blog_id : 'wsal_users_count';
		$users_count     = WpSecurityAuditLog::get_transient( $count_transient );

		// If value exists then delete it.
		if ( false !== $users_count ) {
			WpSecurityAuditLog::delete_transient( $count_transient );
		}
	}

	/**
	 * Get alerts code.
	 *
	 * @return array
	 */
	final public function get_alert_codes() {
		$data = $this->plugin->alerts->get_alerts();
		$keys = array();
		if ( ! empty( $data ) ) {
			$keys = array_keys( $data );
			$keys = array_map( array( $this, 'pad_key' ), $keys );
		}

		return $keys;
	}

	/**
	 * Check to see whether the specified directory is accessible.
	 *
	 * @param string $dir_path - Directory Path.
	 *
	 * @return bool
	 */
	final public function check_directory( $dir_path ) {
		if ( ! is_dir( $dir_path ) ) {
			return false;
		}
		if ( ! is_readable( $dir_path ) ) {
			return false;
		}
		if ( ! is_writable( $dir_path ) ) {
			return false;
		}
		// Create the index.php file if not already there.
		File_Helper::create_index_file( $dir_path );
		$this->reports_dir_path = $dir_path;

		return true;
	}

	/**
	 * Create an index.php file, if none exists, in order to avoid directory listing in the specified directory
	 *
	 * @param string $dir_path - Directory Path.
	 *
	 * @return bool
	 *
	 * @deprecated 4.4.3 - Use \WSAL\Helpers\File_Helper::create_index_file()
	 */
	final public function create_index_file( $dir_path ) {

		_deprecated_function( __FUNCTION__, '4.4.3', '\WSAL\Helpers\File_Helper::create_index_file()' );

		return \WSAL\Helpers\File_Helper::create_index_file( $dir_path );
	}

	/**
	 * Checks if there are any errors.
	 *
	 * @return bool True if there are some errors.
	 */
	final public function has_errors() {
		return ( ! empty( $this->errors ) );
	}

	/**
	 * Gets the errors.
	 *
	 * @return array Errors.
	 */
	final public function get_errors() {
		return $this->errors;
	}

	/**
	 * Erases reports older than configured number of days.
	 */
	public function delete_stale_reports() {
		$reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports', true );
		if ( file_exists( $reports_dir_path ) ) {
			if ( $handle = opendir( $reports_dir_path ) ) { // phpcs:ignore
				$threshold = $this->plugin->settings()->get_report_pruning_threshold();
				while ( false !== ( $entry = readdir( $handle ) ) ) { // phpcs:ignore
					if ( '.' !== $entry && '..' !== $entry ) {
						// We handle legacy report file naming as well as naming introduced in version 4.4.0.
						$filename = explode( '-', str_replace( 'wsal_report_', '', $entry ) );
						if ( ! empty( $filename ) && preg_match( '/\d{8}/', $filename[0] ) ) {
							if ( $filename[0] <= date( 'Ydm', strtotime( '-' . $threshold . ' day' ) ) ) { // phpcs:ignore
								$this->delete_saved_report( $entry );
							}
						}
					}
				}
				closedir( $handle );
			}
		}
	}

	/**
	 * Sends pending periodic report as a cron task.
	 */
	public function send_pending_periodic_reports() {
		$periodic_reports = $this->get_periodic_reports();
		if ( ! empty( $periodic_reports ) ) {
			foreach ( $periodic_reports as $report ) {
				$frequency = $report->frequency;
				if ( $this->can_period_report_be_sent( $frequency ) ) {
					$this->send_single_report( $report );
				}
			}
		}
	}

	/**
	 * Get an array with all the Configured Periodic Reports.
	 */
	public function get_periodic_reports() {
		$result  = array();
		$reports = $this->plugin->get_notifications_setting( self::WSAL_PR_PREFIX );
		if ( ! empty( $reports ) ) {
			foreach ( $reports as $report ) {
				$result[ $report->option_name ] = $this->patch_legacy_report_object( unserialize( $report->option_value ) ); // @codingStandardsIgnoreLine
			}
		}

		return $result;
	}

	/**
	 * Gets a periodic report. It also converts legacy filters to the latest format.
	 *
	 * @param string $report_name Report name.
	 *
	 * @since 4.4.0
	 */
	public function get_periodic_report( $report_name ) {
		$report = $this->get_setting_by_name( $report_name );

		return $this->patch_legacy_report_object( $report );
	}

	/**
	 * Patches object with legacy properties to comply with the latest format.
	 *
	 * @param stdClass $report Report object.
	 *
	 * @return stdClass
	 *
	 * @since 4.4.0
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	 */
	private function patch_legacy_report_object( $report ) {
		if ( property_exists( $report, 'viewState' ) && property_exists( $report, 'triggers' ) ) {
			if ( in_array( 'codes', $report->viewState, true ) ) { // phpcs:ignore
				// Specific event IDs were selected.
				$index             = array_search( 'codes', $report->viewState, true ); // phpcs:ignore
				$codes             = $report->triggers[ $index ]['alert_id'];
				$report->alert_ids = $codes;
			} elseif ( count( $report->viewState ) < 20 ) { // phpcs:ignore
				// Specific groups were selected.
				$report->alert_ids = $report->viewState; // phpcs:ignore
			}
		}

		return $report;
	}

	/**
	 * Check if the periodic report with given frequency can be sent at the moment.
	 *
	 * @param string $frequency - Frequency.
	 *
	 * @return bool True if report can be sent. False otherwise.
	 */
	private function can_period_report_be_sent( $frequency ) {
		$send = false;
		switch ( $frequency ) {
			case self::REPORT_DAILY:
				$send = self::$daily_hour === $this->calculate_daily_hour();
				break;
			case self::REPORT_WEEKLY:
				$weekly_day = $this->calculate_weekly_day();
				if ( ! empty( $weekly_day ) ) {
					$send = $weekly_day === self::$weekly_day;
				}
				break;
			case self::REPORT_MONTHLY:
				$str_date = $this->calculate_monthly_day();
				if ( ! empty( $str_date ) ) {
					$send = date( 'Y-m-d' ) == $str_date; // phpcs:ignore
				}
				break;
			case self::REPORT_QUARTERLY:
				$send = $this->check_quarter();
				break;
			default:
				// Fallback for any other frequencies would go here.
				break;
		}

		return $send;
	}

	/**
	 * Method: Calculate and return hour of the day
	 * based on WordPress timezone.
	 *
	 * @return int Hour of the day.
	 * @since 2.1.1
	 */
	private function calculate_daily_hour() {
		return (int) date( 'H', time() + ( get_option( 'gmt_offset' ) * ( 60 * 60 ) ) ); // phpcs:ignore
	}

	/**
	 * Method: Calculate and return day of the week
	 * based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 * @since 2.1.1
	 */
	private function calculate_weekly_day() {
		if ( self::$daily_hour === $this->calculate_daily_hour() ) {
			return date( 'w' ); // phpcs:ignore
		}

		return false;
	}

	/**
	 * Method: Calculate and return day of the month
	 * based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 * @since 2.1.1
	 */
	private function calculate_monthly_day() {
		if ( self::$daily_hour === $this->calculate_daily_hour() ) {
			return date( 'Y-m-' ) . self::$monthly_day; // phpcs:ignore
		}

		return false;
	}

	/**
	 * Check Quarter of the year
	 * in the cron job.
	 *
	 * @return bool true|false
	 *
	 * phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
	 */
	private function check_quarter() {
		$hour  = gmdate( 'H', time() + ( \get_option( 'gmt_offset' ) * ( 60 * 60 ) ) );
		$month = gmdate( 'n', time() + ( \get_option( 'gmt_offset' ) * ( 60 * 60 ) ) );
		$day   = gmdate( 'j', time() + ( \get_option( 'gmt_offset' ) ) * ( 60 * 60 ) );
		if ( '1' == $day && self::$daily_hour === $hour ) { // phpcs:ignore
			switch ( $month ) {
				case '1':
				case '4':
				case '7':
				case '10':
					return true;
				default:
					return false;
			}
		}

		return false;
	}

	/**
	 * Get the setting by name.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $default - Default option value.
	 *
	 * @return mixed value
	 */
	public function get_setting_by_name( $option, $default = false ) {
		return Settings_Helper::get_option_value( $option, $default );
	}

	/**
	 * Get Quarter of the year.
	 *
	 * @return string N. quarter
	 */
	private function which_quarter() {
		$month = gmdate( 'n', time() );
		if ( $month >= 1 && $month <= 3 ) {
			return 'Q1';
		} elseif ( $month >= 4 && $month <= 6 ) {
			return 'Q2';
		} elseif ( $month >= 7 && $month <= 9 ) {
			return 'Q3';
		} elseif ( $month >= 10 && $month <= 12 ) {
			return 'Q4';
		}
	}

	/**
	 * Create the report appending in a json file.
	 *
	 * @param object $report    Report object.
	 * @param float  $next_date Timestamp for event offsetting.
	 * @param int    $limit     Limits the number of events to include.
	 *
	 * @return string|null Timestamp for the next batch. Null in case of failure or missing events.
	 */
	public function build_attachment( $report, $next_date, $limit ) {

		$attach_key = $report->title;
		@ini_set( 'max_execution_time', '300' ); // phpcs:ignore

		$last_date = null;
		$result    = $this->get_list_events( $report, $next_date, $limit );
		if ( false === $result ) {
			// Stop if there are no events for the report.
			return null;
		}

		if ( ! empty( $result['lastDate'] ) ) {
			$last_date = $result['lastDate'];
		}

		$filename = $this->reports_dir_path . $this->generate_periodic_report_filename( $attach_key, $report->frequency ) . '.json';
		if ( file_exists( $filename ) ) {
			$data = json_decode( file_get_contents( $filename ), true ); // phpcs:ignore
			if ( ! empty( $data ) ) {
				if ( ! empty( $result ) ) {
					$todays_date = gmdate( 'm-d-Y', time() );
					foreach ( $result['data'] as $value ) {
						// first 10 chars in value are in format: 'mm-dd-YYYY'
						// NOTE: that is same as date( 'm-d-Y' ) formatted.
						$item_date = substr( $value['date'], 0, 10 );
						// we only want items from BEFORE today.
						if ( $todays_date !== $item_date ) {
							array_push( $data['data'], $value );
						}
					}
				}

				$data['lastDate'] = $last_date;
				file_put_contents( $filename, json_encode( $data ) ); // phpcs:ignore
			}
		} else {
			if ( ! empty( $result ) ) {
				file_put_contents( $filename, json_encode( $result ) ); // phpcs:ignore
			}
		}

		return $last_date;
	}

	/**
	 * Internal function that converts the arbitrary report object into an array that can be digested by the function
	 * WSAL_ReportArgs::build_from_extension_filters.
	 *
	 * @param object $report Report object.
	 *
	 * @return array
	 */
	private function convert_report_object_to_filters_array( $report ) {

		$filters = array();

		// Fields we can loop.
		$possible_filters = self::get_possible_filters();

		$possible_filters['custom_title'] = array(
			'property_name' => 'custom_title',
		);

		$possible_filters['comment'] = array(
			'property_name' => 'comment',
		);

		$missed = array();
		foreach ( $possible_filters as $filter_key => $filter_definition ) {
			$report_property = $filter_definition['property_name'];
			if ( property_exists( $report, $report_property ) ) {
				if ( strpos( $filter_key, '|' ) !== false ) {
					$array_indexes = explode( '|', $filter_key, 2 );
					if ( 2 === count( $array_indexes ) ) {
						$filters[ $array_indexes[0] ][ $array_indexes[1] ] = $report->$report_property;
					}
				} else {
					$filters[ $filter_key ] = $report->$report_property;
				}
			} else {
				array_push( $missed, $filter_key );
			}
		}

		$filters['date_range']['end'] = ( isset( $end_date ) ) ? $end_date : date( $this->date_format, strtotime( 'yesterday' ) );
		$filters['report_format']     = $report->type;

		return $filters;
	}

	/**
	 * Generate the file of the report (HTML or CSV).
	 *
	 * @param object $report    Report object.
	 * @param float  $next_date Next date for offsetting the data.
	 * @param int    $limit     Query limit.
	 *
	 * @return string|bool Event list in case of success. False otherwise.
	 */
	private function get_list_events( $report, $next_date, $limit ) {

		$filters = $this->convert_report_object_to_filters_array( $report );

		$yesterday = gmdate( $this->date_format, strtotime( 'yesterday' ) );
		$frequency = $report->frequency;
		switch ( $frequency ) {
			case self::REPORT_DAILY:
				// get YESTERDAYS date.
				$start_date = $yesterday;
				break;
			case self::REPORT_WEEKLY:
				$start_date = gmdate( $this->date_format, strtotime( 'last week' ) );
				$end_date   = gmdate( $this->date_format, strtotime( 'last week + 6 days' ) );
				break;
			case self::REPORT_MONTHLY:
				$start_date = gmdate( $this->date_format, strtotime( 'last month' ) );
				$end_date   = gmdate( $this->date_format, strtotime( 'this month - 1 day' ) );
				break;
			case self::REPORT_QUARTERLY:
				$start_date = $this->start_quarter();
				break;
			default:
				// Fallback for any other time period would go here.
				break;
		}

		$filters['date_range']['start'] = $start_date;
		$filters['date_range']['end']   = ( isset( $end_date ) ) ? $end_date : $yesterday;
		$filters['report_format']       = $report->type;
		$filters['limit']               = $limit;
		$filters['nextDate']            = $next_date;

		$this->reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports' );
		return $this->generate_report( $filters, false );
	}

	/**
	 * Get Start Quarter of the year.
	 *
	 * @return string $start_date
	 */
	private function start_quarter() {
		$month = gmdate( 'n', time() );
		$year  = gmdate( 'Y', time() );
		if ( $month >= 1 && $month <= 3 ) {
			$start_date = gmdate( $this->date_format, strtotime( $year . '-01-01' ) );
		} elseif ( $month >= 4 && $month <= 6 ) {
			$start_date = gmdate( $this->date_format, strtotime( $year . '-04-01' ) );
		} elseif ( $month >= 7 && $month <= 9 ) {
			$start_date = gmdate( $this->date_format, strtotime( $year . '-07-01' ) );
		} elseif ( $month >= 10 && $month <= 12 ) {
			$start_date = gmdate( $this->date_format, strtotime( $year . '-10-01' ) );
		}

		return $start_date;
	}

	/**
	 * Generate report matching the filter passed.
	 *
	 * @param array $filters  Filters.
	 * @param bool  $validate (Optional) Validation.
	 *
	 * @return array|false $dataAndFilters Returns report data and filter in case of success. Otherwise false is returned.
	 */
	public function generate_report( array $filters, $validate = true ) {

		// Fields we can loop.
		$possible_filters = array(
			'sites',
			'sites-exclude',
			'users',
			'users-exclude',
			'roles',
			'roles-exclude',
			'ip-addresses',
			'ip-addresses-exclude',
			'alert_codes',
			'alert_codes|groups',
			'alert_codes|groups-exclude',
			'alert_codes|alerts',
			'alert_codes|alerts-exclude',
			'alert_codes|post_types',
			'alert_codes|post_types-exclude',
			'alert_codes|post_statuses',
			'alert_codes|post_statuses-exclude',
			'date_range',
			'date_range|start',
			'date_range|end',
			'report_format',
			'objects',
			'objects-exclude',
			'event-types',
			'event-types-exclude',
			'post_ids',
			'post_ids-exclude',
			'post_types',
			'post_types-exclude',
			'post_statuses',
			'post_statuses-exclude',
		);

		// Arguments we will fill in.
		$actual_filters = array();

		// Validate if requested.
		if ( $validate ) {
			foreach ( $possible_filters as $filter ) {
				if ( strpos( $filter, '|' ) !== false ) {
					$array_indexes = explode( '|', $filter, 2 );
					if ( ! isset( $filters[ $array_indexes[0] ][ $array_indexes[1] ] ) ) {
						$this->add_error( sprintf( esc_html__( 'Internal error. <code>%s</code> key was not found.', 'wp-security-audit-log' ), $filter ) ); // phpcs:ignore

						return false;
					}
				} else {
					if ( ! isset( $filters[ $filter ] ) ) {
						$this->add_error( sprintf( esc_html__( 'Internal error. <code>%s</code> key was not found.', 'wp-security-audit-log' ), $filter ) ); // phpcs:ignore

						return false;
					}
				}
			}
		}

		// Create filters based on possible fields.
		foreach ( $possible_filters as $filter ) {
			if ( strpos( $filter, '|' ) !== false ) {
				$array_indexes                        = explode( '|', $filter, 2 );
				$value                                = ( isset( $filters[ $array_indexes[0] ][ $array_indexes[1] ] ) && ! empty( $filters[ $array_indexes[0] ][ $array_indexes[1] ] ) ) ? $filters[ $array_indexes[0] ][ $array_indexes[1] ] : null;
				$tidy_filter_index                    = str_replace( '|', '_', $filter );
				$actual_filters[ $tidy_filter_index ] = $value;
			} else {
				$value2                    = ( isset( $filters[ $filter ] ) && ! empty( $filters[ $filter ] ) ) ? $filters[ $filter ] : null;
				$actual_filters[ $filter ] = $value2;
			}
		}

		// Filters.
		$report_format = empty( $filters['report_format'] ) ? WSAL_Rep_DataFormat::get_default() : intval( $filters['report_format'] );
		if ( ! WSAL_Rep_DataFormat::is_valid( $report_format ) ) {
			$this->add_error( esc_html__( 'Internal Error: Could not detect the type of the report to generate.', 'wp-security-audit-log' ) );

			return false;
		}

		$args = WSAL_ReportArgs::build_from_extension_filters( $actual_filters, $this );

		$next_date = ( empty( $filters['nextDate'] ) ? null : $filters['nextDate'] );
		$limit     = ( empty( $filters['limit'] ) ? WSAL_Rep_Views_Main::REPORT_LIMIT : $filters['limit'] );
		$limit     = apply_filters( 'wsal_reporting_query_limit', $limit );

		$report_type     = array_key_exists( 'type_statistics', $filters ) ? intval( $filters['type_statistics'] ): null;
		$grouping_period = array_key_exists( 'grouping_period', $filters ) ? $filters['grouping_period'] : null;

		if ( ! empty( $filters['unique_ip'] ) ) {
			// This is only covering the last of the statistics report - Different IP addresses for Usernames.
			$results = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_ip_address_report_data( $args, 0, $report_type, $grouping_period );
		} else {
			$results = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_report_data( $args, $next_date, $limit, $report_type, $grouping_period );
		}

		$last_date = null;
		if ( ! empty( $results['lastDate'] ) ) {
			$last_date = $results['lastDate'];
			unset( $results['lastDate'] );
		}

		if ( empty( $results ) ) {
			$this->add_error( esc_html__( 'There are no alerts that match your filtering criteria. Please try a different set of rules.', 'wp-security-audit-log' ) );

			return false;
		}

		$data             = array();
		$data_and_filters = array();
		if ( ! empty( $filters['unique_ip'] ) ) {
			$data = array_values( $results );
		} elseif ( ! is_null( $report_type ) ) {
			$data = $results;
		} else {
			// #! Get Alert details
			foreach ( $results as $i => $entry ) {
				if ( '9999' === $entry->alert_id ) {
					continue;
				}
				array_push( $data, $this->build_alert_details( $report_format, $entry ) );
			}
		}

		if ( empty( $data ) ) {
			$this->add_error( esc_html__( 'There are no alerts that match your filtering criteria. Please try a different set of rules.', 'wp-security-audit-log' ) );

			return false;
		}

		$data_and_filters['data']         = $data;
		$data_and_filters['filters']      = $filters;
		$data_and_filters['lastDate']     = $last_date;
		$data_and_filters['events_found'] = count( $data );

		return $data_and_filters;
	}

	/**
	 * Adds error.
	 *
	 * @param object $error Error object.
	 */
	private function add_error( $error ) {
		array_push( $this->errors, $error );
	}

	/**
	 * If we have alert groups, we need to retrieve all alert codes for those groups and add them to a final alert
	 * of alert codes that will be sent to db in the select query the same goes for individual alert codes.
	 *
	 * @param array $alert_groups List of alert groups.
	 * @param array $alert_codes  List of alert codes.
	 * @param bool  $show_error   Displays error if true and something went wrong.
	 *
	 * @return int[]|false
	 */
	public function get_codes_by_groups( $alert_groups, $alert_codes, $show_error = true ) {
		$_codes           = array();
		$has_alert_groups = ! empty( $alert_groups );
		$has_alert_codes  = ! empty( $alert_codes );
		if ( $has_alert_codes ) {
			// Add the specified alerts to the final array.
			$_codes = $alert_codes;
		}
		if ( $has_alert_groups ) {
			// Get categorized alerts.
			$alerts     = $this->plugin->alerts->get_categorized_alerts();
			$cat_alerts = array();
			foreach ( $alerts as $cname => $group ) {
				$cat_alerts[ $cname ] = array();
				foreach ( $group as $subname => $_entries ) {
					$cat_alerts[ $subname ] = $_entries;
					$cat_alerts[ $cname ]   = array_merge( $cat_alerts[ $cname ], $_entries );
				}
			}
			$this->alert_groups = array_keys( $cat_alerts );
			if ( empty( $cat_alerts ) ) {
				if ( $show_error ) {
					$this->add_error( esc_html__( 'Internal Error. Could not retrieve the alerts from the main plugin.', 'wp-security-audit-log' ) );
				}

				return false;
			}
			// Make sure that all specified alert categories are valid.
			foreach ( $alert_groups as $k => $category ) {
				// get alerts from the category and add them to the final array
				// #! only if the specified category is valid, otherwise skip it.
				if ( isset( $cat_alerts[ $category ] ) ) {
					// If this is the "System Activity" category...some of those alert needs to be padded.
					if ( esc_html__( 'System Activity', 'wp-security-audit-log' ) === $category ) {
						foreach ( $cat_alerts[ $category ] as $i => $alert ) {
							$aid = $alert->code;
							if ( 1 === strlen( $aid ) ) {
								$aid = $this->pad_key( $aid );
							}
							array_push( $_codes, $aid );
						}
					} else {
						foreach ( $cat_alerts[ $category ] as $i => $alert ) {
							array_push( $_codes, $alert->code );
						}
					}
				}
			}
		}
		if ( empty( $_codes ) ) {
			if ( $show_error ) {
				$this->add_error( esc_html__( 'Please specify at least one Alert Group or specify an Alert Code.', 'wp-security-audit-log' ) );
			}

			return false;
		}

		return $_codes;
	}

	/**
	 * Method: Key padding.
	 *
	 * @param string $key - The key to pad.
	 *
	 * @return string
	 * @internal
	 */
	final public function pad_key( $key ) {
		if ( 1 === strlen( $key ) ) {
			$key = str_pad( $key, 4, '0', STR_PAD_LEFT );
		}

		return $key;
	}

	/**
	 * Get alert details.
	 *
	 * @param int    $report_format Report format.
	 * @param object $entry         Raw database entry.
	 *
	 * @return array|false details
	 */
	private function build_alert_details( $report_format, $entry ) {

		$entry_id   = $entry->id;
		$alert_id   = $entry->alert_id;
		$site_id    = $entry->site_id;
		$created_on = $entry->created_on;
		$object     = $entry->object;
		$event_type = $entry->event_type;
		$user_id    = $entry->user_id;

		$ip    = esc_html( $entry->ip );
		$ua    = esc_html( $entry->ua );
		$roles = maybe_unserialize( $entry->roles );
		if ( is_string( $roles ) ) {
			$roles = str_replace( array( '"', '[', ']' ), ' ', $roles );
		}

		// Must be a new instance every time, otherwise the alert message is not retrieved properly.
		$this->occurrence = new WSAL_Models_Occurrence();
		// #! Get alert details
		$alerts_manager = $this->plugin->alerts;
		$code           = $alerts_manager->get_alert( $alert_id );
		$code           = $code ? $code->severity : 0;
		$const          = $this->plugin->constants->get_constant_to_display( $code );

		// Blog details.
		if ( WpSecurityAuditLog::is_multisite() ) {
			$blog_info = get_blog_details( $site_id, true );
			$blog_name = esc_html__( 'Unknown Site', 'wp-security-audit-log' );
			$blog_url  = '';
			if ( $blog_info ) {
				$blog_name = esc_html( $blog_info->blogname );
				$blog_url  = esc_attr( $blog_info->siteurl );
			}
		} else {
			$blog_name = get_bloginfo( 'name' );
			$blog_url  = '';
			if ( empty( $blog_name ) ) {
				$blog_name = esc_html__( 'Unknown Site', 'wp-security-audit-log' );
			} else {
				$blog_name = esc_html( $blog_name );
				$blog_url  = esc_attr( get_bloginfo( 'url' ) );
			}
		}

		// Get the alert message - properly.
		$this->occurrence->id          = $entry_id;
		$this->occurrence->site_id     = $site_id;
		$this->occurrence->alert_id    = $alert_id;
		$this->occurrence->created_on  = $created_on;
		$this->occurrence->client_ip   = $ip;
		$this->occurrence->object      = $object;
		$this->occurrence->event_type  = $event_type;
		$this->occurrence->user_id     = $user_id;
		$this->occurrence->user_agent  = $ua;
		$this->occurrence->post_id     = $entry->post_id;
		$this->occurrence->post_type   = $entry->post_type;
		$this->occurrence->post_status = $entry->post_status;
		$this->occurrence->set_user_roles( $roles );

		if ( ! $this->occurrence->_cached_message ) {
			$this->occurrence->_cached_message = $this->occurrence->get_alert()->mesg;
		}

		if ( empty( $user_id ) ) {
			$username = esc_html__( 'System', 'wp-security-audit-log' );
			$role     = '';
		} else {
			$user     = new \WP_User( $user_id );
			$username = $user->user_login;
			$role     = ( is_array( $roles ) ? implode( ', ', $roles ) : $roles );
		}
		if ( empty( $role ) ) {
			$role = '';
		}

		$formatted_date  = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $created_on, 'datetime', true, false, false, false );
		$message_context = ( $report_format == WSAL_Rep_DataFormat::CSV ) ? 'report-csv' : 'report-html'; // phpcs:ignore

		$alert_object = $this->occurrence->get_alert();
		if ( ! $alert_object instanceof WSAL_Alert ) {
			// This could happen if an event was created by a 3rd party extension, but the extension is no longer active.
			return false;
		}

		$meta = $this->occurrence->get_meta_array();

		// Meta details.
		$result = array(
			'site_id'    => $site_id,
			'blog_name'  => $blog_name,
			'blog_url'   => $blog_url,
			'alert_id'   => $alert_id,
			'date'       => $formatted_date,
			// We need to keep the timestamp to be able to group entries by dates etc. The "date" field is not suitable
			// as it is already translated, thus difficult to parse and process.
			'timestamp'  => $created_on,
			'code'       => $const->name,
			// Fill variables in message.
			'message'    => $alert_object->get_message( $meta, $this->occurrence->_cached_message, $entry_id, $message_context ),
			'user_id'    => $user_id,
			'user_name'  => $username,
			'role'       => $role,
			'user_ip'    => $ip,
			'object'     => $alerts_manager->get_event_objects_data( $object ),
			'event_type' => $alerts_manager->get_event_type_data( $event_type ),
			'user_agent' => $ua,
		);

		// Metadata and links are formatted separately for CSV only.
		if ( $report_format == WSAL_Rep_DataFormat::CSV ) { // phpcs:ignore
			$alert_formatter = WSAL_AlertFormatterFactory::get_formatter( $message_context );
			$alert_formatter->set_end_of_line( '; ' );

			$result['metadata'] = $alert_object->get_formatted_metadata( $alert_formatter, $meta, $alert_id );
			$result['links']    = $alert_object->get_formatted_hyperlinks( $alert_formatter, $meta, $alert_id );
		}

		return $result;
	}

	/**
	 * Secures given string to make sure it cannot be used to traverse file system when used as (part of) a filename. It
	 * replaces any traversal character (slashes) with underscores.
	 *
	 * @param string $str String to secure.
	 *
	 * @return string Secure string.
	 * @since 4.1.5
	 */
	private static function secure_filename( $str ) {
		$str = str_replace( '/', '_', $str );
		$str = str_replace( '\\', '_', $str );
		$str = str_replace( DIRECTORY_SEPARATOR, '_', $str ); // In case it does not equal the standard values.

		return $str;
	}

	/**
	 * Send the summary email.
	 *
	 * @param string $name - Report name.
	 *
	 * @return bool $result
	 */
	public function send_summary_email( $name ) {

		$report_name   = str_replace( WSAL_PREFIX, '', $name );
		$notifications = $this->get_setting_by_name( self::WSAL_PR_PREFIX . $report_name );

		$is_empty_email_allowed = false;
		/* @premium:start */
		$is_empty_email_enabled = $this->plugin->settings()->is_empty_email_for_periodic_reports_enabled();
		if ( $is_empty_email_enabled ) {
			$is_empty_email_allowed = true;
		}
		/* @premium:end */

		if ( ! empty( $notifications ) ) {
			$frequency       = $notifications->frequency;
			$schedule_number = $this->get_schedule_number( $frequency );
			switch ( $frequency ) {
				case self::REPORT_DAILY:
					$pre_subject = sprintf( esc_html__( '%1$s - Website %2$s', 'wp-security-audit-log' ), $schedule_number, get_bloginfo( 'name' ) ); // phpcs:ignore
					break;
				case self::REPORT_WEEKLY:
					$pre_subject = sprintf( esc_html__( 'Week number %1$s - Website %2$s', 'wp-security-audit-log' ), $schedule_number, get_bloginfo( 'name' ) ); // phpcs:ignore
					break;
				case self::REPORT_MONTHLY:
					$pre_subject = sprintf( esc_html__( 'Month %1$s - Website %2$s', 'wp-security-audit-log' ), $schedule_number, get_bloginfo( 'name' ) ); // phpcs:ignore
					break;
				case self::REPORT_QUARTERLY:
					$pre_subject = sprintf( esc_html__( 'Quarter %1$s - Website %2$s', 'wp-security-audit-log' ), $schedule_number, get_bloginfo( 'name' ) ); // phpcs:ignore
					break;
				default:
					// Fallback for any other reports would go here.
					break;
			}

			// Number logins report.
			$is_number_of_logins = false;
			if ( ! empty( $notifications->enableNumberLogins ) ) { // phpcs:ignore
				$is_number_of_logins = true;
			}

			$attachments = $this->get_attachment( $name, $frequency, $is_number_of_logins );
			if ( empty( $attachments ) && ! $is_empty_email_allowed ) {
				return false;
			}

			$title   = $notifications->title;
			$subject = $pre_subject . sprintf( esc_html__( ' - %s Email Report', 'wp-security-audit-log' ), $title ); // phpcs:ignore

			$content       = '<p>';
			$period_string = '';
			switch ( $frequency ) {
				case self::REPORT_DAILY:
					$period_string = gmdate( $this->date_format, time() ); // @codingStandardsIgnoreLine
					break;
				case self::REPORT_WEEKLY:
					$period_string = 'week ' . $schedule_number;
					break;
				case self::REPORT_MONTHLY:
					$period_string = 'the month of ' . $schedule_number;
					break;
				case self::REPORT_QUARTERLY:
					$period_string = 'the quarter ' . $schedule_number;
					break;
				default:
					// Fallback for any other reports would go here.
					break;
			}

			$report_description = '<strong>' . $title . '</strong> from website <strong>' . get_bloginfo( 'name' ) . '</strong> for <strong>' . $period_string . '</strong>';
			if ( empty( $attachments ) ) {
				$content .= 'No event IDs matched the criteria of the configured report ' . $report_description . '.';
			} else {
				$content .= 'The report ' . $report_description . ' is attached.';
			}

			$content .= '</p>';

			if ( class_exists( 'WSAL_Utilities_Emailer' ) ) {
				// Get email template.
				$email = $notifications->email;
				WSAL_Utilities_Emailer::send_email( $email, $subject, $content, '', $attachments );
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate the report file in desired format from the JSON file.
	 *
	 * @param string $attach_key          Attachment key.
	 * @param string $frequency           Report frequency.
	 * @param bool   $is_number_of_logins True if this is number of logins report.
	 *
	 * @return string Path of the file.
	 */
	private function get_attachment( $attach_key, $frequency, $is_number_of_logins ) {
		$this->reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports', true );

		$result = null;

		$filename = $this->reports_dir_path . $this->generate_periodic_report_filename( $attach_key, $frequency ) . '.json';
		if ( file_exists( $filename ) ) {
			$data = json_decode( file_get_contents( $filename ), true ); // phpcs:ignore
			if ( $is_number_of_logins ) {
				$data['filters']['number_logins'] = true;
			}
			$result = $this->generate_report_file( $data['data'], $data['filters'], true, $attach_key, $frequency );
			$result = $this->reports_dir_path . $result;
		}

		// Make sure the JSON file is only deleted if we are not supposed to generate report formatted as JSON.
		if ( $filename !== $result ) {
			@unlink( $filename ); // phpcs:ignore
		}

		return $result;
	}

	/**
	 * Generate the file of the report.
	 *
	 * @param array  $data        Data.
	 * @param array  $filters     Filters.
	 * @param bool   $is_periodic True if periodic report file is being generated.
	 * @param string $attach_key  Sanitized report name in case this is a periodic report.
	 * @param string $frequency   Periodic report frequency.
	 *
	 * @return string|bool - Filename or false.
	 */
	private function generate_report_file( $data, $filters, $is_periodic = false, $attach_key = '', $frequency = '' ) {
		$report_format    = ! empty( $filters['report_format'] ) ? intval( $filters['report_format'] ) : WSAL_Rep_DataFormat::get_default();
		$report_generator = WSAL_Rep_DataFormat::build_report_generator( $this->plugin, $report_format, $this->reports_dir_path, $filters );
		if ( is_null( $report_generator ) ) {
			return false;
		}

		// Figure out the report filename.
		if ( $is_periodic ) {
			$filename = self::generate_periodic_report_filename( $attach_key, $frequency );
		} else {
			$filename = self::generate_report_filename();
		}

		if ( empty( $data ) ) {
			$this->add_error( self::build_error( 0, $this->reports_dir_path )->get_error_message() );
			return false;
		}

		// Check directory once more.
		if ( ! is_dir( $this->reports_dir_path ) || ! is_readable( $this->reports_dir_path ) || ! is_writable( $this->reports_dir_path ) ) {
			$this->add_error( self::build_error( 1, $this->reports_dir_path )->get_error_message() );
		}

		// Report Number and list of unique IP.
		if ( ! empty( $filters['unique_ip'] ) ) {
			$date_start = ! empty( $filters['date_range']['start'] ) ? $filters['date_range']['start'] : null;
			$date_end   = ! empty( $filters['date_range']['end'] ) ? $filters['date_range']['end'] : null;
			$result     = $report_generator->generate_unique_ips( $filename, $data, $date_start, $date_end );
		} else {
			$result = $report_generator->generate( $filename, $data, $this->alert_groups );
		}

		if ( is_wp_error( $result ) ) {
			$this->add_error( $result->get_error_message() );

			return false;
		}

		return $result;
	}

	/**
	 * Sends a single report. Used both from a cron job context and the AJAX call to send a single periodic report.
	 *
	 * @param object $report Report object.
	 * @param int    $limit Number of events to include in the report.
	 *
	 * @return bool True if there was some relevant data and the email was sent.
	 */
	private function send_single_report( $report, $limit = 100 ) {
		if ( empty( $report ) ) {
			return false;
		}

		$next_date = null;
		do {
			$next_date = $this->build_attachment( $report, $next_date, $limit );
			$last_date = $next_date;
		} while ( ! is_null( $last_date ) );

		return $this->send_summary_email( $report->title );
	}

	/**
	 * Send periodic report.
	 *
	 * @param string $report_name - Report name.
	 * @param int    $limit       - Limit.
	 *
	 * @return bool True if there was some relevant data and the email was sent.
	 */
	public function send_now_periodic( $report_name, $limit = 100 ) {
		$report = $this->get_periodic_report( $report_name );

		return $this->send_single_report( $report, $limit );
	}

	/**
	 * Appending the report data to the content of the json file.
	 *
	 * @param string $report - Report data.
	 */
	public function generate_report_json_file( $report, $user_id = null ) {
		$this->reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports' );
		if ( is_wp_error( $this->reports_dir_path ) ) {
			return;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$filename = $this->reports_dir_path . 'report-user' . $user_id . '.json';
		if ( file_exists( $filename ) ) {
			$data = json_decode( file_get_contents( $filename ), true ); // phpcs:ignore
			if ( ! empty( $data ) ) {
				if ( ! empty( $report ) ) {
					foreach ( $report['data'] as $value ) {
						array_push( $data['data'], $value );
					}
				}
				file_put_contents( $filename, json_encode( $data ) ); // phpcs:ignore
			}
		} else {
			if ( ! empty( $report ) ) {
				file_put_contents( $filename, json_encode( $report ) ); // phpcs:ignore
			}
		}
	}

	/**
	 * Generate the file on download it.
	 *
	 * @return string $download_page_url file URL
	 */
	public function download_report_file( $user_id = null ) {
		$download_page_url      = null;
		$this->reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports', true );
		if ( null === $user_id ) {
			$user_id                = get_current_user_id();
		}
		$filename               = $this->reports_dir_path . 'report-user' . $user_id . '.json';
		if ( file_exists( $filename ) ) {
			$data   = json_decode( file_get_contents( $filename ), true ); // phpcs:ignore
			$result = $this->generate_report_file( $data['data'], $data['filters'] );
			if ( ! empty( $result ) ) {
				$download_page_url = $this->build_report_download_url( $result, $data['filters']['report_format'] );
				$this->save_report_info( $user_id, $download_page_url, $data['filters'], $result );
			}
			@unlink( $filename ); // phpcs:ignore
		}

		return $download_page_url;
	}

	/**
	 * Saves info about a report.
	 *
	 * @param int    $user_id      User ID.
	 * @param string $download_url Download URL.
	 * @param array  $filters      Filters.
	 * @param string $filename     Filename.
	 *
	 * @since 4.4.0
	 */
	private function save_report_info( $user_id, $download_url, $filters, $filename ) {
		$reports = Settings_Helper::get_option_value( self::SAVED_REPORTS_OPTION_NAME, array() );
		$report  = new WSAL_Rep_Report( $user_id, $download_url, $filters, $filename );
		array_push( $reports, $report->to_array() );

		Settings_Helper::set_option_value( self::SAVED_REPORTS_OPTION_NAME, $reports, false );
	}

	/**
	 * Deletes a saved report - the file itself and also the entry from the database.
	 *
	 * @param string $filename Report file name (not a full path).
	 *
	 * @since 4.4.0
	 */
	public function delete_saved_report( $filename ) {
		$reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports', true );
		if ( file_exists( $reports_dir_path . DIRECTORY_SEPARATOR . $filename ) ) {
			@unlink( $reports_dir_path . DIRECTORY_SEPARATOR . $filename ); // phpcs:ignore
		}

		$reports = Settings_Helper::get_option_value( self::SAVED_REPORTS_OPTION_NAME, array() );
		if ( empty( $reports ) ) {
			return;
		}

		$counter = 0;
		foreach ( $reports as $report_array ) {
			$report = WSAL_Rep_Report::from_array( $report_array );
			if ( $filename === $report->get_filename() ) {
				array_splice( $reports, $counter, 1 );
				Settings_Helper::set_option_value( self::SAVED_REPORTS_OPTION_NAME, $reports, false );
			}
			$counter ++;
		}
	}

	/**
	 * Retrieves a list of previously generated reports order by time descending.
	 *
	 * @return WSAL_Rep_Report[]
	 *
	 * @since 4.4.0
	 */
	public function get_all_reports() {
		$reports = Settings_Helper::get_option_value( self::SAVED_REPORTS_OPTION_NAME, array() );

		// Sort by time from newest to oldest.
		$timestamps = array_column( $reports, 'time' );
		array_multisort( $timestamps, SORT_DESC, $reports );

		return array_map(
			function ( $report_array ) {
				return WSAL_Rep_Report::from_array( $report_array );
			},
			$reports
		);
	}

	/**
	 * Get alerts codes by a SINGLE group name.
	 *
	 * @param string $alert_group - Group name.
	 *
	 * @return array codes
	 */
	public function get_codes_by_group( $alert_group ) {
		$_codes = array();
		$alerts = $this->plugin->alerts->get_categorized_alerts();
		foreach ( $alerts as $group ) {
			foreach ( $group as $subname => $_entries ) {
				if ( $subname === $alert_group ) {
					foreach ( $_entries as $alert ) {
						array_push( $_codes, $alert->code );
					}
					break;
				}
			}
		}
		if ( empty( $_codes ) ) {
			return false;
		}

		return $_codes;
	}

	/*============================== Support Archive Database ==============================*/

	/**
	 * Create and send the report return the URL.
	 *
	 * @param array $filters - Filters.
	 *
	 * @return string $download_page_url - Group name.
	 */
	public function statistics_unique_ips( $filters ) {
		$report_format   = ( 0 === strlen( $filters['report_format'] ) ? WSAL_Rep_DataFormat::get_default() : intval( $filters['report_format'] ) );
		$args            = WSAL_ReportArgs::build_from_alternative_filters( $filters );
		$report_type     = array_key_exists( 'type_statistics', $filters ) ? intval( $filters['type_statistics'] ) : null;
		$grouping_period = array_key_exists( 'grouping_period', $filters ) ? $filters['grouping_period'] : null;
		$results         = array_values( $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_ip_address_report_data( $args, 0, $report_type, $grouping_period ) );

		$this->reports_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports' );

		$report_generator = WSAL_Rep_DataFormat::build_report_generator( $this->plugin, $report_format, $this->reports_dir_path, $filters );
		if ( is_null( $report_generator ) ) {
			return;
		}

		$date_start = ! empty( $filters['date_range']['start'] ) ? $filters['date_range']['start'] : null;
		$date_end   = ! empty( $filters['date_range']['end'] ) ? $filters['date_range']['end'] : null;
		$result     = $report_generator->generate_unique_ips( self::generate_report_filename(), $results, $date_start, $date_end );

		if ( 0 === $result ) {
			$this->add_error( esc_html__( 'There are no alerts that match your filtering criteria. Please try a different set of rules.', 'wp-security-audit-log' ) );
			$result = false;
		} elseif ( 1 === $result ) {
			$this->add_error( sprintf( esc_html__( 'Error: The <strong>%s</strong> path is not accessible.', 'wp-security-audit-log' ), $this->reports_dir_path ) ); // phpcs:ignore
			$result = false;
		} elseif ( is_wp_error( $result ) ) {
			$this->add_error( $result->get_error_message() );
			$result = false;
		}

		$download_page_url = null;
		if ( ! empty( $result ) ) {
			$download_page_url = $this->build_report_download_url( $result, $report_format );
		}

		return $download_page_url;
	}

	/**
	 * Check if there is match on the report criteria.
	 *
	 * @param array $filters - Filters.
	 *
	 * @return bool value
	 */
	public function is_matching_report_criteria( $filters ) {
		$report_args = WSAL_ReportArgs::build_from_alternative_filters( $filters );
		$count       = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->check_match_report_criteria( $report_args );

		return $count > 0;
	}

	/**
	 * Set the setting by name with the given value.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - value.
	 */
	public function add_global_setting( $option, $value ) {
		Settings_Helper::set_option_value( $option, $value );
	}

	/**
	 * Gets logins for given user IDs.
	 *
	 * @param int[] $user_ids List of user IDs.
	 *
	 * @return string[] List of user logins names.
	 * @since 4.3.2
	 */
	public function get_logings_for_user_ids( $user_ids ) {
		$user_logins = array();
		foreach ( $user_ids as $user_id ) {
			$user = get_user_by( 'ID', $user_id );
			if ( $user ) {
				$user_logins[] = $user->user_login;
			}
		}

		return $user_logins;
	}

	/**
	 * Gets user IDs for given logins.
	 *
	 * @param string[] $user_logins List of user logins names.
	 *
	 * @return int[] List of user IDs.
	 * @since 4.4.0
	 */
	public function get_user_ids_for_logings( $user_logins ) {
		$result = array();
		foreach ( $user_logins as $user_login ) {
			$user_id = WSAL_Utilities_UsersUtils::swap_login_for_id( $user_login );
			if ( ! is_int( $user_id ) ) {
				array_push( $result, $user_id );
			}
		}

		return $result;
	}

	/**
	 * Builds report download URL that includes nonce for added security.
	 *
	 * @param string $generator_result Report generator result.
	 * @param int    $report_format    Report format.
	 *
	 * @return string URL to download the report.
	 * @since 4.3.2
	 */
	private function build_report_download_url( $generator_result, $report_format ) {
		return add_query_arg(
			array(
				'action' => 'wsal_report_download',
				'f'      => base64_encode( $generator_result ), // phpcs:ignore
				'ctype'  => $report_format,
				'nonce'  => wp_create_nonce( 'wpsal_reporting_nonce_action' ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Populates string representing a schedule number based on time frequency. The result can contain spaces.
	 *
	 * @param string $frequency Report frequency.
	 *
	 * @return string Schedule number string.
	 *
	 * @since 4.4.0
	 */
	private function get_schedule_number( $frequency ) {
		switch ( $frequency ) {
			case self::REPORT_DAILY:
				return date( $this->date_format, time() ); // phpcs:ignore
			case self::REPORT_WEEKLY:
				return date( 'W', strtotime( '-1 week' ) ); // phpcs:ignore
			case self::REPORT_MONTHLY:
				$last_month = strtotime( '-1 month' );
				return date( 'F', $last_month ) . ' ' . date( 'Y', $last_month ); // phpcs:ignore
			case self::REPORT_QUARTERLY:
				return $this->which_quarter();
			default:
				// Fallback for any other report frequency would go here.
				return '';

		}
	}

	/**
	 * Build WordPress error object based on numeric code.
	 *
	 * @param int    $code             Error code.
	 * @param string $reports_dir_path Path to the reports' storage directory.
	 *
	 * @return \WP_Error
	 * @since 4.4.0
	 */
	public static function build_error( $code, $reports_dir_path ) {
		switch ( $code ) {
			case 0:
				return new \WP_Error( 'wsal_no_data', __( 'There are no alerts that match your filtering criteria. Please try a different set of rules.', 'wp-security-audit-log' ) );

			case 1:
				return new \WP_Error(
					'wsal_file_permissions',
					sprintf(
					/* translators: path to the reports' storage directory */
						esc_html__( 'Error: The <strong>%s</strong> path is not accessible.', 'wp-security-audit-log' ),
						$reports_dir_path
					)
				);

			default:
				return new \WP_Error( 'wsal_unknown', __( 'Unexpected error occurred.', 'wp-security-audit-log' ) );
		}
	}

	/**
	 * Builds the full list of possible filters. Ideally this would be used by the rest of the plugin, but for now we
	 * only use if for functionality introduced in version 4.4.0 to prevent further duplication.
	 *
	 * @return array[]
	 *
	 * @since 4.4.0
	 */
	public static function get_possible_filters() {
		return array(
			'sites'                             => array(
				'property_name'  => 'sites',
				'criteria_label' => esc_html__( 'Site(s)', 'wp-security-audit-log' ),
			),
			'sites-exclude'                     => array(
				'property_name'    => 'sites_excluded',
				'is_exclusion_for' => 'sites',
				'criteria_label'   => esc_html__( 'Excluded site(s)', 'wp-security-audit-log' ),
			),
			'users'                             => array(
				'property_name'  => 'users',
				'criteria_label' => esc_html__( 'User(s)', 'wp-security-audit-log' ),
			),
			'users-exclude'                     => array(
				'property_name'    => 'users_excluded',
				'is_exclusion_for' => 'users',
				'criteria_label'   => esc_html__( 'Excluded user(s)', 'wp-security-audit-log' ),
			),
			'roles'                             => array(
				'property_name'  => 'roles',
				'criteria_label' => esc_html__( 'Role(s)', 'wp-security-audit-log' ),
			),
			'roles-exclude'                     => array(
				'property_name'    => 'roles_excluded',
				'is_exclusion_for' => 'roles',
				'criteria_label'   => esc_html__( 'Excluded role(s)', 'wp-security-audit-log' ),
			),
			'ip-addresses'                      => array(
				'property_name'  => 'ipAddresses',
				'criteria_label' => esc_html__( 'IP address(es)', 'wp-security-audit-log' ),
			),
			'ip-addresses-exclude'              => array(
				'property_name'    => 'ipAddresses_excluded',
				'is_exclusion_for' => 'ip-addresses',
				'criteria_label'   => esc_html__( 'Excluded IP address(es)', 'wp-security-audit-log' ),
			),
			'alert_codes'                       => array(
				'property_name'  => 'alert_ids',
				'criteria_label' => esc_html__( 'Alert code(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|alerts'                => array(
				'property_name'  => 'alert_ids',
				'criteria_label' => esc_html__( 'Alert code(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|alerts-exclude'        => array(
				'property_name'    => 'alert_ids_excluded',
				'is_exclusion_for' => 'alert_codes|alerts',
				'criteria_label'   => esc_html__( 'Excluded alert code(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|groups'                => array(
				'property_name'  => 'alert_groups',
				'criteria_label' => esc_html__( 'Alert group(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|groups-exclude'        => array(
				'property_name'    => 'alert_groups_excluded',
				'is_exclusion_for' => 'alert_codes|groups',
				'criteria_label'   => esc_html__( 'Excluded alert group(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_ids'              => array(
				'property_name'  => 'post_ids',
				'criteria_label' => esc_html__( 'Post(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_ids-exclude'      => array(
				'property_name'    => 'post_ids_excluded',
				'is_exclusion_for' => 'alert_codes|post_ids',
				'criteria_label'   => esc_html__( 'Excluded post(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_types'            => array(
				'property_name'  => 'post_types',
				'criteria_label' => esc_html__( 'Post type(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_types-exclude'    => array(
				'property_name'    => 'post_types_excluded',
				'is_exclusion_for' => 'alert_codes|post_types',
				'criteria_label'   => esc_html__( 'Excluded post type(s)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_statuses'         => array(
				'property_name'  => 'post_statuses',
				'criteria_label' => esc_html__( 'Post status(es)', 'wp-security-audit-log' ),
			),
			'alert_codes|post_statuses-exclude' => array(
				'property_name'    => 'post_statuses_excluded',
				'is_exclusion_for' => 'alert_codes|post_statuses',
				'criteria_label'   => esc_html__( 'Excluded post status(es)', 'wp-security-audit-log' ),
			),
			'objects'                           => array(
				'property_name'  => 'objects',
				'criteria_label' => esc_html__( 'Object(s)', 'wp-security-audit-log' ),
			),
			'objects-exclude'                   => array(
				'property_name'    => 'objects_excluded',
				'is_exclusion_for' => 'objects',
				'criteria_label'   => esc_html__( 'Excluded object(s)', 'wp-security-audit-log' ),
			),
			'event-types'                       => array(
				'property_name'  => 'event_types',
				'criteria_label' => esc_html__( 'Event type(s)', 'wp-security-audit-log' ),
			),
			'event-types-exclude'               => array(
				'property_name'    => 'event_types_excluded',
				'is_exclusion_for' => 'event_types',
				'criteria_label'   => esc_html__( 'Excluded event type(s)', 'wp-security-audit-log' ),
			),
		);
	}

	/**
	 * Determines if the report type is based on IP addresses.
	 *
	 * @param int $report_type Report type.
	 *
	 * @return bool True is report is based on IP addresses.
	 * @since 4.4.0
	 */
	public static function is_report_ip_based( $report_type ) {
		return in_array(
			$report_type,
			array(
				self::DIFFERENT_IP,
				self::ALL_IPS,
				self::ALL_USERS,
			),
			true
		);
	}
}
