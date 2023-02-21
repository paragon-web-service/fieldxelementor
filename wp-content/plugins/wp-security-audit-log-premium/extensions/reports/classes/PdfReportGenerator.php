<?php
/**
 * Class WSAL_Rep_PdfReportGenerator.
 *
 * @package    wsal
 * @subpackage reports
 *
 * @since      4.4.0
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Handles generation of reports in PDF format.
 *
 * @package    wsal
 * @subpackage reports
 *
 * @since      4.4.0
 */
class WSAL_Rep_PdfReportGenerator extends WSAL_Rep_AbstractReportGenerator {

	/**
	 * {@inheritDoc}
	 */
	public function generate( string $filename, array $data, array $alert_groups = array() ) {
		$generator = new WSAL_Rep_HtmlReportGenerator( $this->plugin, $this->reports_dir_path, WSAL_Rep_DataFormat::HTML, $this->filters );
		$generator->enable_compact_mode();

		add_filter( 'wsal_generic_report_columns', array( $this, 'alter_columns' ), 10, 2 );
		add_filter( 'wsal_generic_report_row_data', array( $this, 'alter_row_data' ), 10, 2 );
		$html_file = $generator->generate( $filename, $data, $alert_groups );

		return $this->generate_pdf( $html_file );
	}

	/**
	 * Generates a PDF report from given HTML report file.
	 *
	 * It also takes care of clearing the HTML file.
	 *
	 * @param string $html_filename HTML report filename without the extension.
	 *
	 * @return string Name of the PDF file (does not include the path).
	 * @since 4.4.0
	 */
	private function generate_pdf( $html_filename ) {
		$pdf_filename = pathinfo( $html_filename, PATHINFO_FILENAME ) . '.pdf';

		// @codingStandardsIgnoreStart
		try {
			//  we intentionally suppress warnings from the HTML2PDF library to prevent error log from filling up
			@$html2pdf = new Spipu\Html2Pdf\Html2Pdf( 'L', 'A4', 'en' );
			@$html2pdf->setDefaultFont( 'Arial' );

			$html_content = file_get_contents( $this->reports_dir_path . $html_filename );
			@$html2pdf->writeHTML( $html_content );
			@$html2pdf->output( $this->reports_dir_path . $pdf_filename, "F" );
		} catch ( \Spipu\Html2Pdf\Exception\Html2PdfException $exception ) {
			return new WP_Error( 'wsal_pdf_error', 'PDF report generation failed. ' . $exception->getMessage() );
		}
		@unlink( $this->reports_dir_path . $html_filename );

		// @codingStandardsIgnoreEnd

		return $pdf_filename;
	}

	/**
	 * Tweaks selected row values.
	 *
	 * @param array   $row_data    Row data.
	 * @param integer $data_format Report data format.
	 *
	 * @since 4.4.0
	 */
	public function alter_row_data( $row_data, $data_format ) {
		if ( array_key_exists( 'role', $row_data ) ) {
			$row_data['role'] = str_replace( ',', '<br />', $row_data['role'] );
		}

		if ( array_key_exists( 'code', $row_data ) ) {
			$row_data['code'] = substr( $row_data['code'], 0, 4 );
		}

		return $row_data;
	}

	/**
	 * Removes selected columns and also changes some existing ones to make the resulting PDF more compact.
	 *
	 * @param array   $columns     List of columns.
	 * @param integer $data_format Report data format.
	 *
	 * @return array
	 * @since 4.4.0
	 */
	public function alter_columns( $columns, $data_format ) {
		foreach ( array( 'user_displayname', 'object' ) as $column ) {
			if ( array_key_exists( $column, $columns ) ) {
				unset( $columns[ $column ] );
			}
		}

		if ( array_key_exists( 'code', $columns ) ) {
			/* translators: shortcut for "Severity" that displays in the PDF reports */
			$columns['code'] = esc_html__( 'Sev.', 'wp-security-audit-log' );
		}

		return $columns;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_unique_ips( string $filename, array $data, $date_start, $date_end ) {
		$generator = new WSAL_Rep_HtmlReportGenerator( $this->plugin, $this->reports_dir_path, WSAL_Rep_DataFormat::HTML, $this->filters );
		$generator->enable_compact_mode();

		$html_file = $generator->generate_unique_ips( $filename, $data, $date_start, $date_end );

		return $this->generate_pdf( $html_file );
	}
}
