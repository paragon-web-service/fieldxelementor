<?php
/**
 * Class: Alert Filter
 *
 * Filter for alert codes.
 *
 * @since 1.0.0
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_AS_Filters_AlertFilter
 */
class WSAL_AS_Filters_AlertFilter extends WSAL_AS_Filters_AbstractFilter {

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'Event ID', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_applicable( $query ) {
		return 'event' === strtolower( substr( trim( $query ), 0, 5 ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_prefixes() {
		return array(
			'event',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_widgets() {
		return array( new WSAL_AS_Filters_AlertWidget( $this, 'event', esc_html__( 'Event ID', 'wp-security-audit-log' ) ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function modify_query( $query, $prefix, $value ) {
		switch ( $prefix ) {
			case 'event':
				$query->add_or_condition( array( 'alert_id = %s' => $value ) );
				break;
			default:
				throw new Exception( 'Unsupported filter "' . $prefix . '".' );
		}
	}
}
