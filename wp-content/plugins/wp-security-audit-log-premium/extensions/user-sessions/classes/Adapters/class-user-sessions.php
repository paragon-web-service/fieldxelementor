<?php
/**
 * Adapter: User Session.
 *
 * User Sessions class.
 *
 * @package wsal
 */

declare(strict_types=1);

namespace WSAL\Adapter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Adapter\User_Sessions' ) ) {

	/**
	 * Responsible for the user sessions.
	 */
	class User_Sessions {

		/**
		 * Maximum session token length
		 */
		const SESSION_TOKEN_MAX_LENGTH = 128;
		/**
		 * Maximum ip address length
		 */
		const IP_MAX_LENGTH = 45;
		/**
		 * Maximum user roles length
		 */
		const MAXIMUM_ROLES_LENGTH = 128;

		/**
		 * Holds the DB connection (for caching purposes)
		 *
		 * @var [type]
		 *
		 * @since 4.4.2.1
		 */
		private static $connection = null;

		/**
		 * Contains the table name.
		 *
		 * @var string
		 */
		private static $table = 'wsal_sessions';

		/**
		 * Contains primary key column name, override as required.
		 *
		 * @var string
		 */
		private static $idkey = 'session_token';

		/**
		 * Field to hold the user ID.
		 *
		 * @var integer
		 */
		private static $user_id = 0;

		/**
		 * Field to hold the session token.
		 *
		 * @var string
		 */
		private static $session_token = '';

		/**
		 * Field to store the session creation time.
		 *
		 * @var int
		 */
		private static $creation_time = 0;

		/**
		 * Field to store the session expiry time.
		 *
		 * @var int
		 */
		private static $expiry_time = 0;

		/**
		 * Field to store the user IP.
		 *
		 * @var string
		 */
		private static $ip = '';

		/**
		 * Field to store the user roles.
		 *
		 * @var array
		 */
		private static $roles = array();

		/**
		 * Field to store the sites.
		 *
		 * @var string
		 */
		private static $sites = '';

		/**
		 * Deletes given sessions as well as all associated data, for example session token stored in user meta by
		 * WordPress.
		 *
		 * @param array $sessions_data - An array of session data as returned from the database.
		 */
		public static function delete_sessions( $sessions_data ) {
			if ( ! is_array( $sessions_data ) || empty( $sessions_data ) ) {
				return;
			}

			foreach ( $sessions_data as $expired_session ) {
				self::delete_session( $expired_session['user_id'], $expired_session['session_token'] );
			}
		}

		/**
		 * Deletes a session identified by user ID and a token as well as all associated data, for example session token
		 * stored in user meta by WordPress.
		 *
		 * @param int    $user_id    User ID.
		 * @param string $token_hash Session token hash.
		 */
		public static function delete_session( $user_id, $token_hash ) {

			// Purge from WordPress session tokens in user meta.
			$user_sessions = self::get_user_session_tokens( $user_id );
			if ( array_key_exists( $token_hash, $user_sessions ) ) {
				unset( $user_sessions[ $token_hash ] );
				\update_user_meta( $user_id, 'session_tokens', $user_sessions );
			}

			// Purge from our custom table.
			self::delete_by_session_token( $token_hash );
		}

		/**
		 * Helper function to safely load user sessions token from user meta.
		 *
		 * Handles missing data as well as an empty string or serialized data.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return string[]
		 * @since 4.1.3
		 */
		public static function get_user_session_tokens( $user_id ) {
			$session_tokens = \get_user_meta( $user_id, 'session_tokens', true );
			if ( false === $session_tokens || '' === $session_tokens ) {
				$session_tokens = array();
			}

			if ( is_string( $session_tokens ) ) {
				$session_tokens = maybe_unserialize( $session_tokens );
			}

			return $session_tokens;
		}

		/**
		 * Deletes by session token. Token could also be array with tokens.
		 *
		 * @param array|string $session_token - The session token(s).
		 *
		 * @return void
		 *
		 * @since 4.4.2.1
		 */
		public static function delete_by_session_token( $session_token ) {

			if ( is_array( $session_token ) ) {
				self::delete_by_session_tokens( $session_token );
			}

			if ( ! empty( $session_token ) ) {
				$sql = 'DELETE FROM `' . self::get_connection()->base_prefix . self::$table . '` WHERE `session_token` = "' . $session_token . '"';
				// Execute query.
				self::delete_query( $sql );
			}
		}

		/**
		 * Deletes by session tokens
		 *
		 * @param array $session_tokens - The array of session tokens to delete.
		 *
		 * @return void
		 *
		 * @since 4.4.2.1
		 */
		public static function delete_by_session_tokens( array $session_tokens = array() ) {
			if ( is_array( $session_tokens ) && ! empty( $session_tokens ) ) {
				$sql = 'DELETE FROM `' . self::get_connection()->base_prefix . self::$table . '` WHERE `session_token` IN ("' . implode( '", "', $session_tokens ) . '")';
				// Execute query.
				self::delete_query( $sql );
			}
		}

		/**
		 * Deletes records by given array with IDs
		 *
		 * @param array $user_ids - The user IDs to delete.
		 *
		 * @return integer
		 *
		 * @since 4.4.2.1
		 */
		public static function delete_by_user_ids( array $user_ids = array() ): int {
			if ( ! empty( $user_ids ) ) {
				$sql = 'DELETE FROM `' . self::get_connection()->base_prefix . self::$table . '` WHERE `user_id` IN (' . implode( ',', $user_ids ) . ')';
				// Execute query.
				return intval( self::delete_query( $sql ) );
			}

			return 0;
		}

		/**
		 * Loads all the sessions that have exceeded the expiry time.
		 *
		 * @method get_all_expired_sessions
		 * @since  4.1.0
		 * @return array
		 */
		public static function get_all_expired_sessions() {
			return self::load_array( 'expiry_time < %d', array( time() ) );
		}


		/**
		 * Load records from DB (Multi rows).
		 *
		 * @param string $cond Load condition.
		 * @param array  $args (Optional) Load condition arguments.
		 *
		 * @return array
		 */
		public static function load_array( $cond, $args = array() ) {
			$_wpdb  = self::get_connection();
			$result = array();
			$sql    = $_wpdb->prepare( 'SELECT * FROM ' . self::get_connection()->base_prefix . self::$table . ' WHERE ' . $cond, $args );

			$_wpdb->suppress_errors( true );

			$result_data = $_wpdb->get_results( $sql, ARRAY_A );
			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$result_data = $_wpdb->get_results( $sql, ARRAY_A );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			foreach ( $result_data as $data ) {
				$result[] = self::load_data( $data );
			}

			return $result;
		}

		/**
		 * Delete records in DB matching a query.
		 *
		 * @param string $query Full SQL query.
		 * @param array  $args  (Optional) Query arguments.
		 *
		 * @return int|bool
		 */
		public static function delete_query( $query, $args = array() ) {
			$_wpdb = self::get_connection();
			$sql   = count( $args ) ? $_wpdb->prepare( $query, $args ) : $query;

			$_wpdb->suppress_errors( true );

			$data = $_wpdb->query( $sql );
			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$data = $_wpdb->query( $sql );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			return $data;
		}

		/**
		 * Load by user ID.
		 *
		 * @param int $site_id Optional parameter to allow filtering by site ID.
		 *
		 * @return WSAL_Models_Session[]
		 */
		public static function load_all_sessions_ordered_by_user_id( $site_id = 0 ) {
			return self::load_array_ordered_by( 'user_id', 'ASC', $site_id );
		}

		/**
		 * Loads IP addresses of sessions associated with a given user. Possibly filtered by site ID and not including
		 * a selected session entry.
		 *
		 * @param int    $user_id User ID.
		 * @param int    $site_id Optional parameter to allow filtering by site ID.
		 * @param string $exempt_session_token Session token to be excluded from the results.
		 *
		 * @return string[]
		 * @since  4.1.4
		 */
		public static function load_user_ip_addresses( $user_id, $site_id = 0, $exempt_session_token = '' ) {
			$_wpdb        = self::get_connection();
			$query        = 'SELECT DISTINCT(ip) FROM ' . self::get_connection()->base_prefix . self::$table . ' WHERE user_id = %d ';
			$replacements = array( $user_id );
			if ( $site_id > 0 ) {
				$query .= ' AND sites = "all" OR FIND_IN_SET(%d, sites) > 0 ';
				array_push( $replacements, $site_id );
			}
			if ( ! empty( $exempt_session_token ) ) {
				$query .= ' AND session_token != "%s" ';
				array_push( $replacements, $exempt_session_token );
			}

			$prepared_query = $_wpdb->prepare( $query, $replacements );

			return $_wpdb->get_col( $prepared_query );
		}

		/**
		 * Count users query
		 *
		 * @param int   $site_id Optional parameter to allow filtering by site ID.
		 * @param array $excluded_users Users to exclude array.
		 *
		 * @return string
		 * @since  latest
		 */
		public static function get_users_count( $site_id = 0, $excluded_users = array() ) {
			$_wpdb        = self::get_connection();
			$query        = 'SELECT COUNT(session_token) FROM ' . self::get_connection()->base_prefix . self::$table . ' WHERE 1 ';
			$replacements = array();
			if ( $site_id > 0 ) {
				$query .= ' AND sites = "all" OR FIND_IN_SET(%d, sites) > 0 ';
				array_push( $replacements, $site_id );
			}
			if ( ! empty( $excluded_users ) ) {
				$query .= ' AND user_id NOT IN ("%s")';
				array_push( $replacements, implode( ',', $excluded_users ) );
			}

			$prepared_query = ( empty( $replacements ) ) ? $query : $_wpdb->prepare( $query, $replacements );

			$_wpdb->suppress_errors( true );

			$data = $_wpdb->get_col( $prepared_query );
			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$data = $_wpdb->get_col( $prepared_query );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			return $data[0];
		}

		/**
		 * Tries to retrieve an array of sessions ordered by the filed passed.
		 *
		 * @method load_array_ordered_by
		 * @param string $ordered_by the field to order by.
		 * @param string $order      the direction to order - either ASC or DESC.
		 * @param int    $site_id    Optional parameter to allow filtering by site ID.
		 *
		 * @return WSAL_Models_Session[]
		 * @since  4.1.0
		 */
		public static function load_array_ordered_by( $ordered_by = 'user_id', $order = 'ASC', $site_id = 0 ) {
			// ensure we have a correct order string.
			if ( 'ASC' !== $order || 'DESC' !== $order ) {
				$order = 'ASC';
			}
			$_wpdb        = self::get_connection();
			$result       = array();
			$query        = 'SELECT * FROM ' . self::get_connection()->base_prefix . self::$table;
			$replacements = array();
			if ( $site_id > 0 ) {
				$query .= ' WHERE sites = "all" OR FIND_IN_SET(%s, sites) > 0 ';
				array_push( $replacements, $site_id );
			}
			$query .= ' ORDER BY %s ' . $order;
			array_push( $replacements, $ordered_by );

			$prepared_query = $_wpdb->prepare( $query, $replacements );

			$_wpdb->suppress_errors( true );

			$data_sessions = $_wpdb->get_results( $prepared_query, ARRAY_A );
			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$data_sessions = $_wpdb->get_results( $prepared_query, ARRAY_A );
					}
				}
			}
			$_wpdb->suppress_errors( false );
			foreach ( $data_sessions as $data ) {
				$result[] = self::load_data( $data );
			}
			return $result;
		}

		/**
		 * Load object data from variable.
		 *
		 * @param array|object $data Data array or object.
		 * @throws \Exception - Unsupported type.
		 */
		public static function load_data( $data ) {
			foreach ( (array) $data as $key => $val ) {
				$data[ $key ] = self::cast_to_correct_type( $key, $val );
			}
			return $data;
		}

		/**
		 * Saves record in the table
		 *
		 * @param array $active_record - An array with all the user data to insert.
		 *
		 * @return int|false
		 *
		 * @since 4.4.2.1
		 */
		public static function save( array $active_record ) {
			$_wpdb  = self::get_connection();
			$format = array( '%d', '%s', '%d', '%d', '%s', '%s', '%s' );
			$data   = array(
				'user_id'       => $active_record['user_id'],
				'session_token' => $active_record['session_token'],
				'creation_time' => $active_record['creation_time'],
				'expiry_time'   => $active_record['expiry_time'],
				'ip'            => $active_record['ip'],
				'roles'         => json_encode( $active_record['roles'] ),
				'sites'         => json_encode( $active_record['sites'] ),
			);

			$_wpdb->suppress_errors( true );

			$result = $_wpdb->replace( self::get_connection()->base_prefix . self::$table, $data, $format );

			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$result = $_wpdb->replace( self::get_connection()->base_prefix . self::$table, $data, $format );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			return $result;
		}

		/**
		 * Creates table functionality
		 *
		 * @return bool
		 *
		 * @since 4.4.2.1
		 */
		public static function create_table(): bool {
			$table_name    = self::get_connection()->base_prefix . self::$table;
			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					`user_id` bigint NOT NULL,
					`session_token` VARCHAR(' . (int) self::SESSION_TOKEN_MAX_LENGTH . ') NOT NULL,' . PHP_EOL . '
					`creation_time` bigint NOT NULL,
					`expiry_time` bigint NOT NULL,
					`ip` VARCHAR(' . (int) self::IP_MAX_LENGTH . ') NOT NULL,' . PHP_EOL . '
					`roles` longtext NOT NULL,
					`sites` longtext NOT NULL,
				  PRIMARY KEY (`session_token`)
				)
			  ' . self::get_connection()->get_charset_collate() . ';';

			return self::maybe_create_table( $table_name, $wp_entity_sql );
		}

		/**
		 * Returns the current connection (used by the plugin)
		 *
		 * @return \WPDB @see \WSAL_Connector_MySQLDB
		 *
		 * @since 4.4.2.1
		 */
		public static function get_connection() {
			if ( null === self::$connection ) {
				$custom_mysql     = \WSAL_Connector_ConnectorFactory::get_default_connector();
				self::$connection = $custom_mysql->get_connection();
			}
			return self::$connection;
		}

		/**
		 * Checks if the table needs to be recreated / created
		 *
		 * @param string $table_name - The name of the table to check for.
		 * @param string $create_ddl - The create table syntax.
		 *
		 * @return bool
		 *
		 * @since 4.4.2.1
		 */
		public static function maybe_create_table( string $table_name, string $create_ddl ): bool {
			foreach ( self::get_connection()->get_col( 'SHOW TABLES', 0 ) as $table ) {
				if ( $table === $table_name ) {
					return true;
				}
			}
			// Didn't find it, so try to create it.
			self::get_connection()->query( $create_ddl );

			// We cannot directly tell that whether this succeeded!
			foreach ( self::get_connection()->get_col( 'SHOW TABLES', 0 ) as $table ) {
				if ( $table === $table_name ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Casts given value to a correct type based on the type of property (identified by the $key) in the $copy object.
		 * This is to allow automatic type casting instead of handling each database column individually.
		 *
		 * @param string $key  Column key.
		 * @param mixed  $val  Value.
		 *
		 * @return mixed
		 * @throws \Exception - Unsupported type of data.
		 */
		protected static function cast_to_correct_type( $key, $val ) {
			switch ( true ) {
				case is_string( $key ):
				case \WSAL_Utilities_RequestUtils::is_ip_address( $val ):
					return (string) $val;
				case is_array( $key ):
				case is_object( $key ):
					$json_decoded_val = \WSAL_Helpers_DataHelper::json_decode( $val );
					return is_null( $json_decoded_val ) ? $val : $json_decoded_val;
				case is_int( $key ):
					return (int) $val;
				case is_float( $key ):
					return (float) $val;
				case is_bool( $key ):
					return (bool) $val;
				default:
					throw new \Exception( 'Unsupported type "' . gettype( $key ) . '"' );
			}
		}

		/**
		 * Checks and returns last mysql error
		 *
		 * @param \WPDB $_wpdb - The Mysql resource class.
		 *
		 * @return integer
		 *
		 * @since 4.4.2.1
		 */
		private static function get_last_sql_error( $_wpdb ): int {
			$code = 0;
			if ( $_wpdb->dbh instanceof \mysqli ) {
				$code = \mysqli_errno( $_wpdb->dbh ); // phpcs:ignore
			}

			if ( is_resource( $_wpdb->dbh ) ) {
				// Please do not report this code as a PHP 7 incompatibility. Observe the surrounding logic.
				// phpcs:ignore
				$code = mysql_errno( $_wpdb->dbh );
			}
			return $code;
		}
	}
}
