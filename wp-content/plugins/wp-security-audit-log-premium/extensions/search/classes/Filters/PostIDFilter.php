<?php
/**
 * Class: Post ID Filter
 *
 * Filter for Post IDs.
 *
 * @since 3.2.3
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostIDFilter' ) ) {

	/**
	 * WSAL_AS_Filters_PostIDFilter.
	 *
	 * Post type filter class.
	 */
	class WSAL_AS_Filters_PostIDFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Post ID', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			if ( ! is_int( $query ) ) {
				return false;
			}
			return true;
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array(
				'postid',
			);
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			return array( new WSAL_AS_Filters_PostIDWidget( $this, 'postid', esc_html__( 'Post ID', 'wp-security-audit-log' ) ) );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'postid':
					$sql = ' post_id = %d ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}
}
