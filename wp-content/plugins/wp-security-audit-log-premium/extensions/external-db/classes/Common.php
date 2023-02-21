<?php
/**
 * Class: Utility Class
 *
 * Utility class for common function.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage external-db
 */

use WSAL\Helpers\Classes_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_Ext_Common
 *
 * Utility class, used for all the common functions used in the plugin.
 *
 * @package    wsal
 * @subpackage external-db
 */
class WSAL_Ext_Common {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	public $wsal = null;

	/**
	 * Archive DB Connection Object.
	 *
	 * @var object
	 */
	protected static $archive_db = null;

	/**
	 * External DB extension.
	 *
	 * @var WSAL_Ext_Plugin
	 * @since 4.3.0
	 */
	private $extension;

	/**
	 * Holds the extension base URL.
	 *
	 * @var string
	 *
	 * @since 4.4.3.2
	 */
	private static $extension_base_url = null;

	/**
	 * Local cache for mirror types.
	 *
	 * @var array
	 */
	private static $mirror_types;

	/**
	 * Monolog helper.
	 *
	 * @var WSAL_Ext_MonologHelper
	 */
	private $monolog_helper;

	/**
	 * Method: Constructor
	 *
	 * @param WpSecurityAuditLog $wsal      - Instance of WpSecurityAuditLog.
	 * @param WSAL_Ext_Plugin    $extension Instance of external db extension.
	 */
	public function __construct( $wsal, $extension ) {
		$this->wsal      = $wsal;
		$this->extension = $extension;
	}

	/**
	 * Set the setting by name with the given value.
	 *
	 * @param string $option   Option name.
	 * @param mixed  $value    Value.
	 * @param bool   $autoload Whether  to autoload this option.
	 */
	public function add_global_setting( $option, $value, $autoload = false ) {
		\WSAL\Helpers\Settings_Helper::set_option_value( $option, $value, $autoload );
	}

	/**
	 * Update the setting by name with the given value.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $value - Value.
	 * @return boolean
	 */
	public function update_global_setting( $option, $value ) {
		return \WSAL\Helpers\Settings_Helper::set_option_value( $option, $value );
	}

	/**
	 * Delete setting by name.
	 *
	 * @param string $option - Option name.
	 *
	 * @return boolean result
     * @deprecated 4.4.3 - Use \WSAL\Helpers\Settings_Helper::delete_option_value()
     */
	public function delete_global_setting( $option ) {
		_deprecated_function( __FUNCTION__, '4.4.3', '\WSAL\Helpers\Settings_Helper::delete_option_value()' );

		return \WSAL\Helpers\Settings_Helper::delete_option_value( $option );
	}

	/**
	 * Get setting by name.
	 *
	 * @param string $option - Option name.
	 * @param mixed  $default - Default value.
	 * @return mixed value
	 */
	public function get_setting_by_name( $option, $default = false ) {
		return \WSAL\Helpers\Settings_Helper::get_option_value( $option, $default );
	}

	/**
	 * Encrypt password, before saves it to the DB.
	 *
	 * @param string $data - Original text.
	 * @return string - Encrypted text
	 */
	public function encrypt_password( $data ) {
		return $this->wsal->get_connector()->encrypt_string( $data );
	}

	/**
	 * Decrypt password, after reads it from the DB.
	 *
	 * @param string $ciphertext_base64 - Encrypted text.
	 * @return string - Original text.
	 */
	public function decrypt_password( $ciphertext_base64 ) {
		return $this->wsal->get_connector()->decrypt_string( $ciphertext_base64 );
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

		return self::get_url_for_db( $name );
	}

	/**
	 * Method: Return URL based prefix for DB.
	 *
	 * @return string - URL based prefix.
	 *
	 * @param string $name - Name of the DB type.
	 */
	public static function get_url_for_db( $name = '' ) {
		// Get home URL.
		$home_url  = get_home_url();
		$protocols = array( 'http://', 'https://' ); // URL protocols.
		$home_url  = str_replace( $protocols, '', $home_url ); // Replace URL protocols.
		$home_url  = str_replace( array( '.', '-' ), '_', $home_url ); // Replace `.` with `_` in the URL.

		// Concat name of the DB type at the end.
		if ( ! empty( $name ) ) {
			$home_url .= '_';
			$home_url .= $name;
			$home_url .= '_';
		} else {
			$home_url .= '_';
		}

		// Return the prefix.
		return $home_url;
	}

	/**
	 * Creates a connection and returns it
	 *
	 * @param array $connection_config - Array of connection configurations.
	 * @return wpdb Instance of WPDB
	 */
	private function create_connection( $connection_config ) {
		$password = $this->decrypt_password( $connection_config['password'] );
		$new_wpdb = new wpdbCustom( $connection_config['user'], $password, $connection_config['db_name'], $connection_config['hostname'], $connection_config['is_ssl'], $connection_config['is_cc'], $connection_config['ssl_ca'], $connection_config['ssl_cert'], $connection_config['ssl_key'] );
		if ( array_key_exists( 'baseprefix', $connection_config ) ) {
			$new_wpdb->set_prefix( $connection_config['baseprefix'] );
		}
		return $new_wpdb;
	}

	/*============================== External Database functions ==============================*/

	/**
	 * Migrate to external database.
	 *
	 * @param string $connection_name External connection name.
	 * @param int    $limit           - Limit.
	 *
	 * @return int
	 */
	public function migrate_occurrence( $connection_name, $limit ) {
		$db_connection = $this->wsal->external_db_util->get_connection( $connection_name );
		return $this->wsal->get_connector( $db_connection )->migrate_occurrence_from_local_to_external( $limit );
	}

	/**
	 * Migrate back to WP database
	 *
	 * @param int $limit - Limit.
	 *
	 * @return int
	 */
	public function migrate_back_occurrence( $limit ) {
		return $this->wsal->get_connector()->migrate_occurrence_from_external_to_local( $limit );
	}

	/**
	 * Checks if the necessary tables are available.
	 *
	 * @return bool true|false
	 */
	public function is_installed() {
		return $this->wsal->get_connector()->is_installed();
	}

	/**
	 * Remove External DB config.
	 */
	public function remove_external_storage_config() {
		// Get archive connection.
		$adapter_conn_name = $this->get_setting_by_name( 'adapter-connection' );
		if ( $adapter_conn_name ) {
			$adapter_connection             = $this->get_connection( $adapter_conn_name );
			$adapter_connection['used_for'] = '';
			$this->save_connection( $adapter_connection );
		}

		\WSAL\Helpers\Settings_Helper::delete_option_value( 'adapter-connection' );
	}

	/**
	 * Recreate DB tables on WP.
	 */
	public function recreate_tables() {
		$occurrence = new WSAL_Models_Occurrence();
		$occurrence->get_adapter()->install_original();
		$meta = new WSAL_Models_Meta();
		$meta->get_adapter()->install_original();
	}

	/*============================== Mirroring functions ==============================*/

	/*============================== Archiving functions ==============================*/

	/**
	 * Enable/Disable archiving.
	 *
	 * @param bool $enabled - Value.
	 */
	public function set_archiving_enabled( $enabled ) {
		$this->add_global_setting( 'archiving-e', $enabled );
		if ( empty( $enabled ) ) {
			$this->remove_archiving_config();
			\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-last-created' );
		}
	}

	/**
	 * Get archiving date.
	 *
	 * @return int value
	 */
	public function get_archiving_date() {
		return (int) $this->get_setting_by_name( 'archiving-date', 1 );
	}

	/**
	 * Set archiving date.
	 *
	 * @param string $newvalue - New value.
	 */
	public function set_archiving_date( $newvalue ) {
		$this->add_global_setting( 'archiving-date', (int) $newvalue );
	}

	/**
	 * Get archiving date type.
	 *
	 * @return string value
	 */
	public function get_archiving_date_type() {
		return $this->get_setting_by_name( 'archiving-date-type', 'months' );
	}

	/**
	 * Set archiving date type.
	 *
	 * @param string $newvalue - New value.
	 */
	public function set_archiving_date_type( $newvalue ) {
		$this->add_global_setting( 'archiving-date-type', $newvalue );
	}

	/**
	 * Get archiving frequency.
	 *
	 * @return string frequency
	 */
	public function get_archiving_frequency() {
		return $this->get_setting_by_name( 'archiving-run-every', 'hourly' );
	}

	/**
	 * Set archiving frequency.
	 *
	 * @param string $newvalue - New value.
	 */
	public function set_archiving_run_every( $newvalue ) {
		$this->add_global_setting( 'archiving-run-every', $newvalue );
	}

	/**
	 * Enable/Disable archiving stop.
	 *
	 * @param bool $enabled - Value.
	 */
	public function set_archiving_stop( $enabled ) {
		$this->add_global_setting( 'archiving-stop', $enabled );
	}

	/**
	 * Remove the archiving config.
	 */
	public function remove_archiving_config() {
		// Get archive connection.
		$archive_conn_name = $this->get_setting_by_name( 'archive-connection' );

		if ( $archive_conn_name ) {
			$archive_connection             = $this->get_connection( $archive_conn_name );
			$archive_connection['used_for'] = '';
			$this->save_connection( $archive_connection );
		}

		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archive-connection' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-date' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-date-type' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-run-every' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-daily-e' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-weekly-e' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-week-day' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-time' );
		\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-stop' );
	}

	/**
	 * Disable the pruning config.
	 */
	public function disable_pruning() {
		\WSAL\Helpers\Settings_Helper::set_boolean_option_value( 'pruning-date-e', false );
		\WSAL\Helpers\Settings_Helper::set_boolean_option_value( 'pruning-limit-e', false );
	}

	/**
	 * Archive alerts (Occurrences table)
	 *
	 * @param array $args - Arguments array.
	 *
	 * @return array|false|null
	 */
	public function archive_occurrence( $args ) {
		$args['archive_db'] = $this->archive_database_connection();
		if ( empty( $args['archive_db'] ) ) {
			return false;
		}
		$last_created_on = $this->get_setting_by_name( 'archiving-last-created' );
		if ( ! empty( $last_created_on ) ) {
			$args['last_created_on'] = $last_created_on;
		}
		return $this->wsal->get_connector()->archive_occurrence( $args );
	}

	/**
	 * Archive alerts (Metadata table)
	 *
	 * @param array $args - Arguments array.
	 *
	 * @return array|false|null
	 */
	public function archive_meta( $args ) {
		$args['archive_db'] = $this->archive_database_connection();
		return $this->wsal->get_connector()->archive_meta( $args );
	}

	/**
	 * Delete alerts from the source tables
	 * after archiving them.
	 *
	 * @param array $args - Arguments array.
	 */
	public function delete_after_archive( $args ) {
		$args['archive_db'] = $this->archive_database_connection();
		$this->wsal->get_connector()->delete_after_archive( $args );
		if ( ! empty( $args['last_created_on'] ) ) {
			// Update last_created.
			$this->add_global_setting( 'archiving-last-created', $args['last_created_on'] );
		}
	}

	/**
	 * Check if archiving cron job started.
	 *
	 * @return bool
	 */
	public function is_archiving_cron_started() {
		return \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'archiving-cron-started', false );
	}

	/**
	 * Enable/Disable archiving cron job started option.
	 *
	 * @param bool $value - Value.
	 */
	public function set_archiving_cron_started( $value ) {
		if ( ! empty( $value ) ) {
			$this->add_global_setting( 'archiving-cron-started', 1 );
		} else {
			\WSAL\Helpers\Settings_Helper::delete_option_value( 'archiving-cron-started' );
		}
	}

	/**
	 * Archiving alerts.
	 */
	public function archiving_alerts() {
		if ( ! $this->is_archiving_cron_started() ) {
			set_time_limit( 0 );
			// Start archiving.
			$this->set_archiving_cron_started( true );

			$args          = array();
			$args['limit'] = 100;
			$args_result   = false;

			do {
				$num             = $this->get_archiving_date();
				$type            = $this->get_archiving_date_type();
				$now             = current_time( 'timestamp' );
				$args['by_date'] = strtotime( '-' . $num . ' ' . $type, $now );
				$args_result     = $this->archive_occurrence( $args );
				if ( ! empty( $args_result ) ) {
					$this->archive_meta( $args_result );
				}
				if ( ! empty( $args_result ) ) {
					$this->delete_after_archive( $args_result );
				}
			} while ( false !== $args_result );

			// End archiving.
			$this->set_archiving_cron_started( false );
		}
	}

	/**
	 * Get the Archive connection
	 *
	 * @return wpdb Instance of WPDB
	 */
	private function archive_database_connection() {
		if ( ! empty( self::$archive_db ) ) {
			return self::$archive_db;
		} else {
			$connection_config = $this->get_archive_config();
			if ( empty( $connection_config ) ) {
				return null;
			} else {
				// Get archive DB connection.
				self::$archive_db = $this->create_connection( $connection_config );

				// Check object for disconnection or other errors.
				$connected = true;
				if ( isset( self::$archive_db->dbh->errno ) ) {
					$connected = ! ( 0 !== (int) self::$archive_db->dbh->errno ); // Database connection error check.
				} elseif ( is_wp_error( self::$archive_db->error ) ) {
					$connected = false;
				}

				if ( $connected ) {
					return self::$archive_db;
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * Get the Archive config
	 *
	 * @return array|null config
	 */
	private function get_archive_config() {
		$connection_name = $this->get_setting_by_name( 'archive-connection' );
		if ( empty( $connection_name ) ) {
			return null;
		}

		$connection = $this->get_connection( $connection_name );
		if ( ! is_array( $connection ) ) {
			return null;
		}

		return $connection;
	}

	/**
	 * Return Connection Object.
	 *
	 * @param string $connection_name - Connection name.
	 *
	 * @return array|bool
	 * @since 3.3
	 */
	public function get_connection( $connection_name ) {
		if ( empty( $connection_name ) ) {
			return false;
		}
		$result_raw = $this->get_setting_by_name( WSAL_CONN_PREFIX . $connection_name );
		$result     = maybe_unserialize( $result_raw );

		return ( $result instanceof stdClass ) ? json_decode( json_encode( $result ), true ) : $result; // phpcs:ignore
	}

	/**
	 * Set Connection Object.
	 *
	 * @since 3.3
	 *
	 * @param array|stdClass $connection - Connection object.
	 */
	public function save_connection( $connection ) {
		// Stop here if no connection provided.
		if ( empty( $connection ) ) {
			return;
		}

		$connection_name = ( $connection instanceof stdClass ) ? $connection->name : $connection['name'];

		$this->add_global_setting( WSAL_CONN_PREFIX . $connection_name, $connection );
	}

	/**
	 * Delete Connection Object.
	 *
	 * @param string $connection_name - Connection name.
	 *
	 * @since 4.3.0
	 */
	public function delete_connection( $connection_name ) {
		$connection = $this->get_connection( $connection_name );

		if ( is_array( $connection ) && array_key_exists( 'type', $connection ) ) {
			$this->wsal->alerts->trigger_event_if(
				6320,
				array(
					'EventType' => 'deleted',
					'type'      => $connection['type'],
					'name'      => $connection_name,
				),
				array( $this, 'skip_if_updating' )
			);
		}

		\WSAL\Helpers\Settings_Helper::delete_option_value( WSAL_CONN_PREFIX . $connection_name );
	}

	/**
	 * Checks if event 6321 will fire to avoid the wrong reporting.
	 *
	 * @param WSAL_AlertManager $mgr Alert manager.
	 * @return bool
	 */
	public function skip_if_updating( WSAL_AlertManager $mgr ) {
		return ! $mgr->will_trigger( 6321 );
	}

	/**
	 * Return Mirror Object.
	 *
	 * @since 3.3
	 *
	 * @param string $mirror_name - Mirror name.
	 * @return array|bool
	 */
	public function get_mirror( $mirror_name ) {
		if ( empty( $mirror_name ) ) {
			return false;
		}
		$result_raw = $this->get_setting_by_name( WSAL_MIRROR_PREFIX . $mirror_name );
		$result     = maybe_unserialize( $result_raw );
		return ( $result instanceof stdClass ) ? json_decode( json_encode( $result ), true ) : $result; // phpcs:ignore
	}

	/**
	 * Set Mirror Object.
	 *
	 * @since 3.3
	 *
	 * @param array|stdClass $mirror Mirror data.
	 */
	public function save_mirror( $mirror ) {
		if ( empty( $mirror ) ) {
			return;
		}

		$mirror_name = ( $mirror instanceof stdClass ) ? $mirror->name : $mirror['name'];

		$old_value = \WSAL\Helpers\Settings_Helper::get_option_value( WSAL_MIRROR_PREFIX . $mirror_name );

		if ( ! isset( $old_value['state'] ) ) {
			$this->wsal->alerts->trigger_event(
				6323,
				array(
					'EventType'  => 'added',
					'connection' => ( $mirror instanceof stdClass ) ? $mirror->connection : $mirror['connection'],
					'name'       => $mirror_name,
				)
			);
		} elseif ( isset( $old_value['state'] ) && $old_value['state'] !== $mirror['state'] ) {
			$this->wsal->alerts->trigger_event(
				6325,
				array(
					'EventType'  => ( $mirror['state'] ) ? 'enabled' : 'disabled',
					'connection' => ( $mirror instanceof stdClass ) ? $mirror->connection : $mirror['connection'],
					'name'       => $mirror_name,
				)
			);
		} else {
			$this->wsal->alerts->trigger_event(
				6324,
				array(
					'EventType'  => 'modified',
					'connection' => ( $mirror instanceof stdClass ) ? $mirror->connection : $mirror['connection'],
					'name'       => $mirror_name,
				)
			);
		}

		$this->add_global_setting( WSAL_MIRROR_PREFIX . $mirror_name, $mirror );
	}

	/**
	 * Delete mirror.
	 *
	 * @param string $mirror_name - Mirror name.
	 *
	 * @since 4.3.0
	 */
	public function delete_mirror( $mirror_name ) {

		$this->wsal->alerts->trigger_event(
			6326,
			array(
				'EventType' => 'deleted',
				'name'      => $mirror_name,
			)
		);

		\WSAL\Helpers\Settings_Helper::delete_option_value( WSAL_MIRROR_PREFIX . $mirror_name );
	}

	/**
	 * Gets mirror types.
	 * TODO: Get rid of that code
	 *
	 * @return array List of mirror types.
	 *
	 * @since 4.3.0
	 */
	public function get_mirror_types() {
		if ( ! isset( self::$mirror_types ) ) {

			$mirrors = Classes_Helper::get_classes_by_namespace( 'WSAL\Extensions\ExternalDB\Mirrors' );

			$result = array();

			foreach ( $mirrors as $mirror ) {
				$result [ $mirror::get_type() ] = array(
					'name'   => $mirror::get_name(),
					'config' => $mirror::get_config_definition(),
					'class'  => $mirror,
				);
			}

			// $file_filter = $this->get_base_dir() . 'classes' . DIRECTORY_SEPARATOR . 'mirrors' . DIRECTORY_SEPARATOR . '*Connection.php';
			// foreach ( glob( $file_filter ) as $file ) {
			// $base_filename = basename( $file );
			// $class_name    = 'WSAL_Ext_Mirrors_' . substr( $base_filename, 0, strlen( $base_filename ) - 4 );
			// try {
			// require_once $file;
			// $result [ $class_name::get_type() ] = array(
			// 'name'   => $class_name::get_name(),
			// 'config' => $class_name::get_config_definition(),
			// 'class'  => $class_name,
			// );
			// } catch ( Exception $exception ) {  // phpcs:ignore
			// Skip unsuitable class.
			// TODO log to debug log.
			// }
			// }

			self::$mirror_types = $result;
		}

		return self::$mirror_types;
	}

	/**
	 * Gets the extension base URL.
	 *
	 * @return string
	 * @since 4.3.0
	 *
	 * @deprecated 4.4.3.2 - Use self::get_extension_base_url() function instead.
	 */
	public function get_base_url() {
		_deprecated_function( __FUNCTION__, '4.4.3.2', 'WSAL_Ext_Common::get_extension_base_url()' );

		return self::get_extension_base_url();
	}

	/**
	 * Returns the extension base URL directory.
	 *
	 * @return string
	 *
	 * @since 4.4.3.2
	 */
	public static function get_extension_base_url(): string {
		if ( null === self::$extension_base_url ) {
			self::$extension_base_url = trailingslashit( WSAL_BASE_URL ) . 'extensions/external-db/';
		}

		return self::$extension_base_url;
	}

	/**
	 * Finds all mirrors using a specific connection.
	 *
	 * @param string $connection_name Connection name.
	 *
	 * @return array[]
	 * @since 4.3.0
	 */
	public function get_mirrors_by_connection_name( $connection_name ) {
		$mirrors = \WSAL\Helpers\Settings_Helper::get_all_mirrors();
		$result  = array();
		if ( ! empty( $mirrors ) ) {
			foreach ( $mirrors as $mirror ) {
				if ( $connection_name === $mirror['connection'] ) {
					array_push( $result, $mirror );
				}
			}
		}

		return $result;
	}

	/**
	 * Gets the Monolog helper instance.
	 *
	 * @return WSAL_Ext_MonologHelper Monolog helper instance.
	 * @since 4.3.0
	 */
	public function get_monolog_helper() {
		if ( ! isset( $this->monolog_helper ) ) {
			$this->monolog_helper = new WSAL_Ext_MonologHelper( $this->wsal );
		}

		return $this->monolog_helper;
	}

	/**
	 * Checks if the necessary tables are available.
	 *
	 * @return bool true|false
	 */
	protected function check_if_table_exist() {
		return $this->is_installed();
	}

	/**
	 * Updates given connection to be used for external storage.
	 *
	 * @param string $connection_name Connection name.
	 * @since 4.3.2
	 */
	public function update_connection_as_external( $connection_name ) {
		// Set external storage to be used for logging events from now on.
		$db_connection = $this->get_connection( $connection_name );

		// Error handling.
		if ( empty( $db_connection ) ) {
			return false;
		}

		// Set connection's used_for attribute.
		$db_connection['used_for'] = __( 'External Storage', 'wp-security-audit-log' );
		$this->add_global_setting( 'adapter-connection', $connection_name, true );
		$this->save_connection( $db_connection );
		return true;
	}
}
