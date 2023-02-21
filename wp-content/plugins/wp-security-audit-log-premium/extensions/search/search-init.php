<?php
/**
 * Extension: Search Filters
 *
 * Search filters extension for wsal.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_SearchExtension search widget.
 *
 * @package    wsal
 * @subpackage search
 */
class WSAL_SearchExtension {

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	public $plugin;

	/**
	 * Instance of WSAL_AS_FilterManager.
	 *
	 * @var WSAL_AS_FilterManager
	 */
	public $filters;

	/**
	 * Instance of WSAL_Views_AuditLog.
	 *
	 * @var WSAL_Views_AuditLog
	 */
	public $view_notice;

	/**
	 * Instance of WSAL_SearchExtension.
	 *
	 * @var WSAL_SearchExtension
	 */
	protected static $instance;

	/**
	 * Extension directory path.
	 *
	 * @var string
	 */
	public $base_dir;

	/**
	 * Extension directory url.
	 *
	 * @var string
	 */
	public $base_url;

	const CLS_AUDIT_LOG = 'WSAL_Views_AuditLog';

	/**
	 * Method: Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'wsal_init', array( $this, 'wsal_init' ) );
		add_filter( 'wsal_auditlog_query', array( $this, 'wsal_auditlog_query' ) );
		add_action( 'wsal_auditlog_before_view', array( $this, 'wsal_auditlog_before_view' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		add_action( 'wp_ajax_WsalAsWidgetAjax', array( $this, 'admin_ajax_widget' ) );
		add_action( 'wp_ajax_wsal_get_save_search', array( $this, 'get_save_search' ) );
		add_action( 'wp_ajax_wsal_delete_save_search', array( $this, 'delete_save_search' ) );
		add_filter( 'search_extension_active', '__return_true' );
		self::$instance = $this;

		$this->base_dir = WSAL_BASE_DIR . 'extensions/search';
		$this->base_url = WSAL_BASE_URL . 'extensions/search';
	}

	/**
	 * WSAL_SearchExtension Returns the current plugin instance.
	 *
	 * @return object
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 *
	 * @see WpSecurityAuditLog::load()
	 */
	public function wsal_init( WpSecurityAuditLog $wsal ) {
		// Keep a reference to plugin.
		$this->plugin = $wsal;

		// Load filters.
		$this->filters = new WSAL_AS_FilterManager( self::$instance );

		// register a notice to the main view for tracking if a user has hidden
		// the filters changed notice permanently.
		if ( isset( $this->plugin->views->views ) && is_array( $this->plugin->views->views ) ) {
			$main_view = $this->plugin->views->views[0];
			if ( $main_view instanceof WSAL_Views_AuditLog ) {
				$main_view->register_notice( 'filters-changed-permanent-hide' );
			}
		}

	}

	/**
	 * Filter the query.
	 *
	 * @param WSAL_Models_OccurrenceQuery $query - Instance of WSAL_Models_OccurrenceQuery.
	 *
	 * @see WSAL_AuditLogListView::prepare_items()
	 */
	public function wsal_auditlog_query( $query ) {
		// Get search terms & filters.
		// @codingStandardsIgnoreStart
		$search_term      = ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) ? trim( sanitize_text_field( $_REQUEST['s'] ) ) : false;
		$search_filters   = ( isset( $_REQUEST['filters'] ) && is_array( $_REQUEST['filters'] ) ) ? array_map( 'sanitize_text_field', $_REQUEST['filters'] ) : false;
		$search_save_name = ( isset( $_REQUEST['wsal-save-search-name'] ) && ! empty( $_REQUEST['wsal-save-search-name'] ) ) ? trim( sanitize_text_field( $_REQUEST['wsal-save-search-name'] ) ) : false;
		// @codingStandardsIgnoreEnd

		// Handle text search.
		if ( $search_term ) {
			// Handle free text search.
			$query->add_search_condition( $search_term );
		} else {
			// fixes #4 (@see WP_List_Table::search_box).
			$_REQUEST['s'] = ' ';
		}

		// Handle filter search.
		$filters_arr = array();
		if ( ! empty( $search_filters ) && is_array( $search_filters ) ) {
			// Remove duplicate search criteria.
			$search_filters = array_unique( $search_filters );
			foreach ( $search_filters as $filter ) {
				$filter = explode( ':', $filter, 2 );
				if ( isset( $filter[1] ) ) {
					// remap 'severity' to internal name 'code'.
					$filter[0] = ( 'severity' === $filter[0] ) ? 'code' : $filter[0];
					// Group the filter by type.
					$filters_arr[ $filter[0] ][] = $filter[1];
				}
			}
			foreach ( $filters_arr as $prefix => $value ) {
				$the_filter = $this->filters->find_filter_by_prefix( $prefix );
				$the_filter->modify_query( $query, $prefix, $value );
			}
		}

		// Handle Save Search Request.
		if ( ! empty( $search_save_name ) ) {
			// Sanitize Search Name. Only spaces and alphanumeric characters are allowed.
			$save_search_name = preg_replace( '/[^a-z0-9_]+/i', '', $search_save_name );
			$save_search_name = substr( $save_search_name, 0, 12 );

			if ( ! empty( $save_search_name ) ) {
				// Initialize save array.
				$save_search_arr         = array();
				$save_search_arr['name'] = $save_search_name;

				if ( $search_term ) {
					$save_search_arr['search_input'] = $search_term;
				}

				if ( ! empty( $search_filters ) && is_array( $search_filters ) ) {
					$save_search_arr['filters'] = $search_filters;
				}

				// Get saved array from db.
				$saved_searches = \WSAL\Helpers\Settings_Helper::get_option_value( 'save_search', array() );

				// Check if a search with the same filters does not already exist.
				if ( $this->has_matching_search( $saved_searches, $save_search_arr ) ) {
					// This would be ideally done using an admin notice, but this runs too late in our custom view,
					// and therefore it's safe to simply print the error notice.
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'An identical search filter already exists.', 'wp-security-audit-log' ) . '</p>';
					echo '</div>';
				} else {
					// Append current search with saved searches array.
					$saved_searches[ $save_search_name ] = $save_search_arr;
					\WSAL\Helpers\Settings_Helper::set_option_value( 'save_search', $saved_searches );
				}
			}
		}

		return $query;
	}

	/**
	 * Search Box View
	 *
	 * @param WP_List_Table $view – a view object.
	 */
	public function wsal_auditlog_before_view( WP_List_Table $view ) {
		$view->search_box( __( 'Search', 'wp-security-audit-log' ), 'wsal-as-search' );
		?>
		<input type="hidden" id="wsal-admin-url" value="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" />
		<!-- WP Admin Ajax URL -->
		<?php
		$search_filters = ( isset( $_REQUEST['filters'] ) && is_array( $_REQUEST['filters'] ) ) ? array_map( 'sanitize_text_field', $_REQUEST['filters'] ) : false; // phpcs:ignore

		if ( ! empty( $search_filters ) && is_array( $search_filters ) ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready( function() {
					window.WsalAs.Attach( function() {
						WsalAs.list.html( '' );
						<?php foreach ( $search_filters as $filter ) { ?>
							WsalAs.AddFilter(<?php echo json_encode( $filter ); // phpcs:ignore ?>);
						<?php } ?>
					});
				});
			</script>
			<?php
		}
	}

	/**
	 * Enqueue Search CSS & Scripts.
	 */
	public function admin_enqueue_scripts() {
		if ( $this->is_audit_log_page() ) {
			$plugins_url = $this->base_url . '/resources/';
			wp_enqueue_style(
				'auditlog-as',
				$plugins_url . 'auditlog.css',
				array(),
				WSAL_VERSION
			);

			WSAL_Helpers_Assets::load_datepicker();

			foreach ( $this->filters->get_widgets() as $widgets ) {
				$widgets[0]->static_header();
				foreach ( $widgets as $widget ) {
					$widget->dynamic_header();
				}
			}
		}
	}

	/**
	 * Enqueue Search Scripts in Admin Footer.
	 */
	public function admin_footer() {
		if ( $this->is_audit_log_page() ) {
			// Autocomplete script.
			wp_enqueue_script(
				'typeahead-bundle',
				$this->base_url . '/resources/typeahead.bundle.min.js',
				array( 'jquery' ),
				WSAL_VERSION,
				false
			);

			// WSAL search script.
			wp_register_script(
				'auditlog-as',
				$this->base_url . '/resources/auditlog-search.js',
				array( 'typeahead-bundle', 'auditlog' ),
				WSAL_VERSION,
				true
			);

			// Translations array to be passed to the search script.
			$translation_arr = array(
				'search'          => esc_html__( 'Search', 'wp-security-audit-log' ),
				'search_tooltip'  => esc_html__( '- Use the free-text search to search for text in the event\'s message.<br>- To search for a particular Event ID, user, IP address, Post ID or Type or use date ranges, use the filters.', 'wp-security-audit-log' ),
				'clear_search'    => esc_html__( 'Clear Search Results', 'wp-security-audit-log' ),
				'nothing'         => esc_html__( 'Nothing found!', 'wp-security-audit-log' ),
				'search_load'     => esc_html__( 'Load', 'wp-security-audit-log' ),
				'search_loading'  => esc_html__( 'Loading...', 'wp-security-audit-log' ),
				'search_run'      => esc_html__( 'Load & Run', 'wp-security-audit-log' ),
				'search_delete'   => esc_html__( 'Delete', 'wp-security-audit-log' ),
				'search_deleting' => esc_html__( 'Deleting...', 'wp-security-audit-log' ),
				'search_deleted'  => esc_html__( 'Deleted', 'wp-security-audit-log' ),
				'btn_load'        => esc_html__( 'Load Search & Filters', 'wp-security-audit-log' ),
				'invalid_ip'      => esc_html__( '* Invalid IP', 'wp-security-audit-log' ),
				'remove'          => esc_html__( 'Remove', 'wp-security-audit-log' ),
				'filterBtnOpen'   => esc_html__( 'Close Filters', 'wp-security-audit-log' ),
				'filterBtnClose'  => esc_html__( 'Filter View', 'wp-security-audit-log' ),
				'filterChangeMsg' => sprintf(
					/* translators: both placeholders are html formatting strings for itallics */
					esc_html__( 'Click the %1$sSearch%2$s button to apply the filters. Click the %1$sClear Search Results%2$s button to reset the search and filters.', 'wp-security-audit-log' ),
					'<i>',
					'</i>'
				),
			);
			wp_localize_script( 'auditlog-as', 'translation_string', $translation_arr );

			// Enqueue script.
			wp_enqueue_script( 'auditlog-as' );
		}
	}

	/**
	 * Print filters individual scripts.
	 */
	public function admin_print_footer_scripts() {
		if ( $this->is_audit_log_page() ) {
			foreach ( $this->filters->get_widgets() as $widgets ) {
				$widgets[0]->static_footer();
				foreach ( $widgets as $widget ) {
					$widget->dynamic_footer();
				}
			}
		}
	}

	/**
	 * Admin ajax handler.
	 *
	 * @throws Exception - Exception if widget is not found.
	 *
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 * phpcs:disable WordPress.Security.NonceVerification.Recommended
	 */
	public function admin_ajax_widget() {
		try {
			if ( ! isset( $_REQUEST['filter'] ) ) {
				throw new Exception( 'Parameter "filter" is required.' );
			}
			if ( ! isset( $_REQUEST['widget'] ) ) {
				throw new Exception( 'Parameter "widget" is required.' );
			}
			if ( ! $this->plugin ) {
				throw new Exception( 'Ajax handler "' . __FUNCTION__ . '" was called too early.' );
			}

			$widget = $this->filters->find_widget( $_REQUEST['filter'], $_REQUEST['widget'] ); // phpcs:ignore

			if ( ! $widget ) {
				throw new Exception( __( 'Widget could not be found.', 'wp-security-audit-log' ) );
			}

			$widget->handle_ajax();
			die;
		} catch ( Exception $ex ) {
			die(
				json_encode( // phpcs:ignore
					(object) array(
						'mesg' => $ex->getMessage(),
						'line' => $ex->getLine(),
						'file' => basename( $ex->getFile() ),
					)
				)
			);
		}
	}

	/**
	 * Check if current page is audit log's page.
	 */
	protected function is_audit_log_page() {
		if ( empty( $this->plugin->views ) ) {
			return false;
		}

		$view = $this->plugin->views->get_active_view();
		return null !== $this->plugin                        // Is wsal set up?
			&& $view                                       // Is there an active view?
			&& get_class( $view ) === self::CLS_AUDIT_LOG; // Is the view AuditLog?
	}

	/**
	 * Method: Function to handle ajax request for
	 * getting saved searches.
	 *
	 * @since 1.1.7
	 */
	public function get_save_search() {
		$post_data = $_POST; // phpcs:ignore
		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'load-saved-search-action' ) ) {
			// Nonce verification failed.
			$response = array(
				'success' => false,
				'message' => __( 'Nonce verification failed.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;
			die();
		}
		// Get search results.
		$results        = \WSAL\Helpers\Settings_Helper::get_option_value( 'save_search', array() );
		$search_results = array();

		if ( ! empty( $results ) && is_array( $results ) ) {
			// Convert saved searches array into simple associative array for JS.
			foreach ( $results as $result ) {
				$search_results[] = $result;
			}

			$response = array(
				'search_results' => $search_results,
				'success'        => true,
				'message'        => __( 'Saved searches found.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;
		} else {
			// No saved search is present.
			$response = array(
				'success' => false,
				'message' => __( 'No saved search found.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;
		}

		die();
	}

	/**
	 * Method: Function to handle ajax request for
	 * deleting saved search.
	 *
	 * @since 1.1.7
	 */
	public function delete_save_search() {
		$post_data = $_POST; // phpcs:ignore
		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'load-saved-search-action' ) ) {
			// Nonce verification failed.
			$response = array(
				'success' => false,
				'message' => __( 'Nonce verification failed.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;
			die;
		}

		// Get name to be deleted.
		$delete_name = ( isset( $post_data['name'] ) ) ? $post_data['name'] : false;

		if ( empty( $delete_name ) ) {
			$response = array(
				'success' => true,
				'message' => __( 'Search name not specified.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;
			die();
		}

		// Get search results.
		$results = \WSAL\Helpers\Settings_Helper::get_option_value( 'save_search', array() );

		if ( ! empty( $results ) && is_array( $results ) ) {

			if ( array_key_exists( $delete_name, $results ) ) {
				// If the array key exits in saved searches array then unset it.
				unset( $results[ $delete_name ] );
				\WSAL\Helpers\Settings_Helper::set_option_value( 'save_search', $results );

				$response = array(
					'success' => true,
					'message' => __( 'Saved search deleted.', 'wp-security-audit-log' ),
				);
				$response = json_encode( $response ); // phpcs:ignore
				echo $response;
			} else {
				// Search not found.
				$response = array(
					'success' => true,
					'message' => __( 'Saved search not found.', 'wp-security-audit-log' ),
				);
				$response = json_encode( $response ); // phpcs:ignore
				echo $response;
			}
		} else {
			// No saved search is present.
			$response = array(
				'success' => true,
				'message' => __( 'No saved search found.', 'wp-security-audit-log' ),
			);
			$response = json_encode( $response ); // phpcs:ignore
			echo $response;

		}

		die();
	}

	/**
	 * Checks if an array of existing searches contains a new search. It ignores the search names.
	 *
	 * @param array $existing_searches Existing (saved) searches.
	 * @param array $new_search New search (about to be saved).
	 *
	 * @return bool True if there is a matching search already.
	 *
	 * @since 4.1.4
	 */
	private function has_matching_search( $existing_searches, $new_search ) {
		if ( is_array( $existing_searches ) && ! empty( $existing_searches ) ) {
			foreach ( $existing_searches as $existing_filter ) {
				unset( $existing_filter['name'] );
				unset( $new_search['name'] );
				if ( $existing_filter === $new_search ) {
					return true;
				}
			}
		}

		return false;
	}
}
