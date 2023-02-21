<?php
/**
 * Class WSAL_Rep_Report.
 *
 * @package    wsal
 * @subpackage reports
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Plain PHP object for storing information about a single report. Used for example to show a list of generated reports.
 *
 * @package    wsal
 * @subpackage reports
 * @since      4.4.0
 */
class WSAL_Rep_Report {

	/**
	 * ID of the user who created the report.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Download URL.
	 *
	 * @var string
	 */
	private $download_url;

	/**
	 * Arbitrary object storing the report filters.
	 *
	 * @var object
	 */
	private $filters;

	/**
	 * Time when the report was generated.
	 *
	 * @var int
	 */
	private $timestamp;

	/**
	 * Name of the temporary report file stored on server.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Constructor.
	 *
	 * @param int    $user_id      User ID.
	 * @param string $download_url Download URL.
	 * @param object $filters      Filters.
	 * @param string $filename     Filename.
	 */
	public function __construct( $user_id, $download_url, $filters, $filename ) {
		$this->user_id      = $user_id;
		$this->download_url = $download_url;
		$this->filters      = $filters;
		$this->filename     = $filename;
		$this->timestamp    = current_time( 'timestamp', true ); // phpcs:ignore
	}

	/**
	 * Convenience method that builds and object from an array.
	 *
	 * @param array $data Report data as array.
	 *
	 * @return WSAL_Rep_Report Report object.
	 */
	public static function from_array( $data ) {
		$report            = new WSAL_Rep_Report( $data['user'], $data['url'], $data['filters'], $data['file'] );
		$report->timestamp = $data['time'];

		return $report;
	}

	/**
	 * Gets the report format.
	 *
	 * @return string Report format (extension) as text to display.
	 */
	public function get_format() {
		return WSAL_Rep_DataFormat::get_label( $this->filters['report_format'] );
	}

	/**
	 * Convenience method that turns the object to an array that can be stored in database.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'user'    => $this->user_id,
			'url'     => $this->download_url,
			'filters' => $this->filters,
			'time'    => $this->timestamp,
			'file'    => $this->filename,
		);
	}

	/**
	 * Gets the download URL.
	 *
	 * @return string Report download URL.
	 */
	public function get_download_url() {
		return $this->download_url;
	}

	/**
	 * Gets report filters.
	 *
	 * @return object Report filters object.
	 */
	public function get_filters() {
		return $this->filters;
	}

	/**
	 * Gets the timestamp.
	 *
	 * @return int Timestamp.
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * Gets the user ID.
	 *
	 * @return int User ID.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Gets the filename.
	 *
	 * @return string Filename.
	 */
	public function get_filename() {
		return $this->filename;
	}
}
