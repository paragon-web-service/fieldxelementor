<?php
/**
 * Class: Username Filter
 *
 * Username Filter for search extension.
 *
 * @since 1.0.0
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserNameFilter' ) ) :

	/**
	 * Class WSAL_AS_Filters_UserFilter
	 *
	 * @package wsal
	 * @subpackage search
	 */
	class WSAL_AS_Filters_UserNameFilter extends WSAL_AS_Filters_AbstractUserAttributeFilter {

		/**
		 * Method: Get Name.
		 */
		public function get_name() {
			return esc_html__( 'User', 'wp-security-audit-log' );
		}

		/**
		 * Method: Get Prefixes.
		 */
		public function get_prefixes() {
			return array(
				'username',
			);
		}

		/**
		 * Method: Get Widgets.
		 */
		public function get_widgets() {
			return array( new WSAL_AS_Filters_UserNameWidget( $this, 'username', 'Username' ) );
		}

		/**
		 * {@inheritDoc}
		 */
		protected function get_users_for_lookup( $value ) {
			$users = array();
			foreach ( $value as $username ) {
				$user = get_user_by( 'login', $username );
				if ( ! $user ) {
					$user = get_user_by( 'slug', $username );
				}

				if ( $user instanceof WP_User ) {
					array_push( $users, $user );
				}
			}

			return $users;
		}

	}

endif;
