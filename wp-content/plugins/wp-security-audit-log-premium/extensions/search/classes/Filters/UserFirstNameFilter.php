<?php
/**
 * Filter: User First Name Filter
 *
 * User first name filter for search.
 *
 * @since   1.1.7
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserFirstNameFilter' ) ) :

	/**
	 * WSAL_AS_Filters_UserFirstNameFilter.
	 *
	 * User first name filter class.
	 *
	 * @since 1.1.7
	 */
	class WSAL_AS_Filters_UserFirstNameFilter extends WSAL_AS_Filters_AbstractUserAttributeFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'User' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'firstname' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			return array( new WSAL_AS_Filters_UserFirstNameWidget( $this, 'firstname', 'First Name' ) );
		}

		/**
		 * {@inheritDoc}
		 */
		protected function get_users_for_lookup( $value ) {
			$users = array();
			foreach ( $value as $first_name ) {
				$users_array = get_users(
					array(
						'meta_key'     => 'first_name', // phpcs:ignore
						'meta_value'   => $first_name, // phpcs:ignore
						'fields'       => array( 'ID', 'user_login' ),
						'meta_compare' => 'LIKE',
					)
				);

				foreach ( $users_array as $user ) {
					$users[] = $user;
				}
			}
			return $users;
		}
	}

endif;
