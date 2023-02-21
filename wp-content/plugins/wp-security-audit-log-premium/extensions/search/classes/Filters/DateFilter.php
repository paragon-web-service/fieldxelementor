<?php
/**
 * Class: Date Filter
 *
 * Date filter for search extension.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_AS_Filters_DateFilter
 *
 * @package wsal
 * @subpackage search
 */
class WSAL_AS_Filters_DateFilter extends WSAL_AS_Filters_AbstractFilter {

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'Date', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_applicable( $query ) {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_prefixes() {
		return array(
			'from',
			'to',
			'on',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_widgets() {
		return array(
			new WSAL_AS_Filters_DateWidget( $this, 'from', esc_html__( 'Later than', 'wp-security-audit-log' ) ),
			new WSAL_AS_Filters_DateWidget( $this, 'to', esc_html__( 'Earlier than', 'wp-security-audit-log' ) ),
			new WSAL_AS_Filters_DateWidget( $this, 'on', esc_html__( 'On this day', 'wp-security-audit-log' ) ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function modify_query( $query, $prefix, $value ) {
		$sanitized_date_format = $this->plugin->settings()->get_date_format( true );
		$date                  = DateTime::createFromFormat( $sanitized_date_format, $value[0], new \DateTimeZone( \wp_timezone_string() ) );
		$date->setTime( 0, 0 ); // Reset time to 00:00:00.
		$date_string = $date->format( 'U' );

		switch ( $prefix ) {
			case 'from':
				$query->add_condition( 'created_on >= %s', $date_string );
				break;
			case 'to':
				$query->add_condition( 'created_on <= %s', strtotime( '+1 day -1 minute', $date_string ) );
				break;
			case 'on':
				/**
				 * We need to create a date range for events on a particular
				 * date.
				 *   1. From the hour 00:00:01
				 *   2. To the hour 23:59:59
				 */
				$query->add_condition( 'created_on >= %s', strtotime( '-1 day +1 day +1 second', $date_string ) ); // From the hour 00:00:01.
				$query->add_condition( 'created_on <= %s', strtotime( '+1 day -1 second', $date_string ) ); // To the hour 23:59:59.
				break;
			default:
				throw new Exception( 'Unsupported filter "' . $prefix . '".' );
		}
	}
}
