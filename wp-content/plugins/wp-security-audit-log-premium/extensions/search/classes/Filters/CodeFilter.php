<?php
/**
 * Class: Code Filter
 *
 * IP Filter for search extension.
 *
 * @since 3.5.1
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the filters for the code (Severity) items in the admin list.
 *
 * @since 3.5.1
 */
class WSAL_AS_Filters_CodeFilter extends WSAL_AS_Filters_AbstractFilter {

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'Severity', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_applicable( $query ) {
		// NOTE: I'm not certain this is the correct test method.
		return 'code' === strtolower( substr( trim( $query ), 0, 4 ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_prefixes() {
		return array(
			'code',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_widgets() {
		$widget  = new WSAL_AS_Filters_CodeWidget( $this, 'code', esc_html__( 'Severity', 'wp-security-audit-log' ) );
		$options = WSAL_ConstantManager::get_severities();
		foreach ( $options as $key => $option ) {
			$widget->add( str_replace( 'WSAL_', '', $key ), $option );
		}

		return array( $widget );
	}

	/**
	 * {@inheritDoc}
	 */
	public function modify_query( $query, $prefix, $value ) {
		// Check prefix.
		switch ( $prefix ) {
			case 'code':
				$int_values = array_map(
					function ( $item ) {
						return $this->convert_to_int( strtoupper( $item ) );
					},
					$value
				);

				// severity search condition.
				$sql = ' severity = %d ';
				$query->add_or_condition( array( $sql => $int_values ) );
				break;
			default:
				throw new Exception( 'Unsupported filter "' . $prefix . '".' );
		}
	}

	/**
	 * Converts string representing severity in the UI filters to and integer suitable to match the data in the DB.
	 *
	 * Defaults to code for INFO severity.
	 *
	 * @param string $severity_string A string representing the severity in the UI.
	 *
	 * @return string
	 * @since  3.5.1
	 */
	private function convert_to_int( $severity_string ) {
		// Try the given string first (this should work for the legacy PHP error based severity codes).
		$constant = $this->plugin->constants->get_constant_by( 'name', $severity_string );
		if ( null === $constant ) {
			// No match, let's try to prefix with "WSAL_" as this should match all the remaining cases.
			$constant = $this->plugin->constants->get_constant_by( 'name', 'WSAL_' . $severity_string );
		}

		if ( null === $constant ) {
			// Fallback.
			$constant = $this->plugin->constants->get_constant_by( 'name', 'WSAL_INFORMATIONAL' );
		}

		if ( null === $constant ) {
			// still nothing? default to INFO (200): Interesting events.
			return '200';
		}

		return (string) $constant->value;
	}
}
