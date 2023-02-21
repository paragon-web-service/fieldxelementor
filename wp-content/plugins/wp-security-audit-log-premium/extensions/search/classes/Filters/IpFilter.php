<?php
/**
 * Class: IP Filter
 *
 * IP Filter for search extension.
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
 * Class WSAL_AS_Filters_IpFilter
 *
 * @package wsal
 * @subpackage search
 */
class WSAL_AS_Filters_IpFilter extends WSAL_AS_Filters_AbstractFilter {

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'IP', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_applicable( $query ) {
		$query = explode( ':', $query );

		if ( count( $query ) > 1 ) { // phpcs:ignore
			// maybe IPv6?
			// TODO do IPv6 validation.
		}
		$query = explode( '.', $query[0] );

		if ( count( $query ) > 1 ) {
			// maybe IPv4?
			foreach ( $query as $part ) {
				if ( ! is_numeric( $part ) || $part < 0 || $part > 255 ) {
					return false;
				}
			}
			return true;
		}
		return false; // All validations failed.
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_prefixes() {
		return array(
			'ip',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_widgets() {
		$wgt = new WSAL_AS_Filters_IpWidget( $this, 'ip', esc_html__( 'IP Address', 'wp-security-audit-log' ) );
		$wgt->set_data_loader( array( $this, 'get_matching_ips' ) );
		return array( $wgt );
	}

	/**
	 * Get matching IPs for autocomplete.
	 *
	 * @param WSAL_AS_Filters_IpWidget $wgt – Filter widget.
	 */
	public function get_matching_ips( $wgt ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function modify_query( $query, $prefix, $value ) {
		// Check prefix.
		switch ( $prefix ) {
			case 'ip':
				$sql = ' client_ip = %s ';
				$query->add_or_condition( array( $sql => $value ) );
				break;
			default:
				throw new Exception( 'Unsupported filter "' . $prefix . '".' );
		}
	}
}
