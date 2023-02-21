<?php
/**
 * Class WSAL_Rep_HtmlReportGenerator
 * Provides utility methods to generate an html report
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
 * Generator for the HTML formatted report.
 *
 * @package    wsal
 * @subpackage reports
 */
class WSAL_Rep_HtmlReportGenerator extends WSAL_Rep_AbstractReportGenerator {

	/**
	 * Table cell padding styling.
	 *
	 * @var string
	 */
	private $cell_padding_style = 'padding: 16px 7px;';

	/**
	 * Regular font size in px.
	 *
	 * @var string
	 */
	private $regular_font_size = '14';

	/**
	 * CSS style for the cells containing a message.
	 *
	 * @var string
	 */
	private $message_cell_style = 'min-width: 400px;';

	/**
	 * Alignment of the main title and logo.
	 *
	 * @var string
	 */
	private $logo_and_title_alignment = 'center';

	/**
	 * True if logo image should be embedded.
	 *
	 * @var bool
	 */
	private $embed_logo = false;

	/**
	 * Enables compact mode (used when generating PDF reports).
	 */
	public function enable_compact_mode() {
		$this->regular_font_size        = '10';
		$this->cell_padding_style       = 'padding: 5px 4px;';
		$this->message_cell_style       = 'max-width: 300px';
		$this->logo_and_title_alignment = 'left';
		$this->embed_logo               = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate( string $filename, array $data, array $alert_groups = array() ) {

		$is_statistical = isset( $this->filters['type_statistics'] );
		$grouped_data   = $this->group_data_by_blog_name( $data );
		if ( empty( $grouped_data ) ) {
			return WSAL_Rep_Common::build_error( 0, $this->reports_dir_path );
		}

		$report_filename = $filename . '.html';
		$report_filepath = $this->reports_dir_path . $report_filename;

		$file = fopen( $report_filepath, 'w' );

		fwrite( $file, $this->get_document_opening_tag() );
		fwrite( $file, '<div class="wsal_report_wrap" style="margin: 20px 25px; font-family: Arial;">' );
		fwrite( $file, $this->build_report_header() );
		foreach ( $grouped_data as $blog_name => $alerts ) {
			if ( empty( $blog_name ) ) {
				continue;
			}

			if ( $is_statistical ) {
				$this->write_alerts_statistics( $file, $blog_name, $alerts );
			} else {
				$this->write_alerts_for_blog( $file, $blog_name, $alerts );
			}
		}

		fwrite( $file, '</div>' );
		fwrite( $file, $this->get_document_closing_tag() );
		fclose( $file );

		return $report_filename;
	}

	/**
	 * Populates the document opening tags.
	 *
	 * @return string
	 */
	private function get_document_opening_tag() {
		$result  = '<!DOCTYPE html><html><head>';
		$result .= '<meta charset="utf-8">';
		$result .= '<title>' . esc_html__( 'WP Activity Log Reporter', 'wp-security-audit-log' ) . '</title>';
		$result .= '</head>';
		$result .= '<body style=\'margin: 0 0;padding: 0 0;font-size: ' . $this->regular_font_size . 'px;color: #404040;\'>';

		return $result;
	}

	/**
	 * Generate the HTML head of the Report.
	 *
	 * @return string
	 */
	private function build_report_header() {
		$is_statistical = isset( $this->filters['type_statistics'] );
		$logo_src       = $this->get_logo_src();
		$logo_link      = $this->plugin->settings()->get_custom_reports_logo_link();
		if ( 0 === strlen( $logo_link ) ) {
			$logo_link = 'https://wpactivitylog.com/?utm_source=plugin&utm_medium=referral&utm_campaign=WSAL&utm_content=priodic-report';
		}

		$str  = '<div id="section-1" style="margin: 0; padding: 0; text-align: ' . $this->logo_and_title_alignment . ';">';
		$str .= '<a href="' . esc_url( $logo_link ) . '" rel="noopener noreferrer" target="_blank">';
		// Don't use esc_url here. Logo source can be something other than a URL.
		$str .= '<img src="' . $logo_src . '" alt="Report_logo" style="max-height: 150px; max-width: 800px;" />';
		$str .= '</a>';

		$str .= '<h1 style="color: #059348;">';
		if ( array_key_exists( 'custom_title', $this->filters ) ) {
			$str .= $this->filters['custom_title'];
		} else {
			$str .= esc_html__( 'Report from', 'wp-security-audit-log' ) . ' ' . get_bloginfo( 'name' ) . ' ' . esc_html__( 'website', 'wp-security-audit-log' );
		}
		$str .= '</h1>';

		$str .= '</div>';

		$formatter = WSAL_Utilities_DateTimeFormatter::instance();
		$now       = time();
		$date      = $formatter->get_formatted_date_time( $now, 'date' );
		$time      = $formatter->get_formatted_date_time( $now, 'time' );

		$user = wp_get_current_user();
		$str .= '<div id="section-2" style="margin: 0; padding: 0;">';

		$report_attributes = array();
		if ( $is_statistical ) {
			$report_attributes[ esc_html__( 'Type', 'wp-security-audit-log' ) ] = $this->get_statistical_report_title( intval( $this->filters['type_statistics'] ) );
		}

		$report_attributes[ esc_html__( 'Report Date', 'wp-security-audit-log' ) ]  = $date;
		$report_attributes[ esc_html__( 'Report Time', 'wp-security-audit-log' ) ]  = $time;
		$report_attributes[ esc_html__( 'Generated by', 'wp-security-audit-log' ) ] = $user->user_login . ' — ' . $user->user_email;

		$contact_attributes = array(
			esc_html__( 'Business Name', 'wp-security-audit-log' ) => $this->plugin->settings()->get_business_name(),
			esc_html__( 'Contact Name', 'wp-security-audit-log' ) => $this->plugin->settings()->get_contact_name(),
			esc_html__( 'Contact Email', 'wp-security-audit-log' ) => $this->plugin->settings()->get_contact_email(),
			esc_html__( 'Contact Phone', 'wp-security-audit-log' ) => $this->plugin->settings()->get_contact_phone_number(),
		);

		foreach ( $contact_attributes as $label => $value ) {
			if ( strlen( $value ) > 0 ) {
				$report_attributes[ $label ] = $value;
			}
		}

		$tbody  = '<table class="wsal_report_table" style="margin: 0 auto; width: auto; max-width: 600px; font-size: ' . $this->regular_font_size . 'px;">';
		$tbody .= '<tbody>';
		foreach ( $report_attributes as $label => $value ) {
			$tbody .= '<tr>';
			$tbody .= '<td style="padding: 5px 10px 5px 0;"><strong>' . $label . ':</strong></td>';
			$tbody .= '<td style="padding: 5px 10px;">' . $value . '</td>';
			$tbody .= '</tr>';
		}

		if ( array_key_exists( 'comment', $this->filters ) && strlen( $this->filters['comment'] ) > 0 ) {
			$tbody .= '<tr>';
			$tbody .= '<td colspan="2" style="padding: 30px 10px 5px 0;"><strong>' . esc_html__( 'Comment', 'wp-security-audit-log' ) . ':</strong></td>';
			$tbody .= '</tr>';
			$tbody .= '<tr>';
			$tbody .= '<td colspan="2" style="padding: 5px 10px 5px 0;">' . $this->filters['comment'] . '</td>';
			$tbody .= '</tr>';
		}

		$tbody .= '<tr>';
		$tbody .= '<td colspan="2" style="padding: 30px 10px 5px 0;"><strong>' . esc_html__( 'Report Criteria', 'wp-security-audit-log' ) . '</strong></td>';
		$tbody .= '</tr>';

		$criteria = $this->get_criteria_list();
		foreach ( $criteria as $criterion ) {
			$tbody .= '<tr>';
			$tbody .= '<td style="padding: 5px 10px 5px 0;"><em>' . $criterion['label'] . ':</em></td>';
			$tbody .= '<td style="padding: 5px 10px;">' . $criterion['value'] . '</td>';
			$tbody .= '</tr>';
		}

		$tbody .= '</tbody>';
		$tbody .= '</table>';

		$str .= $tbody;
		$str .= '</div>';

		return $str;
	}

	/**
	 * Determines the logo src attribute based on plugin settings. The result could be a URL or embedded image data.
	 *
	 * @return string
	 * @since 4.4.0
	 *
	 * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
	 * phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	 */
	private function get_logo_src() {
		$logo_path     = $this->plugin->settings()->get_custom_reports_logo();
		$relative_path = '/img/wsal-logo-full.png';
		if ( 0 === strlen( $logo_path ) ) {
			$logo_path = WSAL_BASE_URL . $relative_path;
		}

		if ( ! $this->embed_logo ) {
			return $logo_path;
		}

		$is_remote  = true;
		$image_data = @file_get_contents( $logo_path );
		$file_path  = $logo_path;
		if ( false === $image_data ) {
			// Image is not accessible on given URL for some reason. Let's fall back on our logo.
			$image_data = @file_get_contents( WSAL_BASE_DIR . $relative_path );
			$file_path  = WSAL_BASE_DIR . $relative_path;
			$is_remote  = false;
		}

		if ( false !== $image_data ) {

			$file_saved_locally = true;
			if ( $is_remote ) {
				// Save the image data to a temporary file to be able to figure out the mime type.
				$file_path = wp_tempnam();
				if ( false === @file_put_contents( $file_path, $image_data ) ) { // phpcs:ignore
					$file_saved_locally = false;
				}
			}

			if ( $file_saved_locally ) {
				// Only go ahead with the image embedding if file successfully saved locally.
				$mime_type = @mime_content_type( $file_path );
				if ( false !== $mime_type ) {
					return 'data:' . $mime_type . ' ;base64,' . base64_encode( $image_data ); // phpcs:ignore
				}

				if ( $is_remote && file_exists( $file_path ) ) {
					@unlink( $file_path );
				}
			}
		}

		// Fallback on the plugin URL in case embedding fails for any reason.
		return $logo_path;
	}

	/**
	 * Gets the title for a statistical report.
	 *
	 * @param int $statistics_report_type Report type.
	 *
	 * @return string
	 * @since 4.4.0
	 */
	private function get_statistical_report_title( $statistics_report_type ) {

		switch ( $statistics_report_type ) {
			case WSAL_Rep_Common::DIFFERENT_IP:
				return esc_html__( 'List of unique IP addresses per user', 'wp-security-audit-log' );
			case WSAL_Rep_Common::ALL_IPS:
				return esc_html__( 'List of IP addresses that accessed the website', 'wp-security-audit-log' );
			case WSAL_Rep_Common::ALL_USERS:
				return esc_html__( 'List of users that accessed the website', 'wp-security-audit-log' );
			case WSAL_Rep_Common::LOGIN_ALL:
			case WSAL_Rep_Common::LOGIN_BY_USER:
			case WSAL_Rep_Common::LOGIN_BY_ROLE:
				return esc_html__( 'Number of Logins per user', 'wp-security-audit-log' );
			case WSAL_Rep_Common::PUBLISHED_ALL:
			case WSAL_Rep_Common::PUBLISHED_BY_USER:
			case WSAL_Rep_Common::PUBLISHED_BY_ROLE:
				return esc_html__( 'Number of published posts per user', 'wp-security-audit-log' );
			case WSAL_Rep_Common::VIEWS_ALL:
			case WSAL_Rep_Common::VIEWS_BY_USER:
			case WSAL_Rep_Common::VIEWS_BY_ROLE:
				return esc_html__( 'Number of viewed posts per user', 'wp-security-audit-log' );
			case WSAL_Rep_Common::VIEWS_BY_POST:
				return esc_html__( 'Number of post views per user', 'wp-security-audit-log' );
			case WSAL_Rep_Common::PASSWORD_CHANGES:
				return esc_html__( 'Number of password changes and password resets per user', 'wp-security-audit-log' );
			default:
				return esc_html__( 'Statistical report', 'wp-security-audit-log' );
		}
	}

	/**
	 * Retrieves a list of criteria to show in the report header. This defaults to all possible filtering criteria for
	 * the basic (alert list) report. Statistical reports only show teh relevant criteria.
	 *
	 * @return array
	 *
	 * @since 4.4.0
	 */
	private function get_criteria_list() {
		$default_label = esc_html__( 'All', 'wp-security-audit-log' );
		$start_date    = esc_html__( 'From the beginning', 'wp-security-audit-log' );
		$end_date      = $this->get_formatted_date( current_time( 'timestamp' ) ); // phpcs:ignore

		if ( ! empty( $this->filters['date_range']['start'] ) ) {
			$start_date = $this->filters['date_range']['start'];
		}

		if ( ! empty( $this->filters['date_range']['end'] ) ) {
			$end_date = $this->filters['date_range']['end'];
		}

		$criteria = array(
			'date_range|start' => array(
				'label' => esc_html__( 'Start date', 'wp-security-audit-log' ),
				'value' => $start_date,
			),
			'date_range|end'   => array(
				'label' => esc_html__( 'End date', 'wp-security-audit-log' ),
				'value' => $end_date,
			),
		);

		$possible_filters = WSAL_Rep_Common::get_possible_filters();
		// The following are filters that can be nested under alert_codes (legacy), but they can also exist on their own in the newer versions.
		$dual_filters = array(
			'post_ids',
			'post_ids-exclude',
			'post_types',
			'post_types-exclude',
			'post_statuses',
			'post_statuses-exclude',
		);
		foreach ( $dual_filters as $dual_filter ) {
			if ( array_key_exists( $dual_filter, $this->filters ) ) {
				$possible_filters[ $dual_filter ] = $possible_filters[ 'alert_codes|' . $dual_filter ];
				unset( $possible_filters[ 'alert_codes|' . $dual_filter ] );
			}
		}

		foreach ( $possible_filters as $filter_key => $filter_definition ) {
			if ( 'alert_codes' === $filter_key ) {
				// We skip this one as it is only kept for backwards compatibility in other parts of the plugin.
				continue;
			}

			$label        = $filter_definition['criteria_label'];
			$filter_value = null;

			if ( strpos( $filter_key, '|' ) !== false ) {
				$array_indexes = explode( '|', $filter_key, 2 );
				if ( 2 === count( $array_indexes ) ) {
					if ( array_key_exists( $array_indexes[0], $this->filters ) && is_array( $this->filters[ $array_indexes[0] ] ) && array_key_exists( $array_indexes[1], $this->filters[ $array_indexes[0] ] ) ) {
						$filter_value = $this->filters[ $array_indexes[0] ][ $array_indexes[1] ];
					}
				}
			} elseif ( array_key_exists( $filter_key, $this->filters ) ) {
				$filter_value = $this->filters[ $filter_key ];
			}

			if ( ! is_null( $filter_value ) ) {
				if ( is_array( $filter_value ) ) {
					if ( in_array( $filter_key, array( 'users', 'users-exclude' ), true ) ) {
						$tmp      = array();
						$user_ids = WSAL_ReportArgs::extract_user_ids( $filter_value );
						foreach ( $user_ids as $user_id ) {
							$u = get_user_by( 'id', $user_id );
							array_push( $tmp, $u->user_login . ' — ' . $u->user_email );
						}
						$filter_value = implode( ',<br>', $tmp );
					} elseif ( in_array( $filter_key, array( 'post_ids', 'post_ids-exclude' ), true ) ) {
						$tmp      = array();
						$post_ids = array_map( 'intval', $filter_value );
						foreach ( $post_ids as $post_id ) {
							array_push( $tmp, get_the_title( $post_id ) );
						}
						$filter_value = implode( ',<br>', $tmp );
					} else {
						$filter_value = implode( ', ', $filter_value );
					}
				}

				$criteria[ $filter_key ] = array(
					'label' => $label,
					'value' => $filter_value,
				);

				$is_exclusion = array_key_exists( 'is_exclusion_for', $filter_definition );
				if ( $is_exclusion && array_key_exists( $filter_definition['is_exclusion_for'], $criteria ) ) {
					unset( $criteria[ $filter_definition['is_exclusion_for'] ] );
				}
			} else {
				// Default label is added only for the non-exclusion criteria.
				$is_exclusion = array_key_exists( 'is_exclusion_for', $filter_definition );
				if ( ! $is_exclusion ) {
					$criteria[ $filter_key ] = array(
						'label' => $label,
						'value' => $default_label,
					);
				}
			}
		}

		$is_statistical = isset( $this->filters['type_statistics'] );
		if ( $is_statistical ) {
			$criteria = $this->remove_non_statistical_criteria( $criteria, intval( $this->filters['type_statistics'] ) );
		}

		return $criteria;
	}

	/**
	 * Removes report criteria not needed for given statistical report type.
	 *
	 * @param array $criteria               Report criteria for display.
	 * @param int   $statistics_report_type Statistical report type.
	 *
	 * @return array
	 * @since 4.4.0
	 */
	private function remove_non_statistical_criteria( $criteria, $statistics_report_type ) {

		$criteria_to_keep = array(
			'date_range|start',
			'date_range|end',
		);

		switch ( $statistics_report_type ) {
			case WSAL_Rep_Common::DIFFERENT_IP:
			case WSAL_Rep_Common::ALL_IPS:
			case WSAL_Rep_Common::ALL_USERS:
			case WSAL_Rep_Common::PASSWORD_CHANGES:
				break;
			case WSAL_Rep_Common::LOGIN_ALL:
			case WSAL_Rep_Common::LOGIN_BY_USER:
			case WSAL_Rep_Common::PUBLISHED_ALL:
			case WSAL_Rep_Common::PUBLISHED_BY_USER:
			case WSAL_Rep_Common::VIEWS_ALL:
			case WSAL_Rep_Common::VIEWS_BY_USER:
				array_push( $criteria_to_keep, 'alert_codes|alerts' );
				array_push( $criteria_to_keep, 'users' );
				break;
			case WSAL_Rep_Common::LOGIN_BY_ROLE:
			case WSAL_Rep_Common::PUBLISHED_BY_ROLE:
			case WSAL_Rep_Common::VIEWS_BY_ROLE:
				array_push( $criteria_to_keep, 'alert_codes|alerts' );
				array_push( $criteria_to_keep, 'roles' );
				break;
			case WSAL_Rep_Common::VIEWS_BY_POST:
				array_push( $criteria_to_keep, 'post_ids' );
				break;
			default:
		}

		foreach ( $criteria as $key => $criterion ) {
			if ( ! in_array( $key, $criteria_to_keep, true ) ) {
				unset( $criteria[ $key ] );
			}
		}

		return $criteria;
	}

	/**
	 * Generate the HTML body of the Statistics Report.
	 *
	 * @param resource $file      File handle.
	 * @param string   $blog_name Blog name.
	 * @param array    $data      Report data.
	 */
	private function write_alerts_statistics( $file, $blog_name, $data ) {
		$this->write_statistical_report_core( $file, $blog_name, $data );
	}

	/**
	 * Writes the core section of the statistical report.
	 *
	 * @param resource $file  Target file handle.
	 * @param string   $title Title.
	 * @param array    $data  Report data.
	 *
	 * @since 4.4.0
	 */
	private function write_statistical_report_core( $file, $title, $data ) {

		$columns         = $this->get_statistics_columns();
		$type_statistics = array_key_exists( 'type_statistics', $this->filters ) ? $this->filters['type_statistics'] : null;

		$this->write_section_start( $file, $title, $columns );

		foreach ( $data as $i => $element ) {

			/**
			 * $columns are sent by reference and not value, se they could be different after that
			 */
			$row_data = $this->get_statistical_row_data( $element, $columns, $type_statistics );

			$row_html  = ( 0 !== $i % 2 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
			$row_html .= implode(
				'',
				array_map(
					function ( $item ) {
						return '<td style="' . $this->cell_padding_style . '"><p style="margin: 0">' . $item . '</p></td>';
					},
					$row_data
				)
			);
			$row_html .= '</tr>';

			fwrite( $file, $row_html );
		}

		$this->write_section_end( $file );
	}

	/**
	 * Writes a beginning of a table section to given file.
	 *
	 * @param resource $file    File handle.
	 * @param string   $title   Section title.
	 * @param array    $columns $columns for the header.
	 *
	 * @since 4.4.0
	 */
	private function write_section_start( $file, $title, $columns ) {
		fwrite( $file, '<h3 style="font-size: 20px; margin: 25px 0;">' . $title . '</h3>' );
		fwrite( $file, '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: ' . $this->regular_font_size . 'px;">' );

		$header  = '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;">';
		$header .= '<tr>';
		foreach ( $columns as $item ) {
			$header .= '<td style="background-color: #555555;' . $this->cell_padding_style . '"><p style="margin: 0">' . $item . '</p></td>';
		}
		$header .= '</tr>';
		$header .= '</thead>';
		fwrite( $file, $header );

		fwrite( $file, '<tbody>' );
	}

	/**
	 * Writes an end of a table section to given file.
	 *
	 * @param resource $file File handle.
	 *
	 * @since 4.4.0
	 */
	private function write_section_end( $file ) {
		fwrite( $file, '</tbody></table>' );
	}

	/**
	 * Generate the HTML body of the standard Report.
	 *
	 * @param resource $file      File pointer.
	 * @param string   $blog_name Blog name.
	 * @param array    $data      Report data.
	 */
	private function write_alerts_for_blog( $file, $blog_name, array $data ) {
		$columns = $this->get_generic_columns();
		$this->write_section_start( $file, $blog_name, $columns );

		foreach ( $data as $i => $alert ) {
			$date     = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $alert['timestamp'], 'datetime', true, true );
			$bg_color = ( 0 !== $i % 2 ) ? '#f1f1f1' : '#ffffff';
			$r        = '<tr style="background-color: ' . $bg_color . ';">';

			$processed_row_data = array();
			foreach ( $columns as $key => $label ) {
				$value = array_key_exists( $key, $alert ) ? $alert[ $key ] : '';
				if ( 'timestamp' === $key ) {
					$value = $date;
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
			$processed_row_data = apply_filters( 'wsal_generic_report_row_data', $processed_row_data, WSAL_Rep_DataFormat::HTML );

			foreach ( $processed_row_data as $key => $label ) {
				$cell_styling = $this->cell_padding_style;
				if ( 'alert_id' === $key ) {
					$cell_styling .= ' text-align: center; font-weight: 700;';
				} elseif ( 'user_displayname' === $key ) {
					$cell_styling .= ' min-width: 100px;';
				} elseif ( 'message' === $key ) {
					$cell_styling .= ' ' . $this->message_cell_style . ' word-break: break-all; line-height: 1.5;';
				}

				$r .= '<td style="' . $cell_styling . '">' . $label . '</td>';
			}

			$r .= '</tr>';
			fwrite( $file, $r );
		}

		$this->write_section_end( $file );
	}

	/**
	 * Populates the document closing tags.
	 *
	 * @return string
	 */
	private function get_document_closing_tag() {
		return '</body></html>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_unique_ips( string $filename, array $data, $date_start, $date_end ) {
		$report_filename = $filename . '.html';
		$report_filepath = $this->reports_dir_path . $report_filename;

		$file = fopen( $report_filepath, 'w' );
		fwrite( $file, $this->get_document_opening_tag() );
		fwrite( $file, '<div class="wsal_report_wrap" style="margin: 20px 25px; font-family: Arial;">' );
		fwrite( $file, $this->build_report_header() );

		$this->write_statistical_report_core( $file, esc_html__( 'Results', 'wp-security-audit-log' ), $data );

		fwrite( $file, '</div>' );
		fwrite( $file, $this->get_document_closing_tag() );
		fclose( $file );

		return $report_filename;
	}
}
