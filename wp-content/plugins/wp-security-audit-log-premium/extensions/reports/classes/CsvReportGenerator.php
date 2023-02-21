<?php
/**
 * Class WSAL_Rep_CsvReportGenerator
 * Provides utility methods to generate a csv report
 *
 * @package    wsal
 * @subpackage reports
 */

// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fwrite
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Class WSAL_Rep_CsvReportGenerator
 * Provides utility methods to generate a csv report
 *
 * @package    wsal
 * @subpackage reports
 */
class WSAL_Rep_CsvReportGenerator extends WSAL_Rep_AbstractReportGenerator {

	/**
	 * CSV delimiter.
	 *
	 * @var string
	 */
	private static $delimiter = ',';

	/**
	 * {@inheritDoc}
	 */
	public function generate( string $filename, array $data, array $alert_groups = array() ) {

		$grouped_data = $this->group_data_by_blog_name( $data );
		if ( empty( $grouped_data ) ) {
			return WSAL_Rep_Common::build_error( 0, $this->reports_dir_path );
		}

		$report_filename = $filename . '.csv';
		$report_filepath = $this->reports_dir_path . $report_filename;

		$file = fopen( $report_filepath, 'w' );

		$is_statistical = isset( $this->filters['type_statistics'] );
		if ( $is_statistical ) {
			$columns = $this->get_statistics_columns();
		} else {
			$columns = $this->get_generic_columns();
		}

		$quoted_data = array_map( array( $this, 'quote' ), $columns );
		$out         = sprintf( "%s\n", implode( self::$delimiter, $quoted_data ) );

		fwrite( $file, $out );

		if ( isset( $this->filters['type_statistics'] ) ) {
			$this->write_dynamic_rows( $file, $data, $this->filters['type_statistics'], $columns );
		} else {
			foreach ( $grouped_data as $entry ) {
				// Add rows.
				foreach ( $entry as $k => $alert ) {
					$processed_row_data = array();
					foreach ( $columns as $key => $label ) {
						$value = array_key_exists( $key, $alert ) ? $alert[ $key ] : '';
						if ( 'time' === $key ) {
							$value = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $alert['timestamp'], 'time', true, false, false );
						} elseif ( 'date' === $key ) {
							$value = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $alert['timestamp'], 'date', true, false, false );
						}

						$processed_row_data[ $key ] = $value;
					}

					/**
					 * WSAL Filter: `wsal_generic_report_row_data`
					 *
					 * Filters row data in generic report before writing it to a file.
					 *
					 * @param array   $row_data    Row data.
					 * @param integer $data_format Report data format.
					 *
					 * @since 4.4.0
					 */
					$processed_row_data = apply_filters( 'wsal_generic_report_row_data', $processed_row_data, WSAL_Rep_DataFormat::CSV );

					$quoted_data = array_map( array( $this, 'quote' ), $processed_row_data );
					$out         = sprintf( "%s\n", implode( self::$delimiter, $quoted_data ) );

					fwrite( $file, $out );
				}
			}
		}

		fclose( $file );

		return $report_filename;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Note: ignores the $suppress_filter filter as it is used just to suppress the filter from executing in the parent
	 * class.
	 */
	protected function get_generic_columns( $suppress_filter = false ) {
		$base_columns = parent::get_generic_columns( true );
		$position     = array_search( 'timestamp', array_keys( $base_columns ), true );
		$result       = array_merge(
			array(
				'blog_name' => esc_html__( 'Blog Name', 'wp-security-audit-log' ),
			),
			array_slice( $base_columns, 0, $position, true ),
			array(
				'date' => esc_html__( 'Date', 'wp-security-audit-log' ),
				'time' => esc_html__( 'Time', 'wp-security-audit-log' ),
			),
			array_slice( $base_columns, $position + 1, null, true ),
			array(
				'metadata' => esc_html__( 'Metadata', 'wp-security-audit-log' ),
				'links'    => esc_html__( 'Links', 'wp-security-audit-log' ),
			)
		);

		/**
		 * WSAL Filter: `wsal_generic_report_columns`
		 *
		 * Filters columns in generic report before writing it to a file.
		 *
		 * @param array   $columns     List of columns.
		 * @param integer $data_format Report data format.
		 *
		 * @since 4.4.0
		 */
		return apply_filters( 'wsal_generic_report_columns', $result, WSAL_Rep_DataFormat::CSV );
	}

	/**
	 * Write the rows of the file.
	 *
	 * @param resource $file            File handle.
	 * @param array    $data            Report data.
	 * @param int      $type_statistics Statistical report type.
	 * @param array    $columns         Report columns.
	 */
	private function write_dynamic_rows( $file, $data, $type_statistics, $columns ) {
		foreach ( $data as $element ) {
			$row_data = $this->get_statistical_row_data( $element, $columns, $type_statistics );

			$quoted_data = array_map( array( $this, 'quote' ), $row_data );
			fwrite( $file, sprintf( "%s\n", implode( self::$delimiter, $quoted_data ) ) );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_unique_ips( string $filename, array $data, $date_start, $date_end ) {
		$report_filename = $filename . '.csv';
		$report_filepath = $this->reports_dir_path . $report_filename;

		$file = fopen( $report_filepath, 'w' );

		$columns = $this->get_statistics_columns();

		$quoted_data = array_map( array( $this, 'quote' ), $columns );
		$out         = sprintf( "%s\n", implode( self::$delimiter, $quoted_data ) );

		fwrite( $file, $out );
		$this->write_dynamic_rows( $file, $data, $this->filters['type_statistics'], $columns );

		fclose( $file );

		return $report_filename;
	}

	/**
	 * Utility method to quote the given item
	 *
	 * @param mixed $data - Data.
	 *
	 * @return string
	 * @internal
	 */
	final public function quote( $data ) {
		$data = preg_replace( '/"(.+)"/', '""$1""', $data );

		return sprintf( '"%s"', $data );
	}
}
