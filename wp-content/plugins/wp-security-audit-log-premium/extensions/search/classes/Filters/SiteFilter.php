<?php
/**
 * Object Filter
 *
 * Object filter for search.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_SiteFilter' ) ) :

	/**
	 * WSAL_AS_Filters_SitesFilter.
	 *
	 * Object filter class.
	 */
	class WSAL_AS_Filters_SiteFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'Site', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'site' );
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
			// bail early if this is not a multisite.
			if ( ! is_multisite() ) {
				return;
			}

			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_SiteWidget( $this, 'site', esc_html__( 'Sites', 'wp-security-audit-log' ) );

			// Get event objects.
			// TODO: consider making this a transient so we don't need a limit.
			$sites = get_sites(
				array(
					'number' => 15,
					'fields' => 'ids',
				)
			);

			// Add select options to widget.
			foreach ( $sites as $site ) {
				$details = get_blog_details( $site );
				$name    = $details->blogname;
				$widget->add( $name, $site . ': ' . $name );
			}

			return array( $widget );
		}

		/**
		 * {@inheritDoc}
		 */
		public function modify_query( $query, $prefix, $value ) {
			// Check prefix.
			switch ( $prefix ) {
				case 'site':
					$sql = ' site_id = %s ';
					$query->add_or_condition( array( $sql => $value ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
