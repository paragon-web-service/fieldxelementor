<?php
/**
 * Sensor: Sessions Handling
 *
 * @since 4.1.0
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WSAL\Helpers\User_Helper;
use \WSAL\Adapter\User_Sessions;

/**
 * WSAL_Sensors_Sessions
 *
 * @package wsal
 * @subpackage user-sessions
 */
class WSAL_Sensors_UserSessionsTracking extends WSAL_AbstractSensor {

	/**
	 * Current user token
	 *
	 * @var string
	 */
	public static $user_token = '';

	/**
	 * Current user id
	 *
	 * @var int
	 */
	public static $user_id = 0;

	/**
	 * Tru if sensor is already hooked.
	 *
	 * @var bool
	 */
	public static $hooked = false;

	/**
	 * Listening using hooks.
	 *
	 * @since 4.1.0
	 */
	public function init() {
		$this->hook_events();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hook_events() {
		if ( ! self::$hooked ) {
			// track session on login.
			add_action( 'set_auth_cookie', array( $this, 'login_session_store' ), 10, 6 );

			/*
			 * Keep track of the session before WP may clear it from it's own records. This might happen just before
			 * logout (logout form submission) or when user data update includes a new password (for example WooCommerce
			 * password reset).
			 */
			add_action( 'login_form_logout', array( $this, 'store_current_user_session_token_and_id' ) );
			add_filter(
				'send_password_change_email',
				function( $send, $user, $userdata ) {
					if ( is_user_logged_in() && get_current_user_id() == $user['ID'] ) { // phpcs:ignore
						$this->store_current_user_session_token_and_id();
					}
					return $send;
				},
				10,
				3
			);

			// clear on logout.
			add_action( 'clear_auth_cookie', array( $this, 'user_session_logout_clear' ) );
			// Check if user session is allowed when they are authenticating.
			add_filter( 'authenticate', array( $this, 'check_session_login_rules' ), 100, 2 );
			// remember that these actions are already hooked.
			self::$hooked = true;
		}

	}

	/**
	 * By this point session has already validated... this can only save
	 * but unlikely able to block a login here.
	 *
	 * @method login_session_store
	 * @since
	 * @param  string $auth_cookie an auth cookie string.
	 * @param  string $expire the expiration + 12 hours grace.
	 * @param  string $expiration the expiration time of the session.
	 * @param  int    $user_id the user id logging in.
	 * @param  string $scheme the scheme connection are using.
	 * @param  string $token a session token.
	 */
	public function login_session_store( $auth_cookie, $expire, $expiration, $user_id, $scheme, $token ) {

		// We need non-empty $expiration, $user_id, $token passed.
		if ( empty( $token ) || ( empty( $user_id ) || ! filter_var( $user_id, FILTER_VALIDATE_INT ) ) || empty( $expiration ) ) {
			return;
		}

		// Get the user roles and sites (in multisite context).
		$sites = array();
		$user  = get_user_by( 'id', $user_id );
		$roles = $this->get_user_roles( $user );

		$is_multisite = WpSecurityAuditLog::is_multisite();
		if ( $is_multisite && function_exists( 'get_blogs_of_user' ) ) {
			$blogs         = get_blogs_of_user( $user_id );
			$is_superadmin = is_super_admin( $user_id );
			$sites         = ( $is_superadmin ) ? array( 'all' ) : array_keys( $blogs );
		}

		// Setup the session info to store in the custom table.
		$active_record = array();

		$active_record['user_id']       = (int) $user_id;
		$active_record['session_token'] = (string) WSAL_UserSessions_Helpers::hash_token( $token );
		$active_record['creation_time'] = (int) time();
		$active_record['expiry_time']   = (int) $expiration;
		$active_record['ip']            = (string) $this->plugin->settings()->get_main_client_ip();
		// Wrap using array_values for case where a role has been removed from array and the keys need to be reset.
		// The value would be stored differently in the database.
		$active_record['roles'] = array_values( (array) $roles );
		$active_record['sites'] = (string) implode( ',', $sites );

		// Remember token for later.
		self::$user_token = $token;

		// Also remember user ID for later (it is necessary for compatibility with some alternative authentication
		// method plugins, for example 2FA plugin).
		self::$user_id = $user->ID;

		// save the session data to database.
		User_Sessions::save( $active_record );

		// fire event 1000 here.
	}

	/**
	 * Saves the current user session token when it's available so it can be
	 * used later in the login process.
	 *
	 * @method store_current_user_session_token_and_id
	 * @since 4.1.0
	 */
	public function store_current_user_session_token_and_id() {
		// tries to get the current user and their current token before they get
		// cleared.
		$token = wp_get_session_token();
		$user  = wp_get_current_user();
		if ( ( isset( $user ) && is_a( $user, '\WP_User' ) ) && $token ) {
			// hang onto the token for processing at a later point.
			self::$user_token = $token;
			self::$user_id    = $user->ID;
			// add an action where we will clear the token from tables.
			add_action( 'wp_logout', array( $this, 'user_session_logout_clear' ) );
			add_action( 'clear_auth_cookie', array( $this, 'user_session_logout_clear' ) );
		}
	}

	/**
	 * Deletes a session from custom tables if it was removed from usermeta.
	 *
	 * @method user_session_logout_clear
	 * @since  4.1.0
	 */
	public function user_session_logout_clear() {
		$cleared = false;
		if ( ! empty( self::$user_token ) && ! empty( self::$user_id ) ) {
			$user_sessions_wp = User_Sessions::get_user_session_tokens( self::$user_id );
			if ( ! isset( $user_sessions_wp[ self::$user_token ] ) ) {
				// Delete session data from everywhere (even though WP token might have already been cleared in some
				// cases).
				User_Sessions::delete_session( self::$user_id, WSAL_UserSessions_Helpers::hash_token( self::$user_token ) );
				$cleared = true;
			}
		}
		return $cleared;
	}

	/**
	 * NOTE: on multisite roles are empty when user is not part of the site they are logging in from, need to get roles
	 * from a blog id based WP_User.
	 *
	 * @param WP_User $user User.
	 *
	 * @return array|mixed|string[]
	 * @since 4.1.5
	 */
	private function get_user_roles( $user ) {
		$is_multisite = WpSecurityAuditLog::is_multisite();
		if ( ! $is_multisite || ! function_exists( 'get_blogs_of_user' ) ) {
			return ( ! empty( $user->roles ) ) ? $user->roles : array( '' );
		}

		$result        = array();
		$blogs         = get_blogs_of_user( $user->ID );
		$is_superadmin = is_super_admin( $user->ID );
		foreach ( $blogs as $blog ) {
			$user_obj = new WP_User( $user->ID, '', $blog->userblog_id );
			if ( $user_obj->roles && ! empty( $user_obj->roles ) ) {
				foreach ( $user_obj->roles as $role ) {
					if ( ! in_array( $role, $result, true ) ) {
						$result[] = $role;
					}
				}
				// When user has an admin role anywhere put that at the top
				// of the list.
				if ( count( $result ) > 1 && in_array( 'administrator', $result, true ) ) {
					array_unshift( $result, 'administrator' );
					$result = array_unique( $result );
				}

				// Check if user is a superadmin too.
				if ( $is_superadmin ) {
					$roles[] = 'superadmin';
				}
			}
		}
		return $result;
	}
	/**
	 * Handle sessions login allow/block logic.
	 *
	 * @param WP_User $current_user - User object.
	 * @param string  $username - User name.
	 *
	 * @return WP_Error|WP_User
	 */
	public function check_session_login_rules( $current_user, $username ) {
		// Check $current_user if this is already an error return it.
		if ( ! $current_user || $current_user instanceof WP_Error ) {
			return $current_user;
		}

		// if policies are not enabled with master switch just return user.
		if ( ! \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policies_enabled', false ) ) {
			// Track multiple sessions - trigger 1005 if necessary - Issue 2635 - https://github.com/wpwhitesecurity/wp-security-audit-log-premium/issues/2635 .
			$session_tokens = User_Sessions::get_user_session_tokens( $current_user->ID );
			if ( count( $session_tokens ) > 0 ) {

				// Trigger override session event: 1005.
				$site_id                      = (int) $this->plugin->settings()->get_view_site_id();
				$current_session_token_hashed = WSAL_UserSessions_Helpers::hash_token( wp_get_session_token() );
				$ip_addresses                 = User_Sessions::load_user_ip_addresses( $current_user->ID, $site_id, $current_session_token_hashed );

				$roles = ( isset( $current_user->roles ) ) ? $current_user->roles : array();
				if ( is_super_admin( $current_user->ID ) ) {
					$roles[] = 'superadmin';
				}

				if ( ! isset( $this->plugin->alerts ) ) {
					$wsal                 = \WpSecurityAuditLog::get_instance();
					$this->plugin->alerts = new WSAL_AlertManager( $wsal );
				}

				$this->plugin->alerts->trigger_event(
					1005,
					array(
						'Username'         => $current_user->user_login,
						'CurrentUserRoles' => $roles,
						'IPAddress'        => $ip_addresses,
					),
					true
				);
			}
			return $current_user;
		}

		$roles = User_Helper::get_user_roles( $current_user );

		// Get multiple sessions option.
		$policy = WSAL_UserSessions_Helpers::get_role_sessions_policy( reset( $roles ) );
		if ( isset( $policy['policies_disabled'] ) && $policy['policies_disabled'] ) {
			return $current_user;
		}

		// Check if we need to block/clear session for some reason.
		$multiple_sessions = ( isset( $policy['multisessions']['type'] ) ) ? $policy['multisessions']['type'] : 'single';
		if ( 'newest' === $multiple_sessions ) {
			// Only allowing a single user session - any existing sessions are deleted to allow the latest login to happen.
			$user_sessions = \WSAL\Adapter\User_Sessions::get_user_session_tokens( $current_user->ID );
			if ( 0 === count( $user_sessions ) ) {
				return $current_user;
			}
			self::clear_existing_sessions( $current_user->ID ); // Override last user session.
			// trigger override session event: 1006
			// is this an event 1006?
			$tmp          = new WSAL_Models_Occurrence();
			$ip_addresses = $tmp->get_adapter()->get_matching_ips( null );
			$roles        = ( isset( $current_user->roles ) ) ? $current_user->roles : array();
			if ( is_super_admin( $current_user->ID ) ) {
				$roles[] = 'superadmin';
			}

			if ( ! isset( $this->plugin->alerts ) ) {
				$wsal                 = \WpSecurityAuditLog::get_instance();
				$this->plugin->alerts = new WSAL_AlertManager( $wsal );
			}

			$this->plugin->alerts->trigger_event(
				1006,
				array(
					'Username'         => $current_user->user_login,
					'CurrentUserRoles' => $roles,
					'IPAddress'        => $ip_addresses,
				),
				true
			);
			return $current_user; // Return the current user.
		} elseif ( 'single' === $multiple_sessions || 'allow-limited' === $multiple_sessions ) { // If limited sessions are allowed then.
			// Get the number of sessions allowed - only 1 when single.
			$allowed_sessions = ( 'allow-limited' === $multiple_sessions && isset( $policy['multisessions']['limit'] ) ) ? $policy['multisessions']['limit'] : 1;

			// Get current user sessions.
			$session_tokens = User_Sessions::get_user_session_tokens( $current_user->ID );

			// Block if the number of sessions is greater or equal to the set limit.
			if ( ! empty( $session_tokens ) ) {
				if ( count( $session_tokens ) >= $allowed_sessions ) {

					// Fire a 1004.
					$user = $current_user;

					// get the users roles.
					$user_roles = User_Helper::get_user_roles( $user );

					if ( ! isset( $this->plugin->alerts ) ) {
						$wsal                 = \WpSecurityAuditLog::get_instance();
						$this->plugin->alerts = new WSAL_AlertManager( $wsal );
					}

					// record a login blocked event.
					$this->plugin->alerts->trigger_event(
						1004,
						array(
							'Username'         => $user->user_login,
							'CurrentUserRoles' => $user_roles,
						),
						true
					);

					// Get blocked session error message.
					$msg = $policy['sessions_error_message'];
					if ( empty( $msg ) ) {
						$msg = __( 'ERROR: Your session was blocked with the <a href="https://en-gb.wordpress.org/plugins/wp-security-audit-log" target="_blank">WP Activity Log plugin</a> because there is already another user logged in with the same username. Please contact the site administrator for more information.', 'wp-security-audit-log' );
					}
					return new WP_Error( 'login_denied', $msg );
				} elseif ( count( $session_tokens ) > 0 ) {

					// Trigger override session event: 1005.
					$site_id                      = (int) $this->plugin->settings()->get_view_site_id();
					$current_session_token_hashed = WSAL_UserSessions_Helpers::hash_token( wp_get_session_token() );
					$ip_addresses                 = User_Sessions::load_user_ip_addresses( $current_user->ID, $site_id, $current_session_token_hashed );

					$roles = ( isset( $current_user->roles ) ) ? $current_user->roles : array();
					if ( is_super_admin( $current_user->ID ) ) {
						$roles[] = 'superadmin';
					}

					if ( ! isset( $this->plugin->alerts ) ) {
						$wsal                 = \WpSecurityAuditLog::get_instance();
						$this->plugin->alerts = new WSAL_AlertManager( $wsal );
					}

					$this->plugin->alerts->trigger_event(
						1005,
						array(
							'Username'         => $current_user->user_login,
							'CurrentUserRoles' => $roles,
							'IPAddress'        => $ip_addresses,
						),
						true
					);
				}
			}
		}
		return $current_user;
	}

	/**
	 * Clears any and all existing sessions for a given user id.
	 *
	 * @param int $user_id - User id.
	 *
	 * @return int
	 * @since 4.1.0
	 */
	public static function clear_existing_sessions( $user_id ) {
		// Get current user sessions.
		if ( ! $user_id ) {
			return 0;
		}
		delete_user_meta( $user_id, 'session_tokens' );

		// Delete old sessions of this user.
		return User_Sessions::delete_by_user_ids( array( $user_id ) );
	}
}
