<?php
/**
 * Filter: Abstract User Attribute Filter
 *
 * @since 4.4.0
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_AbstractUserAttributeFilter' ) ) :

	/**
	 * Abstract user attribute filter.
	 *
	 * @since 4.4.0
	 */
	abstract class WSAL_AS_Filters_AbstractUserAttributeFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			global $wpdb;
			$args = array( esc_sql( $query ) . '%', esc_sql( $query ) . '%' );
			return $wpdb->count( 'SELECT COUNT(*) FROM ' . $wpdb->users . ' WHERE name LIKE %s OR username LIKE %s', $args ) > 0;
		}

		/**
		 * Retrieves the list of users for events lookup.
		 *
		 * @param array $value - The filter value.
		 *
		 * @return WP_User[]
		 * @since 4.4.0
		 */
		abstract protected function get_users_for_lookup( $value );

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			$allowed_prefix = $this->get_prefixes()[0];
			if ( $prefix !== $allowed_prefix ) {
				throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}

			$users = $this->get_users_for_lookup( $value );
			if ( ! empty( $users ) ) {
				global $wpdb;
				$usernames           = wp_list_pluck( $users, 'user_login' );
				$placeholders_string = implode( ', ', array_fill( 0, count( $usernames ), '%s' ) );

				$sql = $wpdb->prepare( "username IN ( " . $placeholders_string . ' ) ', $usernames ); // phpcs:ignore

				$user_ids            = wp_list_pluck( $users, 'ID' );
				$placeholders_string = implode( ', ', array_fill( 0, count( $user_ids ), '%d' ) );

				$sql .= ' OR ' . $wpdb->prepare( "user_id IN ( " . $placeholders_string . ' ) ', $user_ids ); // phpcs:ignore
				$query->add_or_condition( array( $sql => '' ) );
			}
		}
	}

endif;
