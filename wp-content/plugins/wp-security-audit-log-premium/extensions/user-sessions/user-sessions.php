<?php
/**
 * Extension: Users Sessions Management
 *
 * User sessions management extension for wsal.
 *
 * @since   4.1.0
 * @package wsal
 */

use \WSAL\Adapter\User_Sessions;
use WSAL\Adapter\WSAL_Adapters_MySQL_Occurrence;
use WSAL\Adapter\WSAL_Adapters_MySQL_Meta;
/**
 * Class UserSessions_Plugin
 *
 * @package wsal
 */
class WSAL_UserSessions_Plugin {

	/**
	 * Instance of the main WpSecurityAuditLog plugin class.
	 *
	 * @since 4.1.0
	 * @var   WpSecurityAuditLog
	 */
	protected $plugin = null;

	/**
	 * Method: Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		$this->plugin = WpSecurityAuditLog::get_instance();

		// \WSAL\Adapter\User_Sessions::create_table();

		add_action( 'wsal_init', array( $this, 'wsal_init' ) ); // Function to hook at `wsal_init`.
	}

	/**
	 * Checks to see if the sessions management is available for current Freemius plan.
	 *
	 * @return bool True if the sessions management is available.
	 * @since 4.3.0
	 */
	public static function is_session_management_available() {
		return wsal_freemius()->is_plan_or_trial__premium_only( 'business' );
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @method wsal_init
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 *
	 * @since  4.1.0
	 * @see    WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {
		$wsal->user_sessions = $this;

		if ( isset( $wsal->views ) ) {
			$wsal->views->add_from_class( 'WSAL_UserSessions_Views' );
		}
		// hook in any cleanup events.
		$this->add_cleanup_hooks();
	}

	/**
	 * Hooks in the cleanup actions for the sessions handling.
	 *
	 * @method maybe_add_cleanup_hooks
	 * @since  4.1.0
	 */
	private function add_cleanup_hooks() {
		// cleans up expired sessions - also optionally cleans them up from core
		// tables as well depending on user option.
		add_action( 'wsal_cleanup', array( $this, 'expired_sessions_cleanup' ) );

		// if idle session cleanup is enabled then hook this in as well.
		if ( WSAL_UserSessions_Helpers::is_idle_session_cleanup_enabled() ) {
			add_action( 'wsal_cleanup', array( $this, 'idle_sessions_cleanup' ) );
		}

		$sessions_test = apply_filters( 'wsal_inactive_sessions_test', false );
		if ( isset( $sessions_test ) && ! empty( $sessions_test ) ) {
			add_action( 'run_testing_sessions_cleanup', array( $this, 'schedule_testing_sessions_cleanup' ) );
			$next = wp_next_scheduled( 'run_testing_sessions_cleanup' );
			if ( ! $next ) {
				wp_schedule_single_event( time() + $sessions_test, 'run_testing_sessions_cleanup' );
			}
		}
	}

	/**
	 * Runs the idle sessions cleanup.
	 */
	public function schedule_testing_sessions_cleanup() {
		$this->idle_sessions_cleanup();
	}

	/**
	 * Cleans up user sessions which have not triggered any alerts in a user
	 * configured idle time.
	 *
	 * Hooked as an action on the plugins cleanup cron task.
	 *
	 * @method idle_sessions_cleanup
	 * @since  4.1.0
	 */
	public function idle_sessions_cleanup() {
		$sessions = User_Sessions::load_all_sessions_ordered_by_user_id();
		// bail early if we have no sessions.
		if ( empty( $sessions ) ) {
			return;
		}

		foreach ( $sessions as $session ) {

			// first check if this user role is excluded.
			if ( ! isset( $session['roles'] ) ) {
				continue;
			}

			$session['roles'] = json_decode( $session['roles'], true );

			// For the moment we are only looking at a single user role. We
			// might in future need to do multiple role tests and pick a
			// preference for the main one to use.
			$policy = WSAL_UserSessions_Helpers::get_role_sessions_policy( reset( $session['roles'] ) );

			// Check of role has policies disabled.
			if ( isset( $policy['policies_disabled'] ) && ! empty( $policy['policies_disabled'] ) && $policy['policies_disabled'] ) {
				continue;
			}

			$user = get_user_by( 'id', $session['user_id'] );
			if ( ! is_a( $user, '\WP_User' ) ) {
				// we don't have a user object - skip to next iteration.
				continue;
			}

			$last_alert = $this->get_last_user_alert( $user->data->user_login, $session['session_token'] );
			if ( is_array( $last_alert ) && ! empty( $last_alert ) ) {
				// Are we testing sessions currently? Lets check for the option.
				$sessions_test = apply_filters( 'wsal_inactive_sessions_test', false );
				if ( isset( $sessions_test ) && ! empty( $sessions_test ) ) {
					// idle time is a test, so we just need seconds here.
					$idle_time = (int) $sessions_test;
				} else {
					// idle time is saved in DB in hours so multiply by mins and seconds.
					$idle_time = (int) $policy['auto_terminate']['max_hours'] * 60 * 60;
				}

				if ( $last_alert['created_on'] < ( time() - $idle_time ) ) {
					// User is idle, clear their session from everywhere.
					User_Sessions::delete_session( $session['user_id'], $session['session_token'] );

					$user_data = get_userdata( $user->ID );
					$this->plugin->alerts->trigger_event(
						1009,
						array(
							'TargetUserRole' => is_array( $user_data->roles ) ? implode( ', ', $user_data->roles ) : $user_data->roles,
							'username'       => $user->user_login,
							'SessionID'      => $session['session_token'],
						)
					);
				}
			}
		}
	}

	/**
	 * Get last user event.
	 *
	 * @method get_last_user_alert
	 * @param string  $value   - User login name.
	 * @param string  $session - User session.
	 * @param integer $blog_id - Blog ID.
	 *
	 * @return WSAL_Models_Occurrence|false
	 */
	public function get_last_user_alert( $value, $session, $blog_id = 0 ) {
		// Get latest alert via session id.
		$query  = $this->get_user_alerts_by_session_query( $session, $blog_id );
		$result = $this->execute_query( $query );

		return ( ! empty( $result ) && is_array( $result ) ) ? $result[0] : false;
	}

	/**
	 * Get user alerts query by session id.
	 *
	 * NOTE: when this is used it gets more params attached for ordering and
	 * to only get 1 item.
	 *
	 * @method get_user_alerts_by_session_query
	 * @param string  $session a string with session id.
	 * @param integer $blog_id a site id if we have one to check against.
	 *
	 * @return WSAL_Models_OccurrenceQuery
	 * @since  4.0.3
	 */
	public function get_user_alerts_by_session_query( $session = '', $blog_id = 0 ) {
		// Setup the sql for the query.
		$query = new WSAL_Models_OccurrenceQuery();
		$sql   = " session_id = %s "; // @codingStandardsIgnoreLine
		$query->add_or_condition( array( $sql => $session ) );

		// if we have a blog id then add it.
		if ( $blog_id ) {
			$query->add_condition( 'site_id = %s ', $blog_id );
		}

		// return the query object.
		return $query;
	}

	/**
	 * Adds params to the query args to order all items by 'created_on' date
	 * and then return only the first item.
	 *
	 * @method execute_query
	 * @param WSAL_Models_OccurrenceQuery $query Query object filled with args.
	 *
	 * @return WSAL_Models_Occurrence Occurrence object.
	 * @since  4.1.0
	 */
	private function execute_query( $query ) {
		$query->add_order_by( 'created_on', true );
		$query->set_limit( 1 );

		return $query->get_adapter()->execute_query( $query );
	}

	/**
	 * Hooked as an action on the cleanup cron for cleaning up old WordPress
	 * core session data when session has passed expiry time.
	 *
	 * @method core_sessions_cleanup
	 * @since  4.1.0
	 */
	public function expired_sessions_cleanup() {
		// Get the expired sessions.
		$expired_sessions = User_Sessions::get_all_expired_sessions();
		User_Sessions::delete_sessions( $expired_sessions );

		// Users can optionally flush expired sessions from core directly as
		// well. Only if enabled in plugin settings.
		if ( WSAL_UserSessions_Helpers::is_core_session_cleanup_enabled() ) {
			$this->delete_expired_sessions_using_user_meta();
		}
	}

	/**
	 * Deletes expired user sessions using the session tokens stored in user metadata as a starting point.
	 */
	protected function delete_expired_sessions_using_user_meta() {
		// Get the full list of users that have sessions, we only need the ID field..
		$users_query = new WP_User_Query(
			array(
				'blog_id'      => 0, // whole network if we are multisite.
				'meta_key'     => 'session_tokens', // @codingStandardsIgnoreLine
				'meta_compare' => 'EXISTS',
				'fields'       => 'ID',
			)
		);

		/*
		 * If we have users to work with then loop through them fetching
		 * all the sessions and checking if they have expired.
		 */
		if ( isset( $users_query->results ) && ! empty( $users_query->results ) ) {
			$users = $users_query->results;
			foreach ( $users as $user_id ) {
				$sessions = User_Sessions::get_user_session_tokens( $user_id );
				// if user has sessions loop through them checking expiry.
				if ( ! empty( $sessions ) ) {
					foreach ( $sessions as $token => $session_data ) {
						if ( $session_data['expiration'] < time() ) {
							// This session has expired, delete it from everywhere.
							User_Sessions::delete_session( $user_id, $token );
						}
					}
				}
			}
		}
	}

	/**
	 * Get a query to fetch user alerts by user_id.
	 *
	 * NOTE: when this is used it gets more params attached for ordering and
	 * to only get 1 item.
	 *
	 * @method get_user_alerts_by_user_query
	 *
	 * @param integer $user_id   a user id.
	 * @param string  $user_name username of the user.
	 * @param integer $blog_id   the blog id if we have one to check against.
	 *
	 * @return WSAL_Models_OccurrenceQuery
	 * @since  4.0.3
	 */
	public function get_user_alerts_by_user_query( $user_id = - 1, $user_name = '', $blog_id = 0 ) {

		// Get DB connection array.
		$connection = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_connection();

		// Tables.
		$meta       = new WSAL_Adapters_MySQL_Meta( $connection );
		$table_meta = $meta->get_table(); // Metadata.
		$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
		$table_occ  = $occurrence->get_table(); // Occurrences.

		// set the sql for the query.
		$query = new WSAL_Models_OccurrenceQuery();
		$sql   = "$table_occ.id IN ( SELECT occurrence_id FROM $table_meta as meta WHERE ";
		$sql  .= "( meta.name='CurrentUserID' AND find_in_set(meta.value, '$user_id') > 0 ) ";
		$sql  .= 'OR ';
		$sql  .= "( meta.name='Username' AND find_in_set(meta.value, %s) > 0 ) )";
		$query->add_or_condition( array( $sql => $user_name ) );

		// if we have a blog id then add it.
		if ( $blog_id ) {
			$query->add_condition( 'site_id = %s ', $blog_id );
		}

		// return the query object.
		return $query;
	}
}
