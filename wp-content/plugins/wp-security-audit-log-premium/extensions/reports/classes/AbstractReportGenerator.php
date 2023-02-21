<?php
/**
 * Class WSAL_Rep_AbstractReportGenerator.
 *
 * @package    wsal
 * @subpackage reports
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Abstract class for different report formats.
 *
 * @package    wsal
 * @subpackage reports
 * @since      4.2.0
 */
abstract class WSAL_Rep_AbstractReportGenerator implements WSAL_Rep_ReportGeneratorInterface {

	/**
	 * Plugin instance.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $plugin;

	/**
	 * Reports directory path.
	 *
	 * @var string
	 */
	protected $reports_dir_path = '';

	/**
	 * Report data format.
	 *
	 * @var int
	 */
	protected $format = WSAL_Rep_DataFormat::HTML;

	/**
	 * Report filters.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin           Plugin instance.
	 * @param string             $reports_dir_path Reports directory path.
	 * @param int                $format           Data format.
	 * @param array              $filters          Report filters.
	 */
	public function __construct( $plugin, $reports_dir_path, $format, $filters ) {
		$this->plugin           = $plugin;
		$this->reports_dir_path = $reports_dir_path;
		$this->format           = $format;
		$this->filters          = $filters;
	}

	/**
	 * Determines a list of columns for the statistical reports.
	 *
	 * @return array
	 *
	 * @since 4.4.0
	 */
	public function get_statistics_columns() {
		$report_type     = array_key_exists( 'type_statistics', $this->filters ) ? $this->filters['type_statistics'] : null;
		$grouping_period = array_key_exists( 'grouping_period', $this->filters ) ? $this->filters['grouping_period'] : null;
		$grouping        = \WSAL\Adapter\WSAL_Adapters_MySQL_ActiveRecord::get_grouping( $report_type, $grouping_period );

		$result = array();

		// Period grouping column will be always first.
		if ( ! is_null( $grouping_period ) ) {
			$result['period'] = esc_html__( 'Username', 'wp-security-audit-log' );
			switch ( $grouping_period ) {
				case 'day':
					$result['period'] = esc_html__( 'Day', 'wp-security-audit-log' );
					break;
				case 'week':
					$result['period'] = esc_html__( 'Week', 'wp-security-audit-log' );
					break;
				case 'month':
					$result['period'] = esc_html__( 'Month', 'wp-security-audit-log' );
					break;
			}
		}

		if ( in_array( 'users', $grouping, true ) ) {
			$result['username']     = esc_html__( 'Username', 'wp-security-audit-log' );
			$result['display_name'] = esc_html__( 'Display name', 'wp-security-audit-log' );
		}

		if ( in_array( 'ips', $grouping, true ) ) {
			$result['ip'] = esc_html__( 'List of IP addresses', 'wp-security-audit-log' );
		}

		if ( in_array( 'posts', $grouping, true ) ) {
			$result['post'] = esc_html__( 'Post title', 'wp-security-audit-log' );
		}

		if ( in_array( 'events', $grouping, true ) ) {
			$result['event'] = esc_html__( 'Event', 'wp-security-audit-log' );
		}

		switch ( $report_type ) {
			case WSAL_Rep_Common::LOGIN_ALL:
			case WSAL_Rep_Common::LOGIN_BY_USER:
			case WSAL_Rep_Common::LOGIN_BY_ROLE:
				$result['count'] = esc_html__( 'Number of logins', 'wp-security-audit-log' );
				break;
			case WSAL_Rep_Common::VIEWS_ALL:
			case WSAL_Rep_Common::VIEWS_BY_USER:
			case WSAL_Rep_Common::VIEWS_BY_ROLE:
			case WSAL_Rep_Common::VIEWS_BY_POST:
				$result['count'] = esc_html__( 'Views', 'wp-security-audit-log' );
				break;
			case WSAL_Rep_Common::PUBLISHED_ALL:
			case WSAL_Rep_Common::PUBLISHED_BY_USER:
			case WSAL_Rep_Common::PUBLISHED_BY_ROLE:
				$result['count'] = esc_html__( 'Published', 'wp-security-audit-log' );
				break;
			case WSAL_Rep_Common::PASSWORD_CHANGES:
				$result['count'] = esc_html__( 'Count', 'wp-security-audit-log' );
				break;
			case WSAL_Rep_Common::PROFILE_CHANGES_ALL:
			case WSAL_Rep_Common::PROFILE_CHANGES_BY_USER:
			case WSAL_Rep_Common::PROFILE_CHANGES_BY_ROLE:
				$result['events'] = esc_html__( 'Events IDs', 'wp-security-audit-log' );
				$result['count']  = esc_html__( 'Count', 'wp-security-audit-log' );
				break;
			case WSAL_Rep_Common::NEW_USERS;
				$result['count'] = esc_html__( 'Total', 'wp-security-audit-log' );
				$roles           = get_editable_roles();
				foreach ( $roles as $role_name => $info ) {
					$result[ $role_name ] = $info['name'];
				}
				break;
			default:
				// No fallback for other types.

		}

		return $result;
	}

	/**
	 * Formats date for the presentation layer.
	 *
	 * @param string $timestamp Timestamp.
	 *
	 * @return string Formatted date.
	 * @since 4.2.0
	 */
	protected function get_formatted_date( $timestamp ) {
		return WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $timestamp, 'date' );
	}

	/**
	 * Returns list of columns for the generic report.
	 *
	 * @param bool $suppress_filter If true, the filter wsal_generic_report_columns won't run. Useful to prevent the
	 *                              filter from firing twice when this function is overridden.
	 *
	 * @return array
	 * @since 4.4.0
	 */
	protected function get_generic_columns( $suppress_filter = false ) {
		$result = array(
			'alert_id'         => esc_html__( 'Code', 'wp-security-audit-log' ),
			'code'             => esc_html__( 'Type', 'wp-security-audit-log' ),
			'timestamp'        => esc_html__( 'Date', 'wp-security-audit-log' ),
			'user_name'        => esc_html__( 'Username', 'wp-security-audit-log' ),
			'user_displayname' => esc_html__( 'User', 'wp-security-audit-log' ),
			'role'             => esc_html__( 'Role', 'wp-security-audit-log' ),
			'user_ip'          => esc_html__( 'Source IP', 'wp-security-audit-log' ),
			'object'           => esc_html__( 'Object Type', 'wp-security-audit-log' ),
			'event_type'       => esc_html__( 'Event Type', 'wp-security-audit-log' ),
			'message'          => esc_html__( 'Message', 'wp-security-audit-log' ),
		);

		if ( ! $suppress_filter ) {
			/**
			 * WSAL Filter: `wsal_generic_report_columns`
			 *
			 * Filters columns in generic report before writing it to a file.
			 *
			 * @param array   $columns     List of columns.
			 * @param integer $data_format Report data format.
			 *
			 * @since 4.4.0
			 */
			return apply_filters( 'wsal_generic_report_columns', $result, $this->format );
		}

		return $result;
	}

	/**
	 * Group data by blog so we can display an organized report.
	 *
	 * @param array $data Report data.
	 *
	 * @return array Report data grouped by blog name.
	 * @since 4.4.0
	 */
	protected function group_data_by_blog_name( array $data ): array {

		$result = array();
		foreach ( $data as $entry ) {
			if ( false === $entry || is_null( $entry ) ) {
				continue;
			}

			if ( ! array_key_exists( 'blog_name', $entry ) && array_key_exists( 'site_id', $entry ) ) {
				$blog_info          = WSAL_AlertManager::get_blog_info( $this->plugin, intval( $entry['site_id'] ) );
				$entry['blog_name'] = $blog_info['name'];
			}

			if ( ! array_key_exists( 'blog_name', $entry ) ) {
				continue;
			}

			$blog_name = $entry['blog_name'];
			if ( array_key_exists( 'user_name', $entry ) ) {
				$user                      = get_user_by( 'login', $entry['user_name'] );
				$entry['user_displayname'] = empty( $user ) ? '' : WSAL_Utilities_UsersUtils::get_display_label( $this->plugin, $user );
			}

			if ( ! isset( $result[ $blog_name ] ) ) {
				$result[ $blog_name ] = array();
			}
			array_push( $result[ $blog_name ], $entry );
		}

		return $result;
	}

	/**
	 * Transforms raw data for each row to actual data to display.
	 *
	 * @param array $element         Raw row data.
	 * @param array $columns         Columns data.
	 * @param int   $type_statistics Report type.
	 *
	 * @return array
	 *
	 * @since 4.4.0
	 */
	protected function get_statistical_row_data( $element, &$columns, $type_statistics ) {

		$result = array();
		if ( array_key_exists( 'period', $columns ) ) {
			$result['period'] = $element['period'];
		}

		if ( array_key_exists( 'username', $columns ) ) {
			if ( array_key_exists( 'username', $element ) ) {
				$username = $element['username'];
				$user     = get_user_by( 'login', $username );
			} elseif ( array_key_exists( 'user', $element ) ) {
				if ( is_numeric( $element['user'] ) ) {
					$user = get_user_by( 'id', $element['user'] );
					if ( $user instanceof WP_User ) {
						$username = $user->user_login;
					}
				} else {
					$username = $element['user'];
					$user     = get_user_by( 'login', $username );
				}
			}

			$display_name = $username;
			if ( $user instanceof WP_User ) {
				$display_name = WSAL_Utilities_UsersUtils::get_display_label( $this->plugin, $user );
			}

			$result['username']     = $username;
			$result['display_name'] = $display_name;
		}

		if ( array_key_exists( 'post', $columns ) ) {
			$post_title = get_the_title( $element['post_id'] );
			if ( 0 === strlen( $post_title ) ) {
				$post_title = esc_html__( 'Unknown', 'wp-security-log' );
			}

			$result['post_title'] = $post_title;
		}

		if ( array_key_exists( 'ip', $columns ) ) {
			$result['client_ip'] = $element['client_ip'];
		}

		if ( array_key_exists( 'event', $columns ) ) {
			$alert_id = intval( $element['alert_id'] );
			$alert    = $this->plugin->alerts->get_alert( $alert_id );
			if ( in_array( $alert_id, array( 1010, 4003, 4004, 4029 ), true ) ) {
				// Use non-default event labels for password change stats report.
				switch ( $alert_id ) {
					case 1010:
						$result['alert'] = esc_html__( 'Requested password reset', 'wp-security-log' );
						break;
					case 4003:
						$result['alert'] = esc_html__( 'Changed password', 'wp-security-log' );
						break;
					case 4004:
						$result['alert'] = esc_html__( 'Had password changed by another user', 'wp-security-log' );
						break;
					case 4029:
						$result['alert'] = esc_html__( 'A user sent a password reset for this user', 'wp-security-log' );
						break;
					default:
				}
			} else {
				if ( $alert instanceof WSAL_Alert ) {
					$result['alert'] = $alert->desc;
				} else {
					$result['alert'] = $alert_id;
				}
			}
		}

		if ( array_key_exists( 'events', $columns ) && array_key_exists( 'events', $element ) ) {
			$result['events'] = $element['events'];
		} else {
			unset( $columns['events'] );
		}

		if ( ! is_null( $type_statistics ) && ! WSAL_Rep_Common::is_report_ip_based( intval( $type_statistics ) ) && array_key_exists( 'count', $element ) ) {
			$result['count'] = $element['count'];
		}

		if ( ! is_null( $type_statistics ) && ! WSAL_Rep_Common::is_report_ip_based( 75 ) && array_key_exists( 'count', $element ) && array_key_exists( 'role_counts', $element ) ) {
			$result_string = '';
			foreach ( $element['role_counts'] as $role_name => $count ) {
				$result_string       .= esc_html__( 'Users with the ', 'wp-security-log' ) . ucwords( $role_name ) . esc_html__( ' role', 'wp-security-log' ) . ' ' . $count . ',';
				$result[ $role_name ] = $count;
			}
			$result['count'] = $element['count'];
		}

		return $result;
	}
}
