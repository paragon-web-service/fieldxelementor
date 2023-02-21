<?php
/**
 * Class: Filter Manager
 *
 * Filter Manager for search extension.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \WSAL\Helpers\Classes_Helper;

/**
 * Class WSAL_AS_FilterManager
 *
 * @package    wsal
 * @subpackage search
 */
class WSAL_AS_FilterManager {

	/**
	 * Array of filters.
	 *
	 * @var WSAL_AS_Filters_AbstractFilter[]
	 */
	protected $filters = array();

	/**
	 * Widget cache.
	 *
	 * @var WSAL_AS_Filters_AbstractWidget[]
	 */
	protected $widgets = null;

	/**
	 * Instance of WSAL_SearchExtension.
	 *
	 * @var WSAL_SearchExtension
	 */
	protected $plugin;

	/**
	 * Method: Constructor.
	 *
	 * @param WSAL_SearchExtension $plugin Instance of WSAL_SearchExtension.
	 * @since 1.0.0
	 */
	public function __construct( WSAL_SearchExtension $plugin ) {
		$this->plugin = $plugin;

		$class_map = Classes_Helper::get_subclasses_of_class( __CLASS__, 'WSAL_AS_Filters_AbstractFilter' );

		foreach ( $class_map as $class_name ) {
			$this->add_instance( new $class_name( $this->plugin ) );
		}

		add_action( 'wsal_audit_log_column_header', array( $this, 'display_filters' ), 10, 1 );
		add_action( 'wsal_search_filters_list', array( $this, 'display_search_filters_list' ), 10, 1 );
	}

	/**
	 * Add newly created filter to list.
	 *
	 * @param WSAL_AS_Filters_AbstractFilter $filter The new view.
	 */
	public function add_instance( $filter ) {
		$this->filters[] = $filter;
		// Reset widget cache.
		if ( is_null( $this->widgets ) ) {
			$this->widgets = null;
		}
	}

	/**
	 * Get filters.
	 *
	 * @return WSAL_AS_Filters_AbstractFilter[]
	 */
	public function get_filters() {
		return $this->filters;
	}

	/**
	 * Gets widgets grouped in arrays with widget class as key.
	 *
	 * @return WSAL_AS_Filters_AbstractWidget[][]
	 */
	public function get_widgets() {
		if ( is_null( $this->widgets ) ) {
			$this->widgets = array();
			foreach ( $this->filters as $filter ) {
				$get_widget = $filter->get_widgets();
				if ( ! empty( $get_widget ) ) {
					foreach ( $filter->get_widgets() as $widget ) {
						$class = get_class( $widget );
						if ( ! isset( $this->widgets[ $class ] ) ) {
							$this->widgets[ $class ] = array();
						}
						$this->widgets[ $class ][] = $widget;
					}
				}
			}
		}
		return $this->widgets;
	}

	/**
	 * Find widget given filter and widget name.
	 *
	 * @param string $filter_name - Filter name.
	 * @param string $widget_name - Widget name.
	 * @return WSAL_AS_Filters_AbstractWidget|null
	 */
	public function find_widget( $filter_name, $widget_name ) {
		foreach ( $this->filters as $filter ) {
			if ( $filter->get_safe_name() === $filter_name ) {
				foreach ( $filter->get_widgets() as $widget ) {
					if ( $widget->get_safe_name() === $widget_name ) {
						return $widget;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Find a filter given a supported prefix.
	 *
	 * @param string $prefix Filter prefix.
	 * @return WSAL_AS_Filters_AbstractFilter|null
	 */
	public function find_filter_by_prefix( $prefix ) {
		foreach ( $this->filters as $filter ) {
			if ( in_array( $prefix, $filter->get_prefixes(), true ) ) {
				return $filter;
			}
		}
		return null;
	}

	/**
	 * Display column filters.
	 *
	 * @param string $column_key – Column key.
	 *
	 * @return string
	 * @since 3.2.3
	 */
	public function display_filters( $column_key ) {

		// For WSAL this is being moved elsewhere so returning early.
		if ( isset( $column_key ) ) {
			return;
		}
		/**
		 * Bail early if we have a match against this list of EXCLUDES.
		 *
		 * NOTE: Consider making this a filterable property.
		 */
		if ( in_array( $column_key, array( 'code', 'data', 'site' ), true ) ) {
			return;
		}

		// Sorting filter icon.
		echo '<a href="javascript:;" id="wsal-search-filter-' . esc_attr( $column_key ) . '" class="wsal-search-filter dashicons dashicons-filter"></a>';

		// Filter container.
		echo '<div id="wsal-filter-container-' . esc_attr( $column_key ) . '" class="wsal-filter-container">';

		// Close filter button.
		echo '<span data-container-id="wsal-filter-container-' . esc_attr( $column_key ) . '" class="dashicons dashicons-no-alt wsal-filter-container-close"></span>';

		/*
		 * Render the html for the given form col.
		 *
		 * TODO: Make this via a render factory.
		 */
		switch ( $column_key ) {
			case 'type':
				// Add event code filter widget.
				$filter = $this->find_filter_by_prefix( 'event' );

				// If filter is found, then add to container.
				if ( $filter ) {
					$filter->render();
				}
				echo '<p class="description">';
				echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/" target="_blank">list of Event IDs</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags );
				echo '</p>';
				break;

			case 'crtd':
				// Add date filter widget.
				$date = $this->find_filter_by_prefix( 'from' );

				// If from date filter is found, then add to container.
				if ( $date ) {
					$date->render();
				}
				break;

			case 'user':
				// Add username filter widget.
				$username = $this->find_filter_by_prefix( 'username' );

				// If username filter is found, then add to container.
				if ( $username ) {
					$username->render();
				}

				// Add firstname filter widget.
				$firstname = $this->find_filter_by_prefix( 'firstname' );

				// If firstname filter is found, then add to container.
				if ( $firstname ) {
					$firstname->render();
				}

				// Add lastname filter widget.
				$lastname = $this->find_filter_by_prefix( 'lastname' );

				// If lastname filter is found, then add to container.
				if ( $lastname ) {
					$lastname->render();
				}

				// Add userrole filter widget.
				$userrole = $this->find_filter_by_prefix( 'userrole' );

				// If userrole filter is found, then add to container.
				if ( $userrole ) {
					$userrole->render();
				}
				break;

			case 'mesg':
				// Add post_status filter widget.
				$post_status = $this->find_filter_by_prefix( 'poststatus' );

				// If post_status filter is found, then add to container.
				if ( $post_status ) {
					$post_status->render();
				}

				// Add post_type filter widget.
				$post_type = $this->find_filter_by_prefix( 'posttype' );

				// If post_type filter is found, then add to container.
				if ( $post_type ) {
					$post_type->render();
				}

				// Add post_id filter widget.
				$post_id = $this->find_filter_by_prefix( 'postid' );

				// If post_id filter is found, then add to container.
				if ( $post_id ) {
					$post_id->render();
				}

				// Add post_name filter widget.
				$post_name = $this->find_filter_by_prefix( 'postname' );

				// If post_name filter is found, then add to container.
				if ( $post_name ) {
					$post_name->render();
				}
				break;

			case 'scip':
				// Add ip filter widget.
				$ip = $this->find_filter_by_prefix( 'ip' );

				// If ip filter is found, then add to container.
				if ( $ip ) {
					$ip->render();
				}
				break;

			case 'object':
				// Add object filter widget.
				$object = $this->find_filter_by_prefix( 'object' );

				// If object filter is found, then add to container.
				if ( $object ) {
					$object->render();
				}
				break;

			case 'event_type':
				// Add event type filter widget.
				$event_type = $this->find_filter_by_prefix( 'event-type' );

				// If event type filter is found, then add to container.
				if ( $event_type ) {
					$event_type->render();
				}
				break;

			case 'code':
				// Add code (Severity) filter widget.
				$code = $this->find_filter_by_prefix( 'code' );

				// If code filter is found, then add to container.
				if ( $code ) {
					$code->render();
				}
				break;

			case 'site':
				// Add code (Severity) filter widget.
				$site = $this->find_filter_by_prefix( 'site' );

				// If code filter is found, then add to container.
				if ( $site ) {
					$site->render();
				}
				break;

			default:
		}

		echo '</div>';
	}

	/**
	 * Display list of search filters, load, and save search
	 * buttons and their pop-ups.
	 *
	 * @param string $nav_position – Table navigation position.
	 */
	public function display_search_filters_list( $nav_position ) {
		if ( empty( $nav_position ) ) {
			return;
		}

		if ( 'top' === $nav_position ) :
			$saved_search = \WSAL\Helpers\Settings_Helper::get_option_value( 'save_search', array() );
			?>
			<div class="wsal-as-filter-list no-filters"></div>
			<!-- Filters List -->
			<?php
			/*
			 * This is a notice which shows when the filters have been changed.
			 *
			 * Check if the user has permanently disabled it.
			 */
			if ( ! $this->plugin->plugin->views->views[0]->is_notice_dismissed( 'filters-changed-permanent-hide' ) ) {
				?>
				<div class="wsal-filter-notice-zone" style="display:none;">
					<p><span class="wsal-notice-message"></span> <a id="wsal-filter-notice-permanant-dismiss" href="javascript:;"><?php esc_html_e( 'Do not show this message again', 'wp-security-audit-log' ); ?></a></p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wp-security-audit-log' ); ?></span></button>
				</div>
				<!-- Filters Notices -->
				<?php
			}
			?>
			<div class="load-search-container">
				<button type="button" id="load-search-btn" class="button-secondary button wsal-button" <?php echo empty( $saved_search ) ? 'disabled' : false; ?>>
					<?php esc_html_e( 'Load Search & Filters', 'wp-security-audit-log' ); ?>
				</button>
				<div class="wsal-load-popup" style="display:none">
					<a class="close" href="javascript;" title="<?php esc_attr_e( 'Remove', 'wp-security-audit-log' ); ?>">&times;</a>
					<div class="wsal-load-result-list"></div>
				</div>
				<?php wp_nonce_field( 'load-saved-search-action', 'load_saved_search_field' ); ?>
			</div>
			<!-- Load Search & Filters Container -->

			<div class="save-search-container">
				<a href="javascript:;" id="save-search-btn" class="button wsal-button">
					<?php esc_html_e( 'Save Search & Filters', 'wp-security-audit-log' ); ?>
					<img src="<?php echo esc_url( WSAL_BASE_URL . '/img/icons/save-search.svg' ); ?>" class="save-search-icon" />
				</a>
				<div class="wsal-save-popup" style="display: none;">
					<input name="wsal-save-search-name" id="wsal-save-search-name" placeholder="Search Save Name" />
					<span id="wsal-save-search-error"><?php esc_html_e( '* Invalid Name', 'wp-security-audit-log' ); ?></span>
					<p class="description">
						<?php esc_html_e( 'Name can only be 12 characters long and only letters, numbers and underscore are allowed.', 'wp-security-audit-log' ); ?>
					</p>
					<p class="description">
						<button type="submit" id="wsal-save-search-btn" class="button-primary"><?php esc_html_e( 'Save', 'wp-security-audit-log' ); ?></button>
					</p>
				</div>
			</div>
			<div class="wsal-button-grouping">
				<div class="filter-results-button">
					<button id="filter-container-toggle" class="button wsal-button dashicons-before dashicons-filter" type="button"><?php esc_html_e( 'Filter View', 'wp-security-audit-log' ); ?></button>
				</div>
			</div>
			<!-- Save Search & Filters Container -->
			<div id="wsal-filters-container" style="display:none">
				<div class="filter-col">
					<?php
					// Add event code filter widget.
					$filter = $this->find_filter_by_prefix( 'event' );

					// If filter is found, then add to container.
					if ( $filter ) {
						?>
						<div class="filter-wrap">
							<?php $filter->render(); ?>
							<p class="description"><?php echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/" target="_blank" rel="nofollow noopener">list of Event IDs</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}
					// Add object filter widget.
					$object = $this->find_filter_by_prefix( 'object' );

					// If object filter is found, then add to container.
					if ( $object ) {
						?>
						<div class="filter-wrap">
							<?php $object->render(); ?>
							<p class="description"><?php echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/"  target="_blank" rel="nofollow noopener">metadata in the activity log</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}
					// Add event type filter widget.
					$event_type = $this->find_filter_by_prefix( 'event-type' );

					// If event type filter is found, then add to container.
					if ( $event_type ) {
						?>
						<div class="filter-wrap">
							<?php $event_type->render(); ?>
							<p class="description"><?php echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/severity-levels-wordpress-activity-log/" target="_blank" rel="nofollow noopener">severity levels in the activity log</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}

					// Add code (Severity) filter widget.
					$code = $this->find_filter_by_prefix( 'code' );

					// If code filter is found, then add to container.
					if ( $code ) {
						?>
						<div class="filter-wrap">
							<?php $code->render(); ?>
							<p class="description"><?php echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/list-wordpress-audit-trail-alerts/" target="_blank" rel="nofollow noopener">list of Event IDs</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}
					?>
				</div>
				<div class="filter-col">
					<?php
					// Data for generating and rendering users filters with.
					$user_filters = array(
						'username'  => array(
							'display'     => esc_html__( 'Username', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by username', 'wp-security-audit-log' ),
						),
						'firstname' => array(
							'display'     => esc_html__( 'First Name', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by user first name', 'wp-security-audit-log' ),
						),
						'lastname'  => array(
							'display'     => esc_html__( 'Last Name', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by user last name', 'wp-security-audit-log' ),
						),
						'userrole'  => array(
							'display'     => esc_html__( 'User Role', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by user roles', 'wp-security-audit-log' ),
						),
					);
					$this->render_filter_groups( esc_html__( 'User Filters', 'wp-security-audit-log' ), 'user', $user_filters );
					// The data for fetching and rendering posts filters with.
					$post_filters = array(
						'poststatus' => array(
							'display'     => esc_html__( 'Post Status', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by post status', 'wp-security-audit-log' ),
						),
						'posttype'   => array(
							'display'     => esc_html__( 'Post Type', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by post type', 'wp-security-audit-log' ),
						),
						'postid'     => array(
							'display'     => esc_html__( 'Post ID', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by post ID', 'wp-security-audit-log' ),
						),
						'postname'   => array(
							'display'     => esc_html__( 'Post Name', 'wp-security-audit-log' ),
							'description' => esc_html__( 'Filter by post name', 'wp-security-audit-log' ),
						),
					);
					$this->render_filter_groups( esc_html__( 'Post Filters', 'wp-security-audit-log' ), 'post', $post_filters );

					// Show site alerts widget.
					// NOTE: this is shown when the filter IS true.
					if ( is_multisite() && get_current_blog_id() === 1 && apply_filters( 'search_extension_active', false ) ) {

						$curr = WpSecurityAuditLog::get_instance()->settings()->get_view_site_id();
						?>
						<div class="filter-wrap">
							<label for="wsal-ssas"><?php esc_html_e( 'Select Site to view', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-widget-container">
								<?php
								if ( $this->get_site_count() > 15 ) {
									$curr = $curr ? get_blog_details( $curr ) : null;
									$curr = $curr ? ( $curr->blogname . ' (' . $curr->domain . ')' ) : 'All Sites';
									?>
									<input type="text" class="wsal-ssas" value="<?php echo esc_attr( $curr ); ?>"/>
									<?php
								} else {
									// Add code (Severity) filter widget.
									$site = $this->find_filter_by_prefix( 'site' );

									// If code filter is found, then add to container.
									if ( $site ) {
										?>
										<div class="filter-wrap">
											<?php $site->render(); ?>
											<p class="description"><?php echo wp_kses( __( 'Refer to the <a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/" target="_blank" rel="nofollow noopener">list of Event IDs</a> for reference.', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
										</div>
										<?php
									}
								}
								?>
							</div>
							<p class="description"><?php echo wp_kses( esc_html__( 'Select A Specific Site from the Network', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}
					?>
				</div>
				<div class="filter-col filter-dates-col">
					<?php
					// Add date filter widget.
					$date = $this->find_filter_by_prefix( 'from' );

					// If from date filter is found, then add to container.
					if ( $date ) {
						$date->render();
					}
					// Add ip filter widget.
					$ip = $this->find_filter_by_prefix( 'ip' );

					// If ip filter is found, then add to container.
					if ( $ip ) {
						?>
						<div class="filter-wrap">
							<?php $ip->render(); ?>
							<p class="description"><?php echo wp_kses( esc_html__( 'Enter an IP address to filter', 'wp-security-audit-log' ), $this->plugin->plugin->allowed_html_tags ); ?></p>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		endif;
	}

	/**
	 * Renders an entire group of filters in a single area that is paired with
	 * a select box and some javascript show/hide.
	 *
	 * @method render_filter_groups
	 * @since  4.0.0
	 * @param  string $title Title to use as a lable above select box.
	 * @param  string $slug  The slug to use for identifying groups.
	 * @param  array  $group An array containing all the group data. An array with a handle containing an array of strings - `display` and `description`.
	 */
	public function render_filter_groups( $title = '', $slug = '', $group = array() ) {
		?>
		<div class="wsal-filters-group" id="wsal-user-filters">
			<div class="wsal-filter-group-select">
				<label for="wsal-<?php echo esc_attr( 'slug' ); ?>-filters-select"><?php echo esc_html( $title ); ?></label>
				<select id="wsal-<?php echo esc_attr( 'slug' ); ?>-filters-select">
					<?php
					foreach ( $group as $handle => $strings ) {
						// Render item.
						echo '<option value="' . esc_attr( $handle ) . '">' . esc_html( $strings['display'] ) . '</option>';
					}
					?>
				</select>
			</div>
			<div class="wsal-filter-group-inputs">
				<?php
				foreach ( $group as $handle => $strings ) {
					// Add username filter widget.
					$filter = $this->find_filter_by_prefix( $handle );

					// If username filter is found, then add to container.
					if ( $filter ) {
						?>
						<div class="filter-wrap wsal-filter-wrap-<?php echo sanitize_html_class( $handle ); ?>">
							<?php $filter->render(); ?>
							<?php
							if ( isset( $strings['description'] ) && '' !== $strings['description'] ) {
								?>
								<p class="description"><?php echo wp_kses( $strings['description'], $this->plugin->plugin->allowed_html_tags ); ?></p>
								<?php
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php
	}

	/**
	 * Method: The number of sites on the network.
	 *
	 * @return int
	 */
	public function get_site_count() {
		global $wpdb;
		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->blogs;
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore
	}

	/**
	 * Method: Object with keys: blog_id, blogname, domain.
	 *
	 * @param int|null $limit - Maximum number of sites to return (null = no limit).
	 * @return object
	 */
	public function get_sites( $limit = null ) {
		global $wpdb;
		// Build query.
		$sql = 'SELECT blog_id, domain FROM ' . $wpdb->blogs;
		if ( ! is_null( $limit ) ) {
			$sql .= ' LIMIT ' . $limit;
		}

		// Execute query.
		$res = $wpdb->get_results( $sql ); // phpcs:ignore

		// Modify result.
		foreach ( $res as $row ) {
			$row->blogname = get_blog_option( $row->blog_id, 'blogname' );
		}

		// Return result.
		return $res;
	}
}
