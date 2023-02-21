<?php
/**
 * Filter: Post Name Filter
 *
 * Post Name filter for search.
 *
 * @since 3.2.3
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WSAL\Adapter\WSAL_Adapters_MySQL_Occurrence;
use WSAL\Adapter\WSAL_Adapters_MySQL_Meta;

if ( ! class_exists( 'WSAL_AS_Filters_PostNameFilter' ) ) :

	/**
	 * WSAL_AS_Filters_PostNameFilter.
	 *
	 * Post name filter class.
	 */
	class WSAL_AS_Filters_PostNameFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Post Name' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array(
				'postname',
			);
		}

		/**
		 * {@inheritDoc}
		 */
		public function is_applicable( $query ) {
			return true;
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			return array( new WSAL_AS_Filters_PostNameWidget( $this, 'postname', esc_html__( 'Post Name', 'wp-security-audit-log' ) ) );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Get DB connection array.
			$connection = $this->plugin->get_connector()->get_adapter( 'Occurrence' )->get_connection();

			// Tables.
			$meta       = new WSAL_Adapters_MySQL_Meta( $connection );
			$table_meta = $meta->get_table(); // Metadata.
			$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
			$table_occ  = $occurrence->get_table(); // Occurrences.

			// Post name search condition.
			$sql   = "$table_occ.id IN ( SELECT occurrence_id FROM $table_meta as meta WHERE meta.name='PostTitle' AND ( ";
			$value = array_map( array( $this, 'add_string_wildcards' ), $value );

			// Get the last post name.
			$last_name = end( $value );

			foreach ( $value as $post_name ) {
				if ( $last_name === $post_name ) {
					continue;
				} else {
					$sql .= "( (meta.value LIKE '$post_name') > 0 ) OR ";
				}
			}

			// Add placeholder for the last post id.
			$sql .= "( (meta.value LIKE '%s') > 0 ) ) )";

			// Check prefix.
			switch ( $prefix ) {
				case 'postname':
					$query->add_or_condition( array( $sql => $last_name ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}

		/**
		 * Modify post name values to include MySQL wildcards.
		 *
		 * @param string $search_value – Searched post name.
		 * @return string
		 */
		private function add_string_wildcards( $search_value ) {
			return '%' . $search_value . '%';
		}
	}

endif;
