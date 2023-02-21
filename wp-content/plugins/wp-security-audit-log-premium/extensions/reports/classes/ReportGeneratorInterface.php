<?php
/**
 * Interface WSAL_Rep_ReportGeneratorInterface.
 *
 * @package    wsal
 * @subpackage reports
 * @since      4.4.0
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Report generator interface.
 *
 * @package    wsal
 * @subpackage reports
 *
 * @since      4.4.0
 */
interface WSAL_Rep_ReportGeneratorInterface {

	/**
	 * Generates the events based report.
	 *
	 * @param string $filename     Base of the report filename (without extension).
	 * @param array  $data         Data.
	 * @param array  $alert_groups Alert Groups.
	 *
	 * @return string|WP_Error Filename containing the report or error object in case there was a problem.
	 */
	public function generate( string $filename, array $data, array $alert_groups = array() );

	/**
	 * Generates the IP address based report.
	 *
	 * @param string $filename   Base of the report filename (without extension).
	 * @param array  $data       Report data.
	 * @param mixed  $date_start Start date.
	 * @param mixed  $date_end   End date.
	 *
	 * @return string|WP_Error Filename containing the report or error object in case there was a problem.
	 */
	public function generate_unique_ips( string $filename, array $data, $date_start, $date_end );
}
