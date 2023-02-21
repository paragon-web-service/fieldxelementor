<?php
/**
 * Event Type Filter
 *
 * Event Type filter for search.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_EventTypeFilter' ) ) :

	/**
	 * WSAL_AS_Filters_EventTypeFilter.
	 *
	 * Event Type filter class.
	 */
	class WSAL_AS_Filters_EventTypeFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Event Type', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'event-type' );
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
			$widget = new WSAL_AS_Filters_EventTypeWidget( $this, 'event-type', esc_html__( 'Event Type', 'wp-security-audit-log' ) );

			// Get event objects.
			$event_objects = WpSecurityAuditLog::get_instance()->alerts->get_event_type_data();

			// Add select options to widget.
			foreach ( $event_objects as $key => $role ) {
				$widget->add( $role, $key );
			}

			return array( $widget );
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'event-type':
					$sql = ' event_type = %s ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
