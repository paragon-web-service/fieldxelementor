<?php
/**
 * Class WSAL_UserSessions_Helpers.
 *
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for various functionalities in the user sessions extension.
 *
 * @package wsal
 */
class WSAL_UserSessions_Helpers {

	/**
	 * Hashes the given session token.
	 *
	 * NOTE: This is how core session manager does it.
	 *
	 * @param string $token Session token to hash.
	 * @return string A hash of the session token (a verifier).
	 */
	public static function hash_token( $token ) {
		// If ext/hash is not present, use sha1() instead.
		if ( function_exists( 'hash' ) ) {
			return hash( 'sha256', $token );
		} else {
			return sha1( $token );
		}
	}

	/**
	 * Checks if the option to cleanup core sessions is enabled.
	 *
	 * @method is_core_session_cleanup_enabled
	 * @since  4.1.0
	 * @return boolean
	 */
	public static function is_core_session_cleanup_enabled() {
		return \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_core_cleanup_cron_enabled', true );
	}

	/**
	 * Checks if the option to cleanup idle sessions is enabled.
	 *
	 * @method is_idle_session_cleanup_enabled
	 * @since  4.1.0
	 * @return boolean
	 */
	public static function is_idle_session_cleanup_enabled() {
		// check if policies master switch is enabled. If it's not then return
		// false early to save policy lookups.
		$enabled = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policies_enabled', false );
		if ( ! $enabled ) {
			return false;
		}

		$master_policy = self::get_master_sessions_policy();

		if ( isset( $master_policy['auto_terminate']['enabled'] ) && $master_policy['auto_terminate']['enabled'] ) {
			// already decided this is active based on master policy.
			$enabled = true;
		}

		// If master policy doesn't enable this then we need to check if any
		// role policy does.
		if ( ! $enabled ) {
			global $wp_roles;
			foreach ( array_keys( $wp_roles->roles ) as $role ) {
				$role_policy = self::get_role_sessions_policy( $role );
				if ( ! isset( $role_policy['policies_inherited'] ) || ( isset( $role_policy['policies_inherited'] ) && false === $role_policy['policies_inherited'] ) ) {
					if ( isset( $role_policy['auto_terminate']['enabled'] ) && $role_policy['auto_terminate']['enabled'] ) {
						// already decided this is active based on master policy.
						$enabled = true;
					}
				}
			}
		}

		return $enabled;
	}

	/**
	 * Gets the master user sessions policy.
	 *
	 * @method get_master_sessions_policy
	 * @since  4.1.0
	 * @return array
	 */
	public static function get_master_sessions_policy() {
		$defaults = self::get_policy_defaults();
		$policy   = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policy', array() );

		// merge defaults with any retrieved policy options.
		$policy = array_merge( $defaults, $policy );
		return $policy;
	}

	/**
	 * Gets a sessions policy for a given role type. Returns the master policy
	 * when a role based one does not exist.
	 *
	 * @method get_role_sessions_policy
	 * @since  4.1.0
	 * @param  string $role a role to try get policy for.
	 * @return array
	 */
	public static function get_role_sessions_policy( $role = '' ) {
		$defaults = self::get_policy_defaults();
		if ( is_array( $role ) ) {
			$role = $role[0];
		}
		$policy = \WSAL\Helpers\Settings_Helper::get_option_value( "wsal_usersessions_policy_{$role}" );
		// if we didn't get a policy for the role get the master policy.
		if ( ! $policy ) {
			$policy = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policy', array() );
		}
		// merge defaults with any retrieved policy options.
		$policy = array_merge( $defaults, $policy );
		return $policy;
	}

	/**
	 * Returns an array of policy defaults to use.
	 *
	 * @method get_policy_defaults
	 * @since  4.1.0
	 * @return array
	 */
	public static function get_policy_defaults() {
		return array(
			'policies_enabled'       => false,
			'policies_disabled'      => false,
			'policies_inherited'     => true,
			'multisessions'          => array(
				'type'  => 'single',
				'limit' => 3,
			),
			'sessions_error_message' => __( 'ERROR: Your session was blocked with the WP Activity Log plugin because there is already another user logged in with the same username. Please contact the site administrator for more information.', 'wp-security-audit-log' ),
			'auto_terminate'         => array(
				'enabled'   => false,
				'max_hours' => 1,
			),
		);
	}

	/**
	 * Gets a session ID by hashing the users logged in cookie.
	 *
	 * @method get_session_id_from_logged_in_user_cookie
	 * @since  4.1.0
	 * @return string
	 */
	public static function get_session_id_from_logged_in_user_cookie() {
		$session_id = '';
		$token      = wp_get_session_token();
		if ( ! empty( $token ) ) {
			$session_id = self::hash_token( $token );
		}
		return $session_id;
	}
}
