<?php
/**
 * Class WSAL_UserSessions_View_Options.
 *
 * @package wsal
 */

use WSAL\Helpers\WP_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options view for the user sessions extension.
 *
 * @package wsal
 */
class WSAL_UserSessions_View_Options {

	/**
	 * The tabs slug.
	 *
	 * @var string
	 */
	public static $slug = 'options';

	/**
	 * The default subtab.
	 *
	 * @var string
	 */
	public $requested_subtab = 'policies';

	/**
	 * Array of subpages this page bundles.
	 *
	 * @var array
	 */
	private $subpages = array();

	/**
	 * Method: Get View Title.
	 */
	public function get_title() {
		return esc_html__( 'Users Sessions Management', 'wp-security-audit-log' );
	}

	/**
	 * Method: Get View Icon.
	 */
	public function get_icon() {
		return 'dashicons-admin-generic';
	}

	/**
	 * Method: Get View Name.
	 */
	public function get_name() {
		return esc_html__( 'User Session Options', 'wp-security-audit-log' );
	}

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $plugin Plugin.
	 */
	public function __construct( WpSecurityAuditLog $plugin ) {
		$this->wsal = $plugin;
		$this->register_usersessions_tab();
		$this->setup_subpages();
		$this->requested_subtab = ( isset( $_GET['subtab'] ) && in_array( $_GET['subtab'], $this->allowed_subtab_keys(), true ) ) ? sanitize_text_field( wp_unslash( $_GET['subtab'] ) ) : 'policies'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verifying against known list.
	}

	/**
	 * Options tab outputs a subpage to set roles policies.
	 *
	 * @method setup_subpages
	 * @since  4.1.0
	 */
	private function setup_subpages() {
		$this->subpages[ WSAL_UserSessions_View_Policies::$slug ] = new WSAL_UserSessions_View_Policies( $this );
	}

	/**
	 * Helper to generate whitelist of valid request keys from user roles.
	 *
	 * @method allowed_subtab_keys
	 * @since  4.1.0
	 * @return array
	 */
	public function allowed_subtab_keys() {
		$allowed_keys = array( 'policies' );

		$roles = WP_Helper::get_roles();

		foreach ( $roles as $name => $role ) {
			if ( isset( $role ) ) {
				$allowed_keys[] = $role . '-policies';
			}
		}

		return $allowed_keys;
	}

	/**
	 * Registers this tab to the main page and setups the allowed tabs array.
	 *
	 * @method register_usersessions_tab
	 * @since  4.1.0
	 */
	public function register_usersessions_tab() {
		add_filter(
			'wsal_usersessions_views_nav_header_items',
			function( $tabs ) {
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
			function( $allowed ) {
				$allowed[] = self::$slug;
				return $allowed;
			},
			10,
			1
		);
	}

	/**
	 * Render this page or tab html contents.
	 *
	 * @method render
	 * @since  4.1.0
	 */
	public function render() {
		$this->render_subnav( $this->requested_subtab );
		$this->render_subpage_content();
	}

	/**
	 * Renders the subpage content for policies form.
	 *
	 * @method render_subpage_content
	 * @since  4.1.0
	 */
	private function render_subpage_content() {
		if ( method_exists( $this->subpages['policies'], 'render' ) ) {
			$this->subpages['policies']->render( $this->requested_subtab );
		}
	}

	/**
	 * Handles rendering the subnav that allows setting options per role.
	 *
	 * @method render_subnav
	 * @since  4.1.0
	 * @param  string $active_subtab currently active tab.
	 */
	private function render_subnav( $active_subtab ) {
		$subnav_items = $this->get_subnav_items();
		if ( ! is_array( $subnav_items ) ) {
			return;
		}
		foreach ( $subnav_items as $key => $nav_item ) {
			if ( ! is_array( $nav_item ) || ! isset( $nav_item['title'] ) ) {
				unset( $subnav_items[ $key ] );
				continue;
			}
		}
		if ( count( $subnav_items ) <= 0 ) {
			return;
		}
		?>
		<h2 id="wsal-usersessions-mainnav" class="nav-tab-wrapper">
			<?php
			foreach ( $subnav_items as $key => $nav_item ) {
				// if $active_tab matches the $slug this is the active tab.
				$is_active = $this->is_active_navtab( $key, $active_subtab ) ? 'nav-tab-active' : '';
				?>
				<a href="<?php echo esc_url( add_query_arg( 'subtab', $key, add_query_arg( 'tab', 'options' ) ) ); ?>" id="nav-tab-<?php echo esc_attr( $key ); ?>" class="nav-tab <?php echo esc_attr( $is_active ); ?>"><?php echo esc_html( $nav_item['title'] ); ?></a>
				<?php
			}
			?>
		</h2>
		<?php
	}

	/**
	 * Getter for fetching array of all the subnav items.
	 *
	 * This is filled via a filter.
	 *
	 * @method get_subnav_items
	 * @since  4.1.0
	 * @return array
	 */
	private function get_subnav_items() {
		return apply_filters(
			'wsal_usersessions_views_options_subnav',
			array()
		);
	}

	/**
	 * Determines if the current navtab item being output is the 'active' one
	 * based on the incoming request.
	 *
	 * @method is_active_navtab
	 * @since  4.1.0
	 * @param  string $slug      slug that we are outputting.
	 * @param  string $requested slug that was requested.
	 * @return boolean
	 */
	private function is_active_navtab( $slug = '', $requested = '' ) {
		$active = false;
		if ( $requested === $slug ) {
			$active = true;
		}
		return $active;
	}
}
