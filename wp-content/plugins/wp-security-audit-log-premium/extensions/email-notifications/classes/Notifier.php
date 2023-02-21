<?php
/**
 * Class: Utility Class
 *
 * Check for current generated alert.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage email-notifications
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_Notifier
 *
 * Loop through notifications and check if any matches the current generated alert.
 *
 * @author wp.kytten
 * @package wsal
 * @subpackage email-notifications
 */
class WSAL_NP_Notifier extends WSAL_AbstractLogger {

	/**
	 * Alert date.
	 *
	 * @var string
	 */
	private $alert_date = null;

	/**
	 * Email.
	 *
	 * @var string
	 */
	private $email_address = '';

	/**
	 * Phone Number.
	 *
	 * @var string
	 */
	private $phone_number = '';

	/**
	 * Alert ID.
	 *
	 * @var string
	 */
	private $alert_id = null;

	/**
	 * Alert data.
	 *
	 * @var string
	 */
	private $alert_data = null;

	/**
	 * Notification Select 1 data.
	 *
	 * @var string
	 */
	private $s1_data = null;

	/**
	 * Notification Select 2 data.
	 *
	 * @var string
	 */
	private $s2_data = null;

	/**
	 * Notification Select 3 data.
	 *
	 * @var string
	 */
	private $s3_data = null;

	/**
	 * Notification Select 4 data.
	 *
	 * Post Status Select box.
	 *
	 * @var string
	 */
	private $s4_data = null;

	/**
	 * Notification Select 5 data.
	 *
	 * Post Type Select box.
	 *
	 * @var string
	 */
	private $s5_data = null;

	/**
	 * Notification Select 6 data.
	 *
	 * User Roles Select box.
	 *
	 * @var string
	 */
	private $s6_data = null;

	/**
	 * Notification Select 7 data.
	 *
	 * Object Select box.
	 *
	 * @var string
	 */
	private $s7_data = null;

	/**
	 * Notification Select 8 data.
	 *
	 * Event Type Select box.
	 *
	 * @var string
	 */
	private $s8_data = null;

	/**
	 * Is built in?
	 *
	 * @var bool
	 */
	private $is_built_in = false;

	/**
	 * Has template?
	 *
	 * @var bool
	 */
	protected $has_template = false;

	/**
	 * Notifications.
	 *
	 * @var object
	 */
	private $notifications = null;

	/**
	 * Cache Expire Time.
	 *
	 * Time = 12h (60*60*12).
	 *
	 * @var int
	 */
	private $cache_expiration = 43200;

	/**
	 * Is URL Shortner Option Enabled/Disabled.
	 *
	 * @var boolean
	 */
	private $is_url_shortner = false;

	/**
	 * {@inheritDoc}
	 */
	public function log( $type, $data = array(), $date = null, $site_id = null ) {
		$this->alert_id   = $type;
		$this->alert_data = $data;
		$this->alert_date = $this->get_correct_timestamp( $data, $date );

		// We need to remove the timestamp.
		unset( $data['Timestamp'] );

		$nb = new WSAL_NP_NotificationBuilder();

		$this->s1_data = $nb->get_select_1_data();
		$this->s2_data = $nb->get_select_2_data();
		$this->s3_data = $nb->get_select_3_data();
		$this->s4_data = $nb->get_select_4_data(); // Post status.
		$this->s5_data = $nb->get_select_5_data(); // Post types.
		$this->s6_data = $nb->get_select_6_data(); // User roles.
		$this->s7_data = $nb->get_select_7_data(); // Object.
		$this->s8_data = $nb->get_select_8_data(); // Event type.

		/**
		 * Cache notifications.
		 *
		 * @see http://codex.wordpress.org/Class_Reference/WP_Object_Cache
		 */
		$this->notifications = wp_cache_get( WSAL_CACHE_KEY );

		if ( false === $this->notifications ) {
			$this->notifications = $this->plugin->notifications_util->get_notifications();
			wp_cache_set( WSAL_CACHE_KEY, $this->notifications, null, $this->cache_expiration );
		}
		$this->notify_if_condition_matches();
	}

	/**
	 * Notify if Condition Matches.
	 */
	private function notify_if_condition_matches() {
		if ( empty( $this->notifications ) ) {
			return;
		}
		// Go through each notification.
		foreach ( $this->notifications as $k => $v ) {
			$not_info = maybe_unserialize( $v->option_value );
			$enabled  = intval( $not_info->status );

			if ( 0 === $enabled ) {
				continue;
			}

			$skip = false;
			if ( ! empty( $not_info->firstTimeLogin ) && 1000 === $this->alert_id ) { // phpcs:ignore
				$users_login_list = \WSAL\Helpers\Settings_Helper::get_option_value( 'users_login_list' );
				if ( ! empty( $users_login_list ) ) {
					if ( in_array( $this->alert_data['Username'], $users_login_list ) ) { // phpcs:ignore
						$skip = true;
					} else {
						array_push( $users_login_list, $this->alert_data['Username'] );
						\WSAL\Helpers\Settings_Helper::set_option_value( 'users_login_list', $users_login_list );
					}
				} else {
					$users_login_list = array();
					array_push( $users_login_list, $this->alert_data['Username'] );
					\WSAL\Helpers\Settings_Helper::set_option_value( 'users_login_list', $users_login_list );
				}
			}
			// Skip Suspicious Activity.
			if ( ! empty( $not_info->failUser ) && 1002 === $this->alert_id ) { // phpcs:ignore
				$skip = true;
			}
			if ( ! empty( $not_info->failNotUser ) && 1003 === $this->alert_id ) { // phpcs:ignore
				$skip = true;
			}

			if ( $skip ) {
				continue;
			}

			$conditions          = $not_info->triggers;
			$num                 = count( $conditions );
			$title               = $not_info->title;
			$this->email_address = $not_info->email;
			$this->phone_number  = ! empty( $not_info->phone ) ? $not_info->phone : false;

			if ( ! empty( $not_info->built_in ) ) {
				$this->is_built_in = true;
			} else {
				$this->is_built_in = false;
			}

			if ( ! empty( $not_info->subject ) && ! empty( $not_info->body ) ) {
				$this->has_template['subject'] = $not_info->subject;
				$this->has_template['body']    = $not_info->body;
			} else {
				$this->has_template = false;
			}

			// #! one condition
			if ( 1 === $num ) {
				$condition = $conditions[0];

				// Handle PAGE ID AND CUSTOM POST ID deprecation.
				if ( 7 === $condition['select2'] || 8 === $condition['select2'] ) {
					$condition['select2'] = 6;
				}

				$s1 = $this->s1_data[ $condition['select1'] ];
				$s2 = $this->s2_data[ $condition['select2'] ];
				$s3 = $this->s3_data[ $condition['select3'] ];
				$s4 = isset( $condition['select4'] ) ? $this->s4_data[ $condition['select4'] ] : false; // Post status select.
				$s5 = isset( $condition['select5'] ) ? $this->s5_data[ $condition['select5'] ] : false; // Post type select.
				$s6 = isset( $condition['select6'] ) ? $this->s6_data[ $condition['select6'] ] : false; // User roles select.
				$s7 = isset( $condition['select7'] ) ? $this->s7_data[ $condition['select7'] ] : false; // Object select.
				$s8 = isset( $condition['select8'] ) ? $this->s8_data[ $condition['select8'] ] : false; // Event type select.
				$i1 = $condition['input1'];
				$this->check_if_condition_matches( $s1, $s2, $s3, $s4, $s5, $s6, $s7, $s8, $i1, $title, true );
			} else {
				// #! n conditions
				$test_array = array();
				$groups     = $not_info->viewState; // phpcs:ignore
				$last_id    = 0;
				foreach ( $groups as $i => $entry ) {
					$i = $last_id;
					if ( is_string( $entry ) ) {
						array_push( $test_array, $conditions[ $i ] );
						$last_id++;
					} elseif ( is_array( $entry ) ) {
						$new = array();
						foreach ( $entry as $k => $item ) {
							array_push( $new, $conditions[ $last_id ] );
							$last_id++;
						}
						array_push( $test_array, $new );
					}
				}
				// Validate conditions.
				$exp    = new WSAL_NP_Expression( $this, $this->s1_data, $this->s2_data, $this->s3_data, $title, $this->s4_data, $this->s5_data, $this->s6_data, $this->s7_data, $this->s8_data );
				$result = $exp->evaluate_conditions( $test_array );
				if ( $result ) {
					$this->send_notification( $title );
				}
			}

			/* Trigger Critical alert*/
			$alert = $this->plugin->alerts->get_alert( $this->alert_id );
			if ( ! empty( $not_info->isCritical ) && 'E_CRITICAL' === $alert->severity ) { // phpcs:ignore
				$this->send_notification( $title );
			}
		}
	}

	/**
	 * Check whether a condition matches anything in the Request $data
	 *
	 * @param string      $s1 - Select 1.
	 * @param string      $s2 - Select 2.
	 * @param string      $s3 - Select 3.
	 * @param string      $s4 - Select 4 / Post status.
	 * @param string      $s5 - Select 5 / Post type.
	 * @param string      $s6 - Select 6 / User role.
	 * @param string      $s7 - Select 7 / Object.
	 * @param string      $s8 - Select 8 / Event type.
	 * @param string      $i1 - Input 1.
	 * @param null|string $title - The title of the alert.
	 * @param bool        $send_email - Whether to send the notification email. Defaults to false.
	 * @return bool
	 */
	public function check_if_condition_matches( $s1, $s2, $s3, $s4, $s5, $s6, $s7, $s8, $i1, $title = null, $send_email = false ) {
		$date_format = $this->plugin->settings()->get_date_format();
		$time_format = $this->plugin->settings()->get_time_format();

		if ( 'IS EQUAL' === $s3 ) {
			// Default - $type == ALERT CODE.
			$value = $this->alert_id;

			if ( 'DATE' === $s2 ) {
				$value = date( $date_format ); // phpcs:ignore
			} elseif ( 'TIME' === $s2 ) {
				$value = date( $time_format ); // phpcs:ignore
			} elseif ( 'USERNAME' === $s2 ) {
				$uid = ( isset( $this->alert_data['CurrentUserID'] ) ? intval( $this->alert_data['CurrentUserID'] ) : null );
				if ( empty( $uid ) ) { // will happen "on login"
					// This will be populated.
					if ( isset( $this->alert_data['Username'] ) && ! empty( $this->alert_data['Username'] ) ) {
						$value = $this->alert_data['Username'];
					}
				} else {
					$user = get_user_by( 'id', $uid );
					if ( false === $user ) {
						$value = '';
					} else {
						$value = $user->user_login;
					}
				}
			} elseif ( 'USER ROLE' === $s2 ) {
				$roles = ( isset( $this->alert_data['CurrentUserRoles'] ) ? $this->alert_data['CurrentUserRoles'] : null );

				if ( is_array( $roles ) && ! empty( $roles ) ) {
					$s6 = $this->sanitize_user_role( $s6 );
					foreach ( $roles as $role ) {
						if ( 0 === strcasecmp( $s6, $role ) ) {
							if ( $send_email ) {
								return $this->send_notification( $title );
							} else {
								return true;
							}
						}
					}
				}
			} elseif ( 'SOURCE IP' === $s2 ) {
				$value = $this->alert_data['ClientIP'];
			} elseif ( 'PAGE ID' === $s2 || 'POST ID' === $s2 || 'CUSTOM POST ID' === $s2 ) {
				$pid = intval( $i1 );
				if ( empty( $pid ) || ! isset( $this->alert_data['PostID'] ) ) {
					return false;
				}
				$dpid = intval( $this->alert_data['PostID'] );

				if ( $pid <> $dpid ) { // phpcs:ignore
					return false;
				}

				$post_type = strtolower( $this->alert_data['PostType'] );

				if ( 'POST ID' === $s2 ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				} elseif ( 'PAGE ID' === $s2 && 'page' === $post_type ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				} elseif ( 'CUSTOM POST ID' === $s2 && ( 'post' != $post_type && 'page' != $post_type ) ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'SITE DOMAIN' === $s2 ) {
				$sid     = intval( $i1 );
				$blog_id = get_current_blog_id();
				if ( empty( $sid ) ) {
					return false;
				}

				if ( $sid <> $blog_id ) { // phpcs:ignore
					return false;
				}
				if ( $send_email ) {
					return $this->send_notification( $title );
				} else {
					return true;
				}
			} elseif ( 'POST TYPE' === $s2 ) {
				$post_type = ( isset( $this->alert_data['PostType'] ) ? strtolower( $this->alert_data['PostType'] ) : null );

				if ( ! WpSecurityAuditLog::is_multisite() ) {
					// Convert value of $s5 to lowercase.
					$s5 = strtolower( $s5 );
				} else {
					$s5 = strtolower( $i1 );
				}

				if ( ! empty( $post_type ) && $s5 == $post_type ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'POST STATUS' === $s2 ) {
				// Get Post ID from alert data.
				$post_id = isset( $this->alert_data['PostID'] ) ? intval( $this->alert_data['PostID'] ) : false;

				// Get post status.
				$post_status = get_post_status( $post_id );

				// Return if post status is empty.
				if ( empty( $post_status ) ) {
					return false;
				}

				// Convert value of $s4 to lowercase.
				$s4 = strtolower( $s4 );

				// Check for publish post status.
				$post_status = ( 'publish' === $post_status ) ? 'published' : $post_status;

				// Send notification if the selected status matches with the post status.
				if ( ! empty( $s4 ) && $post_status === $s4 ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'OBJECT' === $s2 ) {
				$object = isset( $this->alert_data['Object'] ) ? $this->alert_data['Object'] : false;

				if ( ! $object || ! $s7 ) {
					return false;
				}

				// Most objects can be matched using the sanitized value as a key.
				$match_found          = false;
				$sanitized_log_object = $this->sanitize_log_object( $s7 );
				if ( $object === $sanitized_log_object ) {
					$match_found = true;
				} else {
					// Some objects cannot be matched using the sanitized value, but can be found in the list of available objects.
					$alternative_log_object = $this->get_alternative_object_key( $sanitized_log_object );
					if ( null !== $alternative_log_object && $object === $this->get_alternative_object_key( $sanitized_log_object ) ) {
						$match_found = true;
					}
				}

				if ( $match_found ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					}

					return true;
				}
			} elseif ( 'TYPE' === $s2 ) {
				$event_type = isset( $this->alert_data['EventType'] ) ? $this->alert_data['EventType'] : false;

				if ( ! $event_type ) {
					return false;
				}

				$s8 = strtolower( trim( $s8 ) );
				$s8 = str_replace( ' ', '-', $s8 );
				if ( $s8 && $event_type === $s8 ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					}
					return true;
				}
			} elseif ( 'CUSTOM USER FIELD' === $s2 ) {
				$result = in_array( $this->alert_id, array( 4015, 4016 ) ) // phpcs:ignore
					&& array_key_exists( 'custom_field_name', $this->alert_data )
					&& $this->alert_data['custom_field_name'] === $i1;

				if ( $result && $send_email ) {
					return $this->send_notification( $title );
				}

				return $result;
			}

			// Equality test - except user role.
			if ( $value == $i1 ) { // phpcs:ignore
				if ( $send_email ) {
					return $this->send_notification( $title );
				} else {
					return true;
				}
			}
		} elseif ( 'CONTAINS' === $s3 ) { // Valid only for: SOURCE IP.
			if ( 'SOURCE IP' === $s2 ) {
				if ( false !== strpos( $this->alert_data['ClientIP'], $i1 ) ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			}
		} elseif ( 'IS AFTER' === $s3 ) { // DATE & TIME ONLY.
			if ( 'DATE' === $s2 ) {
				$current_time = current_time( 'timestamp' ); // phpcs:ignore
				$value        = strtotime( str_replace( '-', '/', $i1 ) );
				if ( $current_time > $value ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'TIME' === $s2 ) {
				$current_time    = current_time( 'timestamp' ); // phpcs:ignore
				$configured_time = new DateTime( $i1, wp_timezone() );
				$value           = $configured_time->getTimestamp();
				if ( $current_time > $value ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			}
		} elseif ( 'IS BEFORE' === $s3 ) { // TIME ONLY.
			if ( 'TIME' === $s2 ) {
				$current_time    = current_time( 'timestamp' ); // phpcs:ignore
				$configured_time = new DateTime( $i1, wp_timezone() );
				$value           = $configured_time->getTimestamp();
				if ( $current_time < $value ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			}
		} elseif ( 'IS NOT' === $s3 ) { // USERNAME && USER ROLE && SOURCE IP.
			if ( 'USERNAME' === $s2 ) {
				$uid = isset( $this->alert_data['CurrentUserID'] ) ? $this->alert_data['CurrentUserID'] : false;
				if ( false === $uid ) {
					$user = get_user_by( 'login', $i1 );
				} else {
					$user = get_user_by( 'id', $uid );
				}
				if ( false === $user ) {
					return false;
				}
				$value = $user->user_login;
				if ( $value != $i1 ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'USER ROLE' === $s2 ) {
				$role_found = false;
				$roles      = $this->alert_data['CurrentUserRoles'];

				if ( is_array( $roles ) && ! empty( $roles ) ) {
					$s6 = $this->sanitize_user_role( $s6 );
					foreach ( $roles as $role ) {
						if ( strcasecmp( $s6, $role ) === 0 ) {
							$role_found = true;
						}
					}
				}

				if ( ! $role_found ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'SOURCE IP' === $s2 ) {
				$value = $this->alert_data['ClientIP'];
				if ( $i1 != $value ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'EVENT ID' === $s2 ) {
				if ( $i1 !== (int) $this->alert_id ) {
					return $this->send_notification( $title );
				}
				return true;
			} elseif ( 'POST ID' === $s2 ) {
				if ( $i1 !== (int) $this->alert_data['PostID'] ) {
					return $this->send_notification( $title );
				}
				return true;
			} elseif ( 'SITE DOMAIN' === $s2 ) {
				$sid     = intval( $i1 );
				$blog_id = get_current_blog_id();
				if ( empty( $sid ) ) {
					return false;
				}

				if ( $sid <> $blog_id ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					}
				}
				
				return true;
			} elseif ( 'POST TYPE' === $s2 ) {
				$post_type = ( isset( $this->alert_data['PostType'] ) ? strtolower( $this->alert_data['PostType'] ) : null );

				if ( ! WpSecurityAuditLog::is_multisite() ) {
					// Convert value of $s5 to lowercase.
					$s5 = strtolower( $s5 );
				} else {
					$s5 = strtolower( $i1 );
				}

				if ( ! empty( $post_type ) && $s5 !== $post_type ) { // phpcs:ignore
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'POST STATUS' === $s2 ) {
				// Get Post ID from alert data.
				$post_id = isset( $this->alert_data['PostID'] ) ? intval( $this->alert_data['PostID'] ) : false;

				// Get post status.
				$post_status = get_post_status( $post_id );

				// Return if post status is empty.
				if ( empty( $post_status ) ) {
					return false;
				}

				// Convert value of $s4 to lowercase.
				$s4 = strtolower( $s4 );

				// Check for publish post status.
				$post_status = ( 'publish' === $post_status ) ? 'published' : $post_status;

				// Send notification if the selected status matches with the post status.
				if ( ! empty( $s4 ) && $post_status !== $s4 ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					} else {
						return true;
					}
				}
			} elseif ( 'OBJECT' === $s2 ) {
				$object = isset( $this->alert_data['Object'] ) ? $this->alert_data['Object'] : false;

				if ( ! $object || ! $s7 ) {
					return false;
				}

				// Most objects can be matched using the sanitized value as a key.
				$match_found          = false;
				$sanitized_log_object = $this->sanitize_log_object( $s7 );
				if ( $object === $sanitized_log_object ) {
					$match_found = true;
				} else {
					// Some objects cannot be matched using the sanitized value, but can be found in the list of available objects.
					$alternative_log_object = $this->get_alternative_object_key( $sanitized_log_object );
					if ( null !== $alternative_log_object && $object === $this->get_alternative_object_key( $sanitized_log_object ) ) {
						$match_found = true;
					}
				}

				if ( ! $match_found ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					}

					return true;
				}
			} elseif ( 'TYPE' === $s2 ) {
				$event_type = isset( $this->alert_data['EventType'] ) ? $this->alert_data['EventType'] : false;

				if ( ! $event_type ) {
					return false;
				}

				$s8 = strtolower( trim( $s8 ) );
				$s8 = str_replace( ' ', '-', $s8 );
				if ( $s8 && $event_type !== $s8 ) {
					if ( $send_email ) {
						return $this->send_notification( $title );
					}
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Send the notification email
	 *
	 * @param string $title - The Notification title.
	 *
	 * @return bool
	 */
	public function send_notification( $title = '' ) {

		// Bail if we have null data.
		if ( ! isset( $this->alert_data ) ) {
			return;
		}

		$alert           = $this->plugin->alerts->get_alert( $this->alert_id );
		$alert_message   = $alert->get_message( (array) $this->alert_data, null, 0, 'email' );
		$alert_formatter = WSAL_AlertFormatterFactory::get_formatter( 'email' );

		$uid      = isset( $this->alert_data['CurrentUserID'] ) ? $this->alert_data['CurrentUserID'] : null;
		$username = esc_html__( 'System', 'wp-security-audit-log' );

		if ( empty( $uid ) ) { // will happen "on login"
			// This will be populated.
			if ( ! empty( $this->alert_data['Username'] ) ) {
				$username = $this->alert_data['Username'];
			}
		} else {
			$user = get_user_by( 'id', $uid );
			if ( false !== $user ) {
				$username = $user->user_login;
			}
		}

		// Get user first and last names from resulting username.
		$current_user = get_user_by( 'login', $username );
		$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
		$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
		$user_email   = isset( $current_user->user_email ) ? $current_user->user_email : '';

		if ( $this->alert_date ) {
			$date = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $this->alert_date );
		} else {
			$date = $this->plugin->notifications_util->get_formatted_datetime();
		}

		$_user_roles = isset( $this->alert_data['CurrentUserRoles'] ) ? $this->alert_data['CurrentUserRoles'] : null;
		$user_role   = '';

		if ( isset( $_user_roles[0] ) && ! empty( $_user_roles[0] ) ) {
			if ( count( $_user_roles ) > 1 ) {
				$user_role = implode( ', ', $_user_roles );
			} else {
				$user_role = $_user_roles[0];
			}
		}

		$blogname           = $this->plugin->notifications_util->get_blog_domain();
		$name               = 'builder';
		$search_email_tags  = array_keys( $this->plugin->notifications_util->get_email_template_tags() );
		$replace_email_tags = array(
			$title,
			$blogname,
			$username,
			$first_name,
			$last_name,
			$user_role,
			$user_email,
			$date,
			$this->alert_id,
			$this->plugin->notifications_util->get_alert_severity( $this->alert_id ),
			$alert_message,
			$alert->get_formatted_metadata( $alert_formatter, $this->alert_data, 0 ),
			$alert->get_formatted_hyperlinks( $alert_formatter, $this->alert_data, 0 ),
			$this->alert_data['ClientIP'],
			$this->alert_data['Object'],
			$this->alert_data['EventType'],
		);

		if ( $this->has_template ) {
			$subject = str_replace( $search_email_tags, $replace_email_tags, $this->has_template['subject'] );
			$content = str_replace( $search_email_tags, $replace_email_tags, stripslashes( $this->has_template['body'] ) );
		} else {
			$template = $this->plugin->notifications_util->get_email_template( $name );
			$subject  = str_replace( $search_email_tags, $replace_email_tags, $template['subject'] );
			$content  = str_replace( $search_email_tags, $replace_email_tags, stripslashes( $template['body'] ) );
		}

		// Send email notification.
		$email = false;
		if ( $this->email_address ) {
			$email = $this->plugin->notifications_util->send_notification_email( $this->email_address, $subject, $content, $this->alert_id );
		}

		// If phone number is set then send an SMS and return.
		$sms_message = false;
		if ( $this->phone_number ) {
			// Make sure to send SMS only if the libraries necessary for SMS messaging functionality are available.
			if ( WSAL_Extension_Manager::is_messaging_available() ) {
				$this->is_url_shortner = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'is-url-shortner' );
				$alert_message         = $alert->get_message( (array) $this->alert_data, null, 0, 'sms' );
				$search_sms_tags       = array_keys( $this->plugin->notifications_util->get_sms_template_tags() );
				$replace_sms_tags      = array(
					$blogname,
					$username,
					$user_role,
					$user_email,
					$date,
					$this->alert_id,
					$this->plugin->notifications_util->get_alert_severity( $this->alert_id ),
					$alert_message,
					$this->alert_data['ClientIP'],
					$this->alert_data['Object'],
					$this->alert_data['EventType'],
				);

				$sms_template = $this->plugin->notifications_util->get_sms_template( $name );
				$sms_content  = str_replace( $search_sms_tags, $replace_sms_tags, $sms_template['body'] );
				$sms_message  = $this->plugin->notifications_util->send_notification_sms( $this->phone_number, $sms_content );
			}
		}

		return $email || $sms_message;
	}

	/**
	 * Sanitizes user role string.
	 *
	 * @param string $value User role string. Might contain spaces and uppercase letters.
	 *
	 * @return string Sanitized user role. All lowercase and underscores instead of spaces.
	 * @since 4.2.0
	 */
	private function sanitize_user_role( $value ) {

		$user_roles    = array();
		$wp_user_roles = array();

		// Check if function `wp_roles` exists.
		if ( function_exists( 'wp_roles' ) ) {
			// Get WP user roles.
			$wp_user_roles = wp_roles()->roles;
		} else { // WP Version is below 4.3.0
			// Get global wp roles variable.
			global $wp_roles;

			// If it is not set then initiate WP_Roles class object.
			if ( ! isset( $wp_roles ) ) {
				$new_wp_roles = new WP_Roles();
			}

			// Get WP user roles.
			$wp_user_roles = $new_wp_roles->roles;
		}

		foreach ( $wp_user_roles as $role => $details ) {
			$user_roles[$role] = translate_user_role( $details['name'] );
		}
		// $user_roles = implode( ', ', $user_roles );
		// $user_roles = strtoupper( $user_roles );
		// $user_roles = explode( ',', $user_roles );
		$user_roles = array_map( 'strtoupper', $user_roles );

		$key = array_search( $value, $user_roles );

		return $key;
	}

	/**
	 * Sanitizes event log object.
	 *
	 * @param string $value Log object string. Might contain spaces and uppercase letters.
	 *
	 * @return string Sanitized log object. All lowercase and dashes instead of spaces.
	 * @since 4.2.0
	 */
	private function sanitize_log_object( $value ) {
		return str_replace( ' ', '-', trim( strtolower( $value ) ) );
	}

	/**
	 * Function provide an alternative log object key. Sometimes there is a mismatch between the log object identifier
	 * used in the UI and the key provided in the object's definition.
	 *
	 * @see WSAL_AlertManager::get_event_objects_data()
	 *
	 * @param string $sanitized_log_object Log object key used by the notifications UI.
	 *
	 * @return string|null An alternative key for the log object. Null if not available.
	 * @since 4.2.0
	 */
	private function get_alternative_object_key( $sanitized_log_object ) {
		$available_objects = $this->plugin->alerts->get_event_objects_data();
		$object_key        = array_search( $sanitized_log_object, $available_objects, true );
		if ( $object_key !== false ) { // phpcs:ignore
			return $object_key;
		} else {
			foreach ( $available_objects as $log_object_key => $available_object ) {
				if ( $this->sanitize_log_object( $available_object ) === $sanitized_log_object ) {
					return $log_object_key;
				}
			}
		}

		return null;
	}
}
