<?php
/**
 * Class WSAL_Rep_JsonReportGenerator.
 *
 * @package    wsal
 * @subpackage reports
 *
 * @since 4.4.0
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Handles generation of reports in JSON format.
 *
 * @package    wsal
 * @subpackage reports
 *
 * @since 4.4.0
 */
class WSAL_Rep_JsonReportGenerator extends WSAL_Rep_AbstractReportGenerator {

	/**
	 * {@inheritDoc}
	 */
	public function generate( string $filename, array $data, array $alert_groups = array() ) {
		return $this->generate_json( $filename, $data );
	}

	/**
	 * Generates a JSON report from given raw data.
	 *
	 * @param string $filename Base of the report filename (without extension).
	 * @param array  $data     Report data.
	 *
	 * @return string Name of the JSON file (does not include the path).
	 */
	private function generate_json( $filename, $data ) {
		$report_filename = $filename . '.json';
		$report_filepath = $this->reports_dir_path . $report_filename;

		file_put_contents( $report_filepath, json_encode( $data, JSON_PRETTY_PRINT ) ); // phpcs:ignore

		return $report_filename;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_unique_ips( string $filename, array $data, $date_start, $date_end ) {
		return $this->generate_json( $filename, $data );
	}
}
