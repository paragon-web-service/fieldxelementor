<?php
/**
 * Class: Abstract Filter
 *
 * Abstract filter class.
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
 * Class WSAL_AS_Filters_AbstractFilter
 *
 * @package wsal
 * @subpackage search
 */
abstract class WSAL_AS_Filters_AbstractFilter {

	/**
	 * Whether to print title for filter or not.
	 *
	 * @var boolean
	 */
	public $has_title = false;

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	public $plugin;

	/**
	 * Method: Constructor.
	 *
	 * @param WSAL_SearchExtension $search_extension – Instance of the search extension.
	 *
	 * @since 3.1.0
	 */
	public function __construct( $search_extension ) {
		$this->plugin = $search_extension->plugin;
	}

	/**
	 * Returns true if this filter has suggestions for this query.
	 *
	 * @param string $query The part of query to check.
	 * @return boolean If filter has suggestions for query or not.
	 */
	abstract public function is_applicable( $query );

	/**
	 * List of filter prefixes (the stuff before the colon).
	 *
	 * @return array
	 */
	abstract public function get_prefixes();

	/**
	 * List of widgets to be used in UI.
	 *
	 * @return WSAL_AS_Filters_AbstractWidget[]
	 */
	abstract public function get_widgets();

	/**
	 * Filter name (used in UI).
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Allow this filter to change the DB query according to the search value (usually a value from GetOptions()).
	 *
	 * @param WSAL_Models_OccurrenceQuery $query Database query for selecting occurrences.
	 * @param string                      $prefix The filter name (filter string prefix).
	 * @param string                      $value The filter value (filter string suffix).
	 * @throws Exception Thrown when filter is unsupported.
	 */
	abstract public function modify_query( $query, $prefix, $value );

	/**
	 * Renders filter widgets.
	 */
	public function render() {
		if ( $this->has_title ) {
			echo '<strong>' . esc_html( $this->get_name() ) . '</strong>';
		}

		foreach ( $this->get_widgets() as $widget ) {
			?>
			<div class="wsal-as-filter-widget">
				<?php $widget->render(); ?>
			</div>
			<?php
		}
	}

	/**
	 * Generates a widget name.
	 *
	 * @return string
	 */
	public function get_safe_name() {
		return strtolower( get_class( $this ) );
	}
}
