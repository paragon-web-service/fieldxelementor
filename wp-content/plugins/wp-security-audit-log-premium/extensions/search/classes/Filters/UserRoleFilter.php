<?php
/**
 * Filter: User Role Filter
 *
 * User Role filter for search.
 *
 * @since 3.1
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WSAL\Adapter\WSAL_Adapters_MySQL_Occurrence;

if ( ! class_exists( 'WSAL_AS_Filters_UserRoleFilter' ) ) :

	/**
	 * WSAL_AS_Filters_UserRoleFilter.
	 *
	 * User Role filter class.
	 *
	 * @since 3.1
	 */
	class WSAL_AS_Filters_UserRoleFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'User Role', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'userrole' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			// Get WP user roles.
			$wp_user_roles = $this->get_wp_user_roles();
			$user_roles    = array();
			foreach ( $wp_user_roles as $role => $details ) {
				$user_roles[ $role ] = translate_user_role( $details['name'] );
			}

			// Search for the post status in query from available post statuses.
			$key = array_search( $query, $user_roles, true );

			return ( ! empty( $key ) );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_UserRoleWidget( $this, 'userrole', esc_html__( 'User Role', 'wp-security-audit-log' ) );

			// Get WP user roles.
			$wp_user_roles = $this->get_wp_user_roles();
			$user_roles    = array();
			foreach ( $wp_user_roles as $role => $details ) {
				$user_roles[ $role ] = translate_user_role( $details['name'] );
			}

			// Add select options to widget.
			foreach ( $user_roles as $key => $role ) {
				$widget->add( $role, $key );
			}
			return array( $widget );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			switch ( $prefix ) {
				case 'userrole':
					$connection = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_connection();
					$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
					$table_occ  = $occurrence->get_table();

					$sql = " replace(replace(replace($table_occ.user_roles, ']', ''), '[', ''), '\\'', '') REGEXP %s ";
					$query->add_or_condition( array( $sql => implode( '|', $value ) ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}

		/**
		 * Method: Get WP User roles.
		 *
		 * @return array
		 */
		private function get_wp_user_roles() {
			$wp_user_roles = '';
			// Check if function `wp_roles` exists.
			if ( function_exists( 'wp_roles' ) ) {
				// Get WP user roles.
				$wp_user_roles = wp_roles()->roles;
			} else { // WP Version is below 4.3.0
				// Get global wp roles variable.
				global $wp_roles;

				// If it is not set then initiate WP_Roles class object.
				if ( ! isset( $wp_roles ) ) {
					$new_wp_roles = new WP_Roles(); // Don't override the original global variable.
				}

				// Get WP user roles.
				$wp_user_roles = $new_wp_roles->roles;
			}
			return $wp_user_roles;
		}
	}

endif;
