<?php
/**
 * Filter: Post Type Filter
 *
 * Post type filter for search.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostTypeFilter' ) ) :

	/**
	 * WSAL_AS_Filters_PostTypeFilter.
	 *
	 * Post type filter class.
	 */
	class WSAL_AS_Filters_PostTypeFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Post Type' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'posttype' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			$output     = 'names'; // Names or objects, note names is the default.
			$operator   = 'and';   // Conditions: "and" or "or".
			$post_types = get_post_types( array(), $output, $operator );

			// Search for the post type in query from available post types.
			$key = array_search( $query, $post_types, true );

			return ( ! empty( $key ) );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_PostTypeWidget( $this, 'posttype', esc_html__( 'Post Type', 'wp-security-audit-log' ) );

			// Get the post types.
			$output     = 'names'; // Names or objects, note names is the default.
			$operator   = 'and'; // Conditions: "and" or "or".
			$post_types = get_post_types( array(), $output, $operator );

			// Search and remove attachment type.
			$key = array_search( 'attachment', $post_types, true );
			if ( false !== $key ) {
				unset( $post_types[ $key ] );
			}

			// Add select options to widget.
			foreach ( $post_types as $post_type ) {
				$widget->add( strtolower( $post_type ), $post_type );
			}
			return array( $widget );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'posttype':
					$sql = ' post_type = %s ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
