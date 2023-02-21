<?php
/**
 * Filter: Post Status Filter
 *
 * Post Status filter for search.
 *
 * @since 3.1
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostStatusFilter' ) ) :

	/**
	 * WSAL_AS_Filters_PostStatusFilter.
	 *
	 * Post type filter class.
	 */
	class WSAL_AS_Filters_PostStatusFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Post Status', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array(
				'poststatus',
			);
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			$post_statuses = array( 'draft', 'future', 'pending', 'private', 'publish' );

			// Search for the post status in query from available post statuses.
			$key = array_search( $query, $post_statuses, true );

			return ( ! empty( $key ) );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			// Intialize post status widget class.
			$widget = new WSAL_AS_Filters_PostStatusWidget( $this, 'poststatus', esc_html__( 'Post Status', 'wp-security-audit-log' ) );

			// Add select options to widget.
			foreach ( array( 'draft', 'future', 'pending', 'private', 'publish' ) as $status ) {
				$text = 'publish' === $status ? 'published' : $status;
				$widget->add( ucwords( $text ), $status );
			}
			return array( $widget );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'poststatus':
					$sql = ' post_status = %s ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
