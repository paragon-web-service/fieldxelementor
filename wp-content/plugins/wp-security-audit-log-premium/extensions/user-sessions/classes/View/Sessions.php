<?php
/**
 * Class WSAL_UserSessions_View_Sessions.
 *
 * @package wsal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session list view for the user sessions extension.
 *
 * @package wsal
 */
class WSAL_UserSessions_View_Sessions {
	/**
	 * Slug of this tab in the sessions pages.
	 *
	 * @var string
	 */
	public static $slug = 'sessions';

	/**
	 * The array of user sessions to output in the loop.
	 *
	 * @var array
	 */
	public $user_sessions = array();

	/**
	 * Stores all the tracked sessions from custom table so they can be
	 * handled/sorted/processed.
	 *
	 * @var array
	 */
	private $all_tracked_sessions = array();

	/**
	 * Plugin instance.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin Plugin instance.
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		$this->plugin = $plugin;

		$this->register_usersessions_tab();

		// Listen for session search submit.
		add_action( 'admin_post_wsal_sessions_search', array( $this, 'sessions_search_form' ) );
	}

	/**
	 * Registers this tab to the main page and setups the allowed tabs array.
	 *
	 * @method register_usersessions_tab
	 *
	 * @since  4.1.0
	 */
	public function register_usersessions_tab() {
		add_filter(
			'wsal_usersessions_views_nav_header_items',
			function ( $tabs ) {
				$tabs[ self::$slug ] = array(
					'title' => $this->get_title(),
				);

				return $tabs;
			},
			10,
			1
		);
		add_filter(
			'wsal_usersessions_views_allowed_tabs',
			function ( $allowed ) {
				$allowed[] = self::$slug;

				return $allowed;
			},
			10,
			1
		);
	}

	/**
	 * Returns a title to use for this tab/page.
	 *
	 * @method get_title
	 *
	 * @return string
	 *
	 * @since  4.1.0
	 */
	public function get_title() {
		return esc_html__( 'Logged In Users', 'wp-security-audit-log' );
	}

	/**
	 * Render this page or tab html contents.
	 *
	 * @method render
	 *
	 * @since  4.1.0
	 */
	public function render() {
		/**
		 * Loads all the sessions data we may need for rendering this page into
		 * properties.
		 */
		$current_blog_id = (int) $this->get_view_site_id();
		$this->setup_session_data( $current_blog_id );

		/**
		 * Performs the search filtering and displays a message about results.
		 */
		$search_results = array();
		if ( isset( $_GET['keyword'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) ) ) ) {
			try {
				// perform the search filtering.
				$search_results = $this->sessions_search();
				if ( empty( $search_results ) ) {
					$results_notice_text = ( isset( $_GET['sessions-terminated'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['sessions-terminated'] ) ) ) ) ? esc_html__( 'Sessions successfully terminated', 'wp-security-audit-log' ) : esc_html__( 'No search results were found.', 'wp-security-audit-log' );
					// No search results found.
					?>
					<div class="updated">
						<p><?php echo $results_notice_text; // phpcs:ignore ?></p>
					</div>
					<?php
				} else {
					// Display a message with the search term.
					?>
					<div class="updated">
						<p>
							<?php esc_html_e( 'Showing results for ', 'wp-security-audit-log' ); ?>
							<?php
							if ( isset( $_GET['type'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['type'] ) ) ) ) {
								echo \esc_html( \sanitize_text_field( \wp_unslash( $_GET['type'] ) ) );
							}
							?>
							<strong><?php echo \esc_html( \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) ) ); ?></strong>
						</p>
					</div>
					<?php
				}
			} catch ( Exception $ex ) {
				// catching a search failure error.
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: ', 'wp-security-audit-log' ); ?><?php echo esc_html( $ex->getMessage() ); ?></p>
				</div>
				<?php
			}
		}

		// Get the type of name to display from settings.
		$name_column = esc_html__( 'Username', 'wp-security-audit-log' );
		$type_name   = $this->plugin->settings()->get_type_username();
		if ( in_array( $type_name, array( 'display_name', 'first_last_name' ), true ) ) {
			$name_column = esc_html__( 'User', 'wp-security-audit-log' );
		}

		$columns = array(
			'username'      => $name_column,
			'creation_time' => esc_html__( 'Created', 'wp-security-audit-log' ),
			'expiry_time'   => esc_html__( 'Expires', 'wp-security-audit-log' ),
			'ip'            => esc_html__( 'Source IP', 'wp-security-audit-log' ),
			'alert'         => esc_html__( 'Last Event', 'wp-security-audit-log' ),
			'action'        => esc_html__( 'Actions', 'wp-security-audit-log' ),
		);

		// Verify sessions form submission nonce.
		if ( isset( $_GET['wsal-sessions-form'] ) ) {
			check_admin_referer( 'wsal-sessions-form', 'wsal-sessions-form' );
		}

		// @codingStandardsIgnoreStart
		$sorted                    	         = array();
		$spp                       	         = ! empty( $_GET['sessions_per_page'] ) ? absint( $_GET['sessions_per_page'] ) : 10;
		$paged                      	     = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$offset                     	     = absint( ( $paged - 1 ) * $spp );
		$orderby                      	     = ! empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'creation_time';
		$order                         		 = ! empty( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc';
		$search_results_termination_data	 = array();
		$all_search_results_termination_data = array();
		$all_results 						 = array();
		// @codingStandardsIgnoreEnd

		if ( empty( $search_results ) ) {
			// with no results sessions list should be emptied.
			if ( isset( $_GET['keyword'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) ) ) ) {
				$this->user_sessions = array();
			}
			$results = array_slice( $this->user_sessions, $offset, $spp );

			// Create an unsliced variable for later.
			$all_results = $this->user_sessions;
		} else {
			$results = array_slice( $search_results, $offset, $spp );

			// Create an unsliced variable for later.
			$all_results = $search_results;
		}

		foreach ( $results as $user_id => $user_session ) {
			reset( $user_session );
			$session  = current( $user_session );
			$sorted[] = $session[ $orderby ];

			// Make a note of found user IDs and their sessions in case we terminate.
			if ( ! empty( $search_results ) ) {
				$user_session_data = array(
					$user_session[0]['user_id'],
					$user_session[0]['session_token'],
					wp_create_nonce( sprintf( 'destroy_session_nonce-%d', $user_session[0]['user_id'] ) ),
				);
				array_push( $search_results_termination_data, $user_session_data );
			}
		}

		// If we are looking at a specific critera, gather ALL user sessions for termination.
		if ( isset( $_GET['keyword'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) ) ) ) {
			foreach ( $all_results as $user_id => $user_session ) {
				reset( $user_session );
				$session  = current( $user_session );
				$sorted[] = $session[ $orderby ];

				// Make a note of found user IDs and their sessions in case we terminate.
				if ( ! empty( $search_results ) ) {
					$user_session_data = array(
						$user_session[0]['user_id'],
						$user_session[0]['session_token'],
						wp_create_nonce( sprintf( 'destroy_session_nonce-%d', $user_session[0]['user_id'] ) ),
					);
					array_push( $all_search_results_termination_data, $user_session_data );

					// Ensure the results reflect each unique session rather than user for termination.
					foreach ( $this->user_sessions as $user_sessions ) {
						foreach ( $user_sessions as $user_session ) {
							$user_session_data = array(
								$user_session['user_id'],
								$user_session['session_token'],
								wp_create_nonce( sprintf( 'destroy_session_nonce-%d', $user_session['user_id'] ) ),
							);
							array_push( $search_results_termination_data, $user_session_data );
						}
					}
				}
			}
		}

		$total_sessions = empty( $search_results ) ? count( $this->user_sessions ) : count( $search_results );
		$sessions_count = $this->get_per_role_sessions_count( $this->user_sessions );
		$pages          = absint( ceil( $total_sessions / $spp ) );

		$users = $results;

		ob_start();

		// Selected type.
		$type = false;
		if ( isset( $_GET['type'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['type'] ) ) ) ) {
			$type = \sanitize_text_field( \wp_unslash( $_GET['type'] ) );
		}

		// Searched keyword.
		$keyword = false;
		if ( isset( $_GET['type'] ) && ! empty( \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) ) ) ) {
			$keyword = \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) );
		}

		// Pagination first page link.
		$first_link_args['page'] = 'wsal-usersessions-views';
		if ( ! empty( $type ) && ! empty( $keyword ) ) {
			$first_link_args['type']    = $type;
			$first_link_args['keyword'] = $keyword;
		}
		$first_link = add_query_arg( $first_link_args, admin_url( 'admin.php' ) );

		// Pagination last link.
		$last_link_args['paged'] = $pages;
		if ( ! empty( $type ) && ! empty( $keyword ) ) {
			$last_link_args['type']    = $type;
			$last_link_args['keyword'] = $keyword;
		}
		$last_link = add_query_arg( $last_link_args, $first_link );

		// Previous link.
		if ( $paged > 2 ) {
			$prev_link_args = array(
				'paged'             => absint( $paged - 1 ),
				'sessions_per_page' => $spp,
			);
			if ( ! empty( $type ) && ! empty( $keyword ) ) {
				$prev_link_args['type']    = $type;
				$prev_link_args['keyword'] = $keyword;
			}
			$prev_link = add_query_arg( $prev_link_args, $first_link );
		} else {
			$prev_link = $first_link;
		}

		// Next link.
		if ( $pages > $paged ) {
			$next_link_args = array(
				'paged'             => absint( $paged + 1 ),
				'sessions_per_page' => $spp,
			);
			if ( ! empty( $type ) && ! empty( $keyword ) ) {
				$next_link_args['type']    = $type;
				$next_link_args['keyword'] = $keyword;
			}
			$next_link = add_query_arg( $next_link_args, $first_link );
		} else {
			$next_link = $last_link;
		}

		// Calculate the number of sessions after offset.
		$session_token = $total_sessions % 10;

		if ( empty( $search_results ) ) :
			$session_data = array(
				'token'         => $session_token,
				'blog_id'       => $current_blog_id,
				'session_nonce' => wp_create_nonce( 'wsal-session-auto-refresh' ),
			);
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					SessionAutoRefresh('<?php echo wp_json_encode( $session_data ); ?>')
				})</script>
			<?php
		endif;

		// Navigation links buffer start.
		ob_start();
		?>
		<div class="tablenav-pages">
			<?php if ( $pages > 1 ) : ?>
				<span class="pagination-links">
					<a class="button first-page<?php echo ( 1 === $paged ) ? ' disabled' : null; ?>"
							title="<?php esc_attr_e( 'Go to the first page', 'wp-security-audit-log' ); ?>"
							href="<?php echo esc_url( $first_link ); ?>">«</a>
					<a class="button prev-page<?php echo ( 1 === $paged ) ? ' disabled' : null; ?>"
							title="<?php esc_attr_e( 'Go to the previous page', 'wp-security-audit-log' ); ?>"
							href="<?php echo esc_url( $prev_link ); ?>">‹</a>
					<span class="paging-input">
						<?php echo absint( $paged ); ?> <?php esc_html_e( 'of', 'wp-security-audit-log' ); ?> <span
								class="total-pages"><?php echo absint( $pages ); ?></span>
					</span>
					<a class="button next-page<?php echo ( $pages === $paged ) ? ' disabled' : null; ?>"
							title="<?php esc_attr_e( 'Go to the next page', 'wp-security-audit-log' ); ?>"
							href="<?php echo esc_url( $next_link ); ?>">›</a>
					<a class="button last-page<?php echo ( $pages === $paged ) ? ' disabled' : null; ?>"
							title="<?php esc_attr_e( 'Go to the last page', 'wp-security-audit-log' ); ?>"
							href="<?php echo esc_url( $last_link ); ?>">»</a>
				</span>
			<?php endif; ?>
		</div>
		<?php
		// Get navigation links buffer.
		$pagination = ob_get_clean();

		if ( ! empty( $sessions_count ) ) {
			$row_data = array();
			foreach ( wp_roles()->roles as $role_slug => $role ) {
				$value                     = array_key_exists( $role_slug, $sessions_count ) ? $sessions_count[ $role_slug ] : 0;
				$row_data[ $role['name'] ] = array();

				if ( $value > 0 ) {
					$role_search_url = add_query_arg(
						array(
							'page'    => 'wsal-usersessions-views',
							'type'    => 'user-role',
							'keyword' => $role_slug,
						),
						admin_url( 'admin.php' )
					);

					$row_data[ $role['name'] ] = array(
						'value' => $value,
						'url'   => $role_search_url,
					);
				} else {
					$row_data[ $role['name'] ] = $value;
				}
			}

			echo '<div class="card session-totals-overview" ' . ( ( $pages > 1 ) ? 'style="margin-bottom:3px"' : '' ) . '>';
			echo '<h3>' . esc_html__( 'Number of logged in sessions per role', 'wp-security-audit-log' ) . '</h3>';

			echo '<table>';

			echo '<tbody>';
			echo '<tr>';
			$counter = 0;
			foreach ( $row_data as $label => $value_data ) {
				$markup = $label;
				$count  = $value_data;
				if ( is_array( $value_data ) ) {
					$markup = '<a href="' . esc_url( $value_data['url'] ) . '" title="' . esc_attr( $label ) . '">' . $label . '</a>';
					$count  = $value_data['value'];
				}
				echo '<td class="role">' . $markup . '</td>'; // phpcs:ignore
				echo '<td class="count">' . $count . '</td>'; // phpcs:ignore
				echo '<td class="spacer"></td>';
				if ( 3 === $counter % 4 ) {
					echo '</tr><tr>';
				}

				++$counter;
			}
			echo '</tr>';
			echo '</tbody>';

			echo '<tfoot>';
			echo '<tr>';
			echo '<th class="role"></th><th class="count"></th><th class="spacer"></th>';
			echo '<th class="role"></th><th class="count"></th><th class="spacer"></th>';
			echo '<th class="role"></th><th class="count"></th><th class="spacer"></th>';
			echo '<th class="role">' . esc_html__( 'Total', 'wp-security-audit-log' ) . '</th>';
			echo '<th class="count">' . $sessions_count['total'] . '</th>'; // phpcs:ignore
			echo '<th class="spacer"></th>';
			echo '</tr>';
			echo '</tfoot>';
			echo '</table>';
			echo '</div>';
		}
		?>
		<!-- Sessions Search -->
		<?php if ( ! empty( $results ) ) { ?>
			<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="wsal_sessions__search"
					class="wsal_sessions_search" <?php echo ( $pages > 1 ) ? 'style="position:inherit"' : ''; ?>>
				<?php
				// Options array.
				$options_arr = array(
					'username'  => esc_html__( 'Username', 'wp-security-audit-log' ),
					'email'     => esc_html__( 'Email', 'wp-security-audit-log' ),
					'firstname' => esc_html__( 'First Name', 'wp-security-audit-log' ),
					'lastname'  => esc_html__( 'Last Name', 'wp-security-audit-log' ),
					'ip'        => esc_html__( 'IP Address', 'wp-security-audit-log' ),
					'user-role' => esc_html__( 'User Role', 'wp-security-audit-log' ),
				);
				?>
				<select name="type" id="type">
					<?php foreach ( $options_arr as $option_value => $option_text ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>"
								<?php echo ( $option_value === $type ) ? ' selected' : false; ?>>
							<?php echo esc_html( $option_text ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="keyword" id="keyword" value="<?php echo esc_attr( $keyword ); ?>">
				<input type="hidden" name="action" value="wsal_sessions_search">
				<?php wp_nonce_field( 'wsal_session_search__nonce', 'wsal_session_search__nonce' ); ?>
				<input type="submit" class="button" name="wsal_session_search__btn" id="wsal_session_search__btn"
						value="<?php esc_attr_e( 'Search', 'wp-security-audit-log' ); ?>">
			</form>
		<?php } ?>
		<!-- / Sessions Search -->

		<form id="sessionsForm" method="get">
			<?php
			$page                     = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
			$site_id                  = isset( $_GET['wsal-cbid'] ) ? sanitize_text_field( wp_unslash( $_GET['wsal-cbid'] ) ) : '0';
			$fetch_user_session_nonce = wp_create_nonce( 'fetch_user_session_event_data' );
			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
			<input type="hidden" id="wsal-cbid" name="wsal-cbid" value="<?php echo esc_attr( $site_id ); ?>" />
			<input type="hidden" id="wsal-sessions-form" name="wsal-sessions-form"
					value="<?php echo esc_attr( wp_create_nonce( 'wsal-sessions-form' ) ); ?>">
			<div class="tablenav top">
				<?php if ( ! empty( $results ) ) { ?>
					<div class="alignleft actions bulkactions">
						<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'wp-security-audit-log' ); ?></label><select name="action" id="bulk-action-selector-top">
						<option value="-1"><?php esc_html_e( 'Bulk actions', 'wp-security-audit-log' ); ?></option>
							<option value="terminate-sessions"><?php esc_html_e( 'Terminate sessions', 'wp-security-audit-log' ); ?></option>
							<option value="retrieve-events" data-nonce="<?php esc_attr_e( $fetch_user_session_nonce ); ?>"><?php esc_html_e( "Retrieve last users' event", 'wp-security-audit-log' ); ?></option>
						</select>
						<a id="do-session-bulk-action" class="button action" href="#"><?php esc_html_e( 'Apply', 'wp-security-audit-log' ); ?></a>
					</div>
				<?php } ?>
				<?php
				// Show site alerts widget.
				if ( $this->is_multisite() && $this->is_main_blog() ) {
					$curr = $this->get_view_site_id();
					esc_html_e( 'Show:', 'wp-security-audit-log' );
					?>
					<div class="wsal-ssa">
						<?php if ( $this->get_site_count() > 15 ) : ?>
							<?php $curr = $curr ? get_blog_details( $curr ) : null; ?>
							<?php $curr = $curr ? ( $curr->blogname . ' (' . $curr->domain . ')' ) : 'Network-wide Logins'; ?>
							<input type="text" value="<?php echo esc_attr( $curr ); ?>" />
						<?php else : ?>
							<select onchange="WsalSsasChange(value);">
								<option value="0"><?php esc_html_e( 'Network-wide Logins', 'wp-security-audit-log' ); ?></option>
								<?php foreach ( $this->get_sites() as $info ) : ?>
									<option value="<?php echo absint( $info->blog_id ); ?>" <?php echo ( (int) $info->blog_id === (int) $curr ) ? 'selected="selected"' : false; ?>>
										<?php echo esc_html( $info->blogname ) . ' (' . esc_html( $info->domain ) . ')'; ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</div>
					<?php
				}
			echo $pagination; // phpcs:ignore
				if ( ! empty( $results ) ) {
					$this->render_fetch_data_button();
					$this->render_terminate_search_result_sessions_button( $search_results_termination_data, 'currently-shown' );
					// If we have more sessions that the currently displayed page shows, show termination button.
					if ( count( $all_search_results_termination_data ) > $spp ) {
						$this->render_terminate_search_result_sessions_button( $all_search_results_termination_data, 'all' );
					}
					$this->render_multiple_sessions_only_checkbox();
				}
				?>
				<br class="clear">
			</div>
			<table class="wp-list-table widefat fixed users">
				<thead>
					<tr>
						<td class="manage-column column-cb check-column" id="cb" scope="col">
							<label for="cb-select-all-1" class="screen-reader-text"><?php esc_html_e( 'Select All', 'wp-security-audit-log' ); ?></label>
							<input type="checkbox" id="cb-select-all-1" class="session-bulk-check">
						</td>
						<?php foreach ( $columns as $slug => $name ) : ?>
							<th scope="col" class="manage-column column-<?php echo esc_attr( $slug ); ?>">
								<span><?php echo esc_html( $name ); ?></span>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<?php if ( empty( $results ) && isset( $_REQUEST['type'] ) ) { ?>
					<tbody class="no-results-found">
					<tr>
						<td colspan="7">
							<?php esc_html_e( 'No logged in sessions meet your search criteria.', 'wp-security-audit-log' ); ?>
						</td>
					</tr>
					</tbody>
				<?php } elseif ( empty( $results ) ) { ?>
					<tbody class="no-results-found">
					<tr>
						<td colspan="7">
							<?php esc_html_e( 'WP Activity Log keeps its own user session data. This means that the sessions of already logged in users will only show up once they logout and log back in. The same applies to your session.', 'wp-security-audit-log' ); ?>
						</td>
					</tr>
					</tbody>
				<?php } else { ?>
					<tbody id="the-list">
					<?php
					$i = 0;
					foreach ( $users as $user_id => $result_sessions ) :
						$multiples_only = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'wsal_sessions_show_multiple_sessions_only' );
						// If we only want to show multiple session holders only, skip.
						if ( $multiples_only && count( $result_sessions ) == 1 ) {
							continue;
						}
						$load_event_on_click_button_output = false;
						++$i;
						?>
						<tr <?php echo ( 0 !== $i % 2 ) ? 'class="alternate"' : ''; ?>>
							<td colspan="7">
								<table class="wp-list-table widefat fixed users">
									<?php
									foreach ( $result_sessions as $result ) :
										$user_id   = absint( $result['user_id'] );
										$edit_link = add_query_arg(
											array(
												'wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), // phpcs:ignore
											),
											self_admin_url( sprintf( 'user-edit.php?user_id=%d', $user_id ) )
										);

										$created    = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $result['creation_time'] );
										$expiration = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( $result['expiry_time'] );

										// not using microtime here so strip the seconds.
										$created    = WSAL_Utilities_DateTimeFormatter::remove_milliseconds( $created );
										$expiration = WSAL_Utilities_DateTimeFormatter::remove_milliseconds( $expiration );

										$user               = get_user_by( 'id', $user_id );
										$is_current_session = WSAL_UserSessions_Helpers::hash_token( wp_get_session_token() ) === $result['session_token'];
										?>
										<tr id="<?php echo esc_html( $result['session_token'] ); ?>" <?php echo ( $is_current_session ) ? 'class="is-current-session"' : ''; ?>>
											<th class="check-column" scope="row">
												<label for="cb-select-1" class="screen-reader-text"><?php echo esc_html__( 'Select', 'wp-security-audit-log' ) . ' ' . esc_html( $user->data->user_login ); ?></label>
												<input type="checkbox" class="bulk-checkbox" value="<?php echo esc_attr( $user_id ); ?>" name="users[]" id="cb-select-1" data-bulk-destroy-nonce="<?php esc_attr_e( wp_create_nonce( sprintf( 'destroy_session_nonce-%d', $user_id ) ) ); ?>">
											</th>

											<td class="username column-username" data-colname="Username">
												<?php echo get_avatar( $user_id, 32 ); ?>
												<a class="wsal_session_user" href="<?php echo esc_url( $edit_link ); ?>"
														target="_blank"
														data-login="<?php echo esc_attr( $user->data->user_login ); ?>">
													<?php echo WSAL_Utilities_UsersUtils::get_display_label( $this->plugin, $user ); // phpcs:ignore ?>
												</a>
												<br>
												<?php echo WSAL_Utilities_UsersUtils::get_roles_label( $result['roles'] ); // phpcs:ignore ?>
												<br><br>
												<span><strong><?php esc_html_e( 'Session ID: ', 'wp-security-audit-log' ); ?></strong><span
															class="user_session_id"><?php echo esc_html( $result['session_token'] ); ?></span></span>
											</td>
											<td class="created column-created" data-colname="Created">
												<?php echo $created; // phpcs:ignore ?>
											</td>
											<td class="expiration column-expiration" data-colname="Expires">
												<?php echo $expiration; // phpcs:ignore ?>
											</td>
											<td class="ip column-ip" data-colname="Source IP">
												<?php $url = 'whatismyipaddress.com/ip/' . $result['ip'] . '?utm_source=plugin&utm_medium=referral&utm_campaign=WPSAL'; ?>
												<a target="_blank"
														href="<?php echo esc_url( $url ); ?>"><?php echo $result['ip']; // phpcs:ignore ?></a>
											</td>
											<td class="alert column-alert" data-colname="Last Alert">
												<?php
												// outputs the load button message for loading events in only the first row.
												// other rows get just an empty placeholder for JS targeting.
												if ( ! $load_event_on_click_button_output ) {
													$message = esc_html__( 'Click the Retrieve user data button to retrieve the users\' last event.', 'wp-security-audit-log' );
													// set the flag to show we output the message already.
													$load_event_on_click_button_output = true;
												} else {
													$message = '';
												}
												echo '<span class="fetch_placeholder">' . esc_html( $message ) . '</span>';
												?>
											</td>
											<td class="action column-action"
													data-colname="<?php esc_attr_e( 'Actions', 'wp-security-audit-log' ); ?>">
												<?php
												if ( $this->plugin->settings()->current_user_can( 'edit' ) ) {
													$user_data     = get_user_by( 'ID', $user_id );
													$user_wsal_url = add_query_arg(
														array(
															'page' => 'wsal-auditlog',
															'filters' => array( 'username:' . $user_data->user_login ),
														),
														network_admin_url( 'admin.php' )
													);
													echo '<a href="' . esc_url( $user_wsal_url ) . '" class="button-primary get-events">' . esc_html__( 'Show me this user\'s events', 'wp-security-audit-log' ) . '</a>';

													echo ( ! $is_current_session ) ? '<a href="#"' : '<button type="button"';
													?>
													data-action="destroy_session"
													data-user-id="<?php echo esc_attr( $user_id ); ?>"
													data-token="<?php echo esc_attr( $result['session_token'] ); ?>"
													data-wpnonce="<?php echo esc_attr( wp_create_nonce( sprintf( 'destroy_session_nonce-%d', $user_id ) ) ); ?>"
													class="button wsal_destroy_session"
													<?php
													if ( $is_current_session ) {
														echo 'disabled';
													}
													?>
													>
													<?php
													esc_html_e( 'Terminate Session', 'wp-security-audit-log' );
													echo ( ! $is_current_session ) ? '</a>' : '</button>';
												}
												?>
											</td>
										</tr>
									<?php endforeach; ?>
								</table>
							</td>
						</tr>
						<?php
					endforeach;
					?>
					</tbody>
				<?php } ?>
				<tfoot>
				<tr>
					<td class="manage-column column-cb check-column" scope="col">
						<label for="cb-select-all-2" class="screen-reader-text"><?php esc_html_e( 'Select All', 'wp-security-audit-log' ); ?></label>
						<input type="checkbox" id="cb-select-all-2" class="session-bulk-check">
					</td>
					<?php
					foreach ( $columns as $slug => $name ) {
						?>
						<th scope="col" class="manage-column column-<?php echo esc_attr( $slug ); ?>">
							<span><?php echo esc_html( $name ); ?></span>
						</th>
						<?php
					}
					?>
				</tr>
				</tfoot>
			</table>
			<div class="tablenav bottom">
				<br class="clear">
			</div>
		</form>
		<?php

	}

	/**
	 * Gets the view site ID.
	 *
	 * @return int View site ID.
	 */
	protected function get_view_site_id() {
		switch ( true ) {
			// Non-multisite.
			case ! $this->is_multisite():
				return 0;
				// Multisite + main site view.
			case $this->is_main_blog() && ! $this->is_specific_view():
				return 0;
				// Multisite + switched site view.
			case $this->is_main_blog() && $this->is_specific_view():
				return $this->get_specific_view();
				// Multisite + local site view.
			default:
				return get_current_blog_id();
		}
	}

	/**
	 * Checks if we're running multisite.
	 *
	 * @return bool True if we're running multisite.
	 */
	protected function is_multisite() {
		return WpSecurityAuditLog::is_multisite();
	}

	/**
	 * Checks if this is the main blog.
	 *
	 * @return bool True if this is the main blog.
	 */
	protected function is_main_blog() {
		return get_current_blog_id() === 1;
	}

	/**
	 * Checks if this is a specific view.
	 *
	 * @return bool True if this is a specific view.
	 */
	protected function is_specific_view() {
		return isset( $_REQUEST['wsal-cbid'] ) && $_REQUEST['wsal-cbid'] != '0'; // phpcs:ignore
	}

	/**
	 * Gets specific view.
	 *
	 * @return int View ID.
	 */
	protected function get_specific_view() {
		return isset( $_REQUEST['wsal-cbid'] ) ? (int) $_REQUEST['wsal-cbid'] : 0; // phpcs:ignore
	}

	/**
	 * Sets up session data to display.
	 *
	 * @param int $blog_id Blog ID.
	 */
	public function setup_session_data( $blog_id = 0 ) {
		// get all the sessions.
		$this->all_tracked_sessions = \WSAL\Adapter\User_Sessions::load_all_sessions_ordered_by_user_id( $blog_id );

		$all_sessions_cache_key = md5( wp_json_encode( $this->all_tracked_sessions ) );
		// short cache on this of 5 mins.
		WpSecurityAuditLog::set_transient( 'wsal_usersessions_cache_sum', $all_sessions_cache_key, 300 );

		$this->user_sessions = array();
		foreach ( $this->all_tracked_sessions as $tracked_session ) {
			$this->user_sessions[ $tracked_session['user_id'] ][] = $tracked_session;
		}
	}

	/**
	 * Method: Search Sessions.
	 *
	 * @return array - Array of search results.
	 *
	 * @throws Exception - Throw exception if sessions don't exist.
	 *
	 * @since 3.1.2
	 */
	protected function sessions_search() {
		$type                = \sanitize_text_field( \wp_unslash( $_GET['type'] ) );
		$keyword             = \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) );
		$sessions_terminated = ( isset( $_GET['sessions-terminated'] ) ) ? \sanitize_text_field( \wp_unslash( $_GET['sessions-terminated'] ) ) : null;

		// Verify user sessions exists.
		if ( ! is_array( $this->all_tracked_sessions ) && ! isset( $sessions_terminated ) || empty( $this->all_tracked_sessions ) && ! isset( $sessions_terminated ) ) {
			throw new Exception( __( 'User sessions do not exist.', 'wp-security-audit-log' ) );
		}

		// Search results.
		$results = array();

		// Get the type of search made.
		if ( isset( $type ) ) {
			switch ( $type ) {
				case 'username':
					// Search by username.
					if ( isset( $keyword ) ) {
						// Get user from WP.
						$user = get_user_by( 'login', $keyword );

						// If user exists then search in sessions.
						if ( $user && $user instanceof WP_User ) {
							// If user id match then add the sessions array to results array.
							if ( isset( $this->user_sessions[ $user->ID ] ) ) {
								$results[ $user->ID ] = $this->user_sessions[ $user->ID ];
							}
						}
					}

					break;

				case 'email':
					// Search by email.
					if ( isset( $keyword ) && is_email( $keyword ) ) {
						// Get user from WP.
						$user = get_user_by( 'email', $keyword );

						// If user exists then search in sessions.
						if ( $user && $user instanceof WP_User ) {
							// If user id match then add the sessions array to results array.
							if ( isset( $this->user_sessions[ $user->ID ] ) ) {
								$results[ $user->ID ] = $this->user_sessions[ $user->ID ];
							}
						}
					}

					break;

				case 'firstname':
					// Search by user first name.
					if ( isset( $keyword ) ) {
						// Ensure that incoming keyword is string.
						$name = (string) $keyword;

						// Get users.
						$users_array = get_users(
							array(
								'meta_key'     => 'first_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
								'meta_value'   => $name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								'fields'       => array( 'ID', 'user_login' ),
								'meta_compare' => 'LIKE',
							)
						);

						// Extract user id.
						$user_ids = array();
						foreach ( $users_array as $user ) {
							$user_ids[] = $user->ID;
						}

						// If user_ids array is not empty then.
						if ( ! empty( $user_ids ) ) {
							// Search sessions by user id.
							foreach ( $user_ids as $user_id ) {
								// If user id match then add the sessions array to results array.
								if ( isset( $this->user_sessions[ $user_id ] ) ) {
									$results[ $user_id ] = $this->user_sessions[ $user_id ];
								}
							}
						}
					}

					break;

				case 'lastname':
					// Search by user last name.
					if ( isset( $keyword ) ) {
						// Ensure that incoming keyword is string.
						$name = (string) $keyword;

						// Get users.
						$users_array = get_users(
							array(
								'meta_key'     => 'last_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
								'meta_value'   => $name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								'fields'       => array( 'ID', 'user_login' ),
								'meta_compare' => 'LIKE',
							)
						);

						// Extract user id.
						$user_ids = array();
						foreach ( $users_array as $user ) {
							$user_ids[] = $user->ID;
						}

						// If user_ids array is not empty then.
						if ( ! empty( $user_ids ) ) {
							// Search sessions by user id.
							foreach ( $user_ids as $user_id ) {
								// If user id match then add the sessions array to results array.
								if ( isset( $this->user_sessions[ $user_id ] ) ) {
									$results[ $user_id ] = $this->user_sessions[ $user_id ];
								}
							}
						}
					}

					break;

				case 'ip':
					// Search by ip.
					if ( isset( $keyword ) ) {
						// Search sessions by ip.
						foreach ( $this->user_sessions as $user_id => $sessions ) {
							// Search for matching IPs in $sessions.
							foreach ( $sessions as $session ) {
								if ( $keyword === $session['ip'] ) {
									$results[ $user_id ][] = $session;
								}
							}
						}
					}

					break;

				case 'user-role':
					// Search by user-role.
					if ( isset( $keyword ) ) {
						// Search sessions by user role.
						foreach ( $this->user_sessions as $user_id => $sessions ) {
							// Search for matching user role in $sessions.
							foreach ( $sessions as $session ) {
								$session['roles'] = json_decode( $session['roles'], true );
								$session['roles'] = array_map( 'strtolower', $session['roles'] );

								if ( is_array( $session['roles'] ) && in_array( strtolower( $keyword ), $session['roles'], true ) ) {
									$results[ $user_id ][] = $session;
								}
							}
						}
					}

					break;

				default:
					// Default.
					break;
			}
		}

		// Return results.
		return $results;
	}

	/**
	 * Calculates a number of sessions for each role as well as total number of sessions.
	 *
	 * @param array $sessions Array of current blog sessions.
	 *
	 * @return array
	 */
	public function get_per_role_sessions_count( $sessions = array() ) {
		$result = array();

		// If user sessions array is empty then use from property.
		if ( empty( $sessions ) ) {
			$sessions = $this->all_tracked_sessions;
		}

		// If user sessions array is still empty then return 0.
		if ( empty( $sessions ) ) {
			return $result;
		}

		// Wrap non-nested array of session to process data in a uniform way.
		if ( is_array( $sessions ) && is_object( reset( $sessions ) ) ) {
			$sessions = array( $sessions );
		}

		// A sorted array will be an array of arrays to count.
		if ( is_array( $sessions ) && is_array( reset( $sessions ) ) ) {
			foreach ( $sessions as $id => $session_arr ) {
				// Check for admin roles in the user sessions array.
				foreach ( $session_arr as $session ) {
					if ( is_array( $session ) && array_key_exists( 'roles', $session ) ) {
						$user_roles = json_decode( $session['roles'] );
						$role       = reset( $user_roles );
						if ( ! array_key_exists( $role, $result ) ) {
							$result[ $role ] = 0;
						}
						if ( ! array_key_exists( 'total', $result ) ) {
							$result['total'] = 0;
						}
						++$result[ $role ];
						++$result['total'];
					}
				}
			}
		}

		// Return count of admin sessions.
		return $result;
	}

	/**
	 * The number of sites on the network.
	 *
	 * @return int
	 */
	public function get_site_count() {
		global $wpdb;
		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->blogs;

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore
	}

	/**
	 * Query sites from WP DB.
	 *
	 * @param int|null $limit — Maximum number of sites to return (null = no limit).
	 *
	 * @return object — Object with keys: blog_id, blogname, domain
	 */
	public function get_sites( $limit = null ) {
		global $wpdb;

		$sql = 'SELECT blog_id, domain FROM ' . $wpdb->blogs;
		if ( ! is_null( $limit ) ) {
			$sql .= ' LIMIT ' . $limit;
		}
		$res = $wpdb->get_results( $sql ); // phpcs:ignore
		foreach ( $res as $row ) {
			$row->blogname = get_blog_option( $row->blog_id, 'blogname' );
		}

		return $res;
	}

	/**
	 * Renders a button to fetch session data.
	 *
	 * This is conditional and only shows on pages where a site is likely to
	 * have a large number of users that could cause timeouts.
	 *
	 * @method render_fetch_data_button
	 *
	 * @since  4.1.0
	 */
	public function render_fetch_data_button() {
		$nonce = wp_create_nonce( 'fetch_user_session_event_data' );
		?>
		<button class="button-primary wsal_fetch_users_event_data" type="button"
				data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php esc_html_e( 'Retrieve user data', 'wp-security-audit-log' ); ?></button>
		<span class="fetch-progress-spinner spinner"></span>
		<?php
	}

	public function render_multiple_sessions_only_checkbox() {
		$nonce   = wp_create_nonce( 'show_multiple_sessions' );
		$enabled = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'wsal_sessions_show_multiple_sessions_only' );
		?>
        	
		<div style="margin-left: 15px; display: inline-block;">
			<input data-nonce="<?php echo esc_attr( $nonce ); ?>" type="checkbox" id="wsal_show_multiple_sessions_only" name="wsal_show_multiple_sessions_only" value="wsal_show_multiple_sessions_only" <?php checked( $enabled, true, true ); ?> >
			<label for="wsal_show_multiple_sessions_only" class="show_multiple_sessions_label"> <?php esc_html_e( 'Only show sessions of users with multiple logged in sessions.', 'wp-security-audit-log' ); ?></label>
		</div>
		<?php
	}

	/**
	 * Renders a button terminate found sessions.
	 *
	 * @method render_terminate_search_result_sessions_button
	 *
	 * @param array $search_results_user_data - possible data found during a search (optional).
	 *
	 * @since 4.4.0
	 */
	public function render_terminate_search_result_sessions_button( $search_results_user_data = array(), $items_to_terminate = 'all' ) {
		if ( isset( $_REQUEST['type'] ) && isset( $_REQUEST['keyword'] ) && ! empty( $search_results_user_data ) ) { // phpcs:ignore
			$label = ( 'all' === $items_to_terminate ) ? esc_html__( 'Terminate all sessions that match this search criteria', 'wp-security-audit-log' ) : esc_html__( 'Terminate currently shown sessions', 'wp-security-audit-log' );
			?>
			<button class="button-primary terminate-session-for-query type="
					button" data-users-to-terminate='<?php echo json_encode( $search_results_user_data ); // phpcs:ignore ?>'><?php echo $label; ?></button>
			<span class="terminate-query-progress"></span>
			<?php
		}
	}

	/**
	 * Groups a list of sessions by the user ID.
	 *
	 * @method get_sessions_grouped_by_user_id
	 *
	 * @param array $sessions array of sessions to filter by.
	 *
	 * @return array
	 *
	 * @since  4.1.0
	 */
	public function get_sessions_grouped_by_user_id( $sessions ) {
		$results  = array();
		$sessions = ( ! empty( $sessions ) ) ? $sessions : $this->all_tracked_sessions;
		if ( ! empty( $sessions ) ) {
			foreach ( $sessions as $key => $data ) {
				$results[ $data->user_id ][] = $data;
			}
		}

		return $results;
	}

	/**
	 * Method: Search sessions form submit redirect.
	 */
	public function sessions_search_form() {
		$type                       = \sanitize_text_field( \wp_unslash( $_GET['type'] ) );
		$keyword                    = \sanitize_text_field( \wp_unslash( $_GET['keyword'] ) );
		$wsal_session_search__nonce = \sanitize_text_field( \wp_unslash( $_GET['wsal_session_search__nonce'] ) );

		// Get redirect URL.
		$redirect = filter_input( INPUT_GET, '_wp_http_referer' );

		// Verify nonce.
		if ( isset( $wsal_session_search__nonce )
			&& wp_verify_nonce( $wsal_session_search__nonce, 'wsal_session_search__nonce' ) ) {
			$redirect = add_query_arg(
				array(
					'type'    => $type,
					'keyword' => $keyword,
				),
				$redirect
			);
		}

		wp_safe_redirect( $redirect );
		die();
	}
}
