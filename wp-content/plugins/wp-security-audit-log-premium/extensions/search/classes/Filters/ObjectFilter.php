<?php
/**
 * Object Filter
 *
 * Object filter for search.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_ObjectFilter' ) ) :

	/**
	 * WSAL_AS_Filters_ObjectFilter.
	 *
	 * Object filter class.
	 */
	class WSAL_AS_Filters_ObjectFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Object', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'object' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			return true;
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_ObjectWidget( $this, 'object', esc_html__( 'Object', 'wp-security-audit-log' ) );

			// Get event objects.
			$event_objects = $this->plugin->alerts->get_event_objects_data();

			// Add select options to widget.
			foreach ( $event_objects as $key => $role ) {
				$widget->add( $role, $key );
			}

			return array( $widget );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'object':
					$sql = ' object = %s ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
