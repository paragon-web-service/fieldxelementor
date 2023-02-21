<?php
/**
 * Class WSAL_UserSessions_View_Policies.
 *
 * @package wsal
 */

use WSAL\Helpers\Options;
use WSAL\Helpers\WP_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session policies view for the user sessions extension.
 *
 * @package wsal
 */
class WSAL_UserSessions_View_Policies {

	/**
	 * View slug.
	 *
	 * @var string
	 */
	public static $slug = 'policies';

	/**
	 * Constructor.
	 *
	 * @param mixed $parent_page Parent page.
	 */
	public function __construct( $parent_page ) {
		$this->parent = $parent_page;
		$this->register_usersessions_subtab();
		// Set allowed HTML tags for blocked sessions error message.
		$this->allowed_error_tags = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		);

	}

	/**
	 * Method: Get View Title.
	 */
	public function get_title() {
		return esc_html__( 'All', 'wp-security-audit-log' );
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
		return esc_html__( 'Main Policies', 'wp-security-audit-log' );
	}

	/**
	 * Registers a subtab.
	 */
	public function register_usersessions_subtab() {
		add_filter(
			'wsal_usersessions_views_options_subnav',
			function( $tabs ) {
				$tabs[ self::$slug ] = array(
					'title' => $this->get_title(),
				);

				$roles = WP_Helper::get_roles();

				foreach ( $roles as $name => $role ) {
					if ( isset( $role ) ) {
						$tabs[ $role . '-policies' ] = array(
							'title' => $name,
						);
					}
				}

				return $tabs;
			},
			10,
			1
		);
	}

	/**
	 * Renders the content.
	 */
	public function render() {
		$this->render_subpage_content( $this->parent->requested_subtab );
	}

	/**
	 * Renders the subpage content.
	 *
	 * @param mixed $subpage Subpage.
	 */
	public function render_subpage_content( $subpage ) {
		$callback = array( $this, 'render_' . $subpage );
		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		} else {
			$this->render_role_form();
		}
	}

	/**
	 * Gets the form of options - capable of doing it for master or a role.
	 *
	 * @method render_role_form
	 * @since  4.1.0
	 */
	public function render_role_form() {
		if ( isset( $_POST[ 'wsal_usersessions_updated_' . self::$slug . '_' . $this->parent->requested_subtab ] ) && 'true' == $_POST[ 'wsal_usersessions_updated_' . self::$slug . '_' . $this->parent->requested_subtab ] ) { // phpcs:ignore
			$saved = $this->maybe_save_form();
			// if the form was saved show a notification to tell users.
			// NOTE: since this uses WP default notification classes WP hoists
			// this to the top of the page regardless of where it renders out.
			if ( $saved ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings have been saved', 'wp-securitu-audit-log' ); ?></p>
				</div>
				<?php
			}
		}
		$form_data                    = $this->get_form_data();
		$session_management_available = WSAL_UserSessions_Plugin::is_session_management_available()
		?>
		<form method="POST">
			<input type="hidden" name="wsal_usersessions_updated_<?php echo esc_attr( self::$slug . '_' . $this->parent->requested_subtab ); ?>" value="true" />
			<?php wp_nonce_field( 'wsal_usersessions_' . self::$slug . '_' . $this->parent->requested_subtab, '_wpnonce' ); ?>
			<?php if ( ! $session_management_available ) : ?>
				<div class="updated wsal_notice">
					<div class="wsal_notice__wrapper">
						<div class="wsal_notice__content">
							<p>
								<?php esc_html_e( 'Users session management is available in the Business and Enterprise plans.', 'wp-security-audit-log' ); ?>
							</p>
						</div>
						<!-- /.wsal_notice__content -->
						<div class="wsal_notice__btns">
							<a href="https://wpactivitylog.com/pricing/?utm_source=plugin&utm_medium=referral&utm_campaign=WSAL"
								rel="nofollow" target="_blank"
								class="button button-primary wsal_notice__btn"><?php esc_html_e( 'Upgrade the plan', 'wp-security-audit-log' ); ?></a>
						</div>
						<!-- /.wsal_notice__btns -->
					</div>
					<!-- /.wsal_notice__wrapper -->
				</div>
			<?php endif; ?>
			<?php
			// determine if we are on the main master policy or a role specific one.
			if ( self::$slug === $this->parent->requested_subtab ) {
				// master policy.
				?>
				<h3><?php esc_html_e( 'Users sessions policies', 'wp-security-audit-log' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Use the settings below to configure the policies to manage the users\' sessions policies. These policies are automatically inherited by all profiles. However, you can disable the inheritance or configure different policies for specific roles in the role\'s tab.', 'wp-security-audit-log' ); ?>
				</p>
				
				<table class="form-table">
					<tbody>
						<tr>
							<td colspan="2"><p><b><?php esc_html_e( 'Imoportant:', 'wp-security-audit-log' ); ?></b> <?php esc_html_e( 'Before enabling session restrictions consider terminating all current sessions to avoid lock-out due to hung-up sessions.', 'wp-security-audit-log' ); ?></p></td>
						</tr>
						<tr>
							<th><label for="policies_enabled"><?php esc_html_e( 'Enable session policies', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset <?php disabled( ! $session_management_available ); ?>">
									<input
										type="checkbox"
										name="policies_enabled"
										value="1"
										id="policies_enabled"
										<?php ( isset( $form_data['policies_enabled'] ) ) ? checked( $form_data['policies_enabled'] ) : null; ?>
									/>
									<input type="hidden" name="wsal_usersessions_policy_type" value="master" />
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
			} else {
				// sub policy.
				?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="wsal_usersessions_exclude_role"><?php esc_html_e( 'Do not enforce policies on users with this role', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset <?php disabled( ! $session_management_available ); ?>" style="display:inline; float:left">
									<input
										type="checkbox"
										name="exclude_role"
										value="1"
										id="exclude_role"
										<?php ( isset( $form_data['policies_disabled'] ) ) ? checked( $form_data['policies_disabled'] ) : null; ?>
									/>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="policies_inherited"><?php esc_html_e( 'Inherit the sessions policies', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset <?php disabled( ! $session_management_available ); ?>" style="display:inline; float:left">
									<input
										type="checkbox"
										name="policies_inherited"
										value="1"
										id="policies_inherited"
										<?php ( isset( $form_data['policies_inherited'] ) ) ? checked( $form_data['policies_inherited'] ) : null; ?>
									/>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
			}
			?>
			<div id="sessions-policy-settings">
				<p class="description">
					<?php esc_html_e( 'By default WordPress does not limit how many times the same user can connect simultaneously. So two different users can login at the same time using the same username. Use the settings below to limit and also block simultaneous connections for the same username.', 'wp-security-audit-log' ); ?>
				</p>
				<h3><?php esc_html_e( 'Do you want to allow two or more people to login simultaneously with the same username?', 'wp-security-audit-log' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'By allowing multiple sessions two or more people can login to WordPress using the same username. By blocking them, once a person is logged in with a username, if another person tries to login with the same username they will be blocked.', 'wp-security-audit-log' ); ?>
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="allow"><?php esc_html_e( 'Multiple Sessions', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<?php
									// Allow multiple sessions option.
									$is_allow = ( isset( $form_data['multisessions']['type'] ) ) ? $form_data['multisessions']['type'] : 'single';
									?>
									<label for="single">
										<input type="radio" name="multisessions" id="single" <?php checked( $is_allow, 'single' ); ?> value="single">
										<span><?php esc_html_e( 'Allow one session only', 'wp-security-audit-log' ); ?></span>
									</label>

									<br/>
									<label for="newest">
										<input type="radio" name="multisessions" id="newest" <?php checked( $is_allow, 'newest' ); ?> value="newest">
										<span><?php esc_html_e( 'Allow one session only and override current session', 'wp-security-audit-log' ); ?></span>
									</label>
									<br/>
									<label for="allow-limited">
										<input type="radio" name="multisessions" id="allow-limited" <?php checked( $is_allow, 'allow-limited' ); ?> value="allow-limited">
										<span>
											<?php
											$allowed_sessions = ( isset( $form_data['multisessions']['limit'] ) ) ? (int) $form_data['multisessions']['limit'] : 3;
											$allow_limited    = '<input type="number" min="1" name="multisessions_limit" id="multi-sessions-limit" value="' . esc_attr( $allowed_sessions ) . '" />';
											/* Translators: Number of sessions input tag */
											printf( esc_html__( 'Allow up to %s sessions and block the rest', 'wp-security-audit-log' ), $allow_limited ); // phpcs:ignore
											?>
										</span>
									</label>
								</fieldset>
							</td>
						</tr>
						<!-- / Multiple Sessions -->
					</tbody>
				</table>

				<h3><?php esc_html_e( 'Configure a Blocked Session Notification for Users', 'wp-security-audit-log' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'This error message is shown to users when they try to login with a username that already has a session and their session is blocked. You can change this message by editing the text in the below placeholder. Only <a href> HTML code is allowed.', 'wp-security-audit-log' ); ?>
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="sessions-error-message"><?php esc_html_e( 'Blocked Sessions Error', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<?php
									$default_message = __( 'ERROR: Your session was blocked with the <a href="https://en-gb.wordpress.org/plugins/wp-security-audit-log" target="_blank">WP Activity Log plugin</a> because there is already another user logged in with the same username. Please contact the site administrator for more information.', 'wp-security-audit-log' );
									// use from saved settings - if no setting is saved use default.
									$error_message = ( isset( $form_data['sessions_error_message'] ) && ! empty( $form_data['sessions_error_message'] ) ) ? $form_data['sessions_error_message'] : $default_message;
									?>
									<label for="sessions-error-message">
										<textarea rows="5" cols="50" name="sessions_error_message" id="sessions-error-message"><?php echo wp_kses( $error_message, $this->allowed_error_tags ); ?></textarea>
									</label>
								</fieldset>
							</td>
						</tr>
						<!-- / Events Dashboard Widget -->
					</tbody>
				</table>

				<h3><?php esc_html_e( 'Do you want to terminate idle sessions automatically?', 'wp-security-audit-log' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'If a session has been idle for more than the configured number of hours, it will be automatically destroyed by the plugin.', 'wp-security-audit-log' ); ?>
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="auto_terminate"><?php esc_html_e( 'Terminate Idle Sessions', 'wp-security-audit-log' ); ?></label></th>
							<td>
								<fieldset>
									<label for="auto_terminate">
										<input
											type="checkbox"
											name="auto_terminate"
											value="1"
											id="auto_terminate"
											<?php ( isset( $form_data['auto_terminate']['enabled'] ) ) ? checked( $form_data['auto_terminate']['enabled'] ) : null; ?>
										/>
										<?php
										// Get stored number of hours.
										$auto_terminate_hours = (int) ( isset( $form_data['auto_terminate']['max_hours'] ) ) ? $form_data['auto_terminate']['max_hours'] : 1;

										// Are we testing sessions currently? Lets check for the option.
										$sessions_test = apply_filters( 'wsal_inactive_sessions_test', false );
										?>
										<span>
											<?php esc_html_e( 'terminate sessions if they have been idle for more than', 'wp-security-audit-log' ); ?>
											<?php if ( isset( $sessions_test ) && ! empty( $sessions_test ) ) { ?>
												<input name="auto_terminate_hours" disabled type="number" value="<?php echo esc_attr( $sessions_test ); ?>" id="auto_terminate_hours" style="max-width: 60px;" >
												<?php esc_html_e( ' seconds (Testing)', 'wp-security-audit-log' ); ?>
											<?php } else { ?>
												<input name="auto_terminate_hours" type="number" min="1" max="48" value="<?php echo esc_attr( $auto_terminate_hours ); ?>" id="auto_terminate_hours" style="max-width: 60px;" >
												<?php esc_html_e( ' hours', 'wp-security-audit-log' ); ?>
											<?php } ?>
										</span>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" <?php disabled( ! $session_management_available ); ?> value="<?php esc_html_e( 'Save', 'wp-security-audit-log' ); ?>">
			</p>
		</form>
		<?php
	}

	/**
	 * Gets the form data for this tab as an array for rendering the page with.
	 *
	 * @method get_form_data
	 * @since  4.1.0
	 * @return array
	 */
	public function get_form_data() {
		$policy_suffix = rtrim( str_replace( self::$slug, '', $this->parent->requested_subtab ), '-' );
		if ( strlen( $policy_suffix ) > 0 ) {
			$policy_suffix = '_' . $policy_suffix;
		}
		$data = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policy' . $policy_suffix, array() );
		if ( empty( $data ) ) {
			$data = array(
				'policies_inherited' => true,
			);
		}

		// polices_enabled is a separate option - it's the master on/off toggle.
		$enabled = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policies_enabled', false );
		return array_merge( $data, array( 'policies_enabled' => $enabled ) );
	}

	/**
	 * Parse and save the form data if the nonce verifies.
	 *
	 * @method maybe_save_form
	 * @since  4.1.0
	 */
	public function maybe_save_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wsal_usersessions_' . self::$slug . '_' . $this->parent->requested_subtab ) ) {
			return;
		}

		if ( ! WSAL_UserSessions_Plugin::is_session_management_available() ) {
			return;
		}

		$multiple_sessions = array(
			'type'  => ( isset( $_POST['multisessions'] ) ) ? \sanitize_text_field( \wp_unslash( $_POST['multisessions'] ) ) : 'allow-limited',
			'limit' => (int) ( isset( $_POST['multisessions_limit'] ) ) ? \sanitize_text_field( \wp_unslash( $_POST['multisessions_limit'] ) ) : 3,
		);

		$max_hours = (int) ( isset( $_POST['auto_terminate_hours'] ) ) ? filter_input( INPUT_POST, 'auto_terminate_hours', FILTER_SANITIZE_NUMBER_INT ) : 0;

		if ( $max_hours < 1 ) {
			$max_hours = 1;
		}
		if ( $max_hours > 48 ) {
			$max_hours = 48;
		}

		$auto_terminate = array(
			'enabled'   => ( isset( $_POST['auto_terminate'] ) ) ? filter_input( INPUT_POST, 'auto_terminate', FILTER_VALIDATE_BOOLEAN ) : false,
			'max_hours' => $max_hours,
		);

		$policy = array(
			'multisessions'          => $multiple_sessions,
			'sessions_error_message' => ( isset( $_POST['sessions_error_message'] ) ) ? wp_kses_post( $_POST['sessions_error_message'] ) : 'A default blocked message here.',
			'auto_terminate'         => $auto_terminate,
		);

		// check if this role is explicitly disabled via policy.
		if ( isset( $_POST['exclude_role'] ) ) {
			// when policy is disabled use the previously saved settings and
			// return early without farther processing.
			$policy['policies_disabled'] = (bool) $_POST['exclude_role'];

			$old_policy = $this->get_form_data();

			if ( isset( $old_policy['auto_terminate'] ) && isset( $old_policy['sessions_error_message'] ) && isset( $old_policy['multisessions'] ) ) {
				$policy['multisessions']          = $old_policy['multisessions'];
				$policy['sessions_error_message'] = $old_policy['sessions_error_message'];
				$policy['auto_terminate']         = $old_policy['auto_terminate'];
			}

			$this->save_policy( $policy );
			return true;

		} else {
			$policy['policies_disabled'] = false;
		}

		// check if master switch is available and add if so.
		if ( isset( $_POST['wsal_usersessions_policy_type'] ) && 'master' === $_POST['wsal_usersessions_policy_type'] ) {
			if ( isset( $_POST['policies_enabled'] ) ) {
				\WSAL\Helpers\Settings_Helper::set_option_value( 'wsal_usersessions_policies_enabled', true );
			} else {
				\WSAL\Helpers\Settings_Helper::set_option_value( 'wsal_usersessions_policies_enabled', false );
				$policy['auto_terminate']['enabled'] = true;
			}
		} else {
			// this is not master polcy - adding inherit setting.
			$policy['policies_inherited'] = ( isset( $_POST['policies_inherited'] ) ) ? filter_input( INPUT_POST, 'policies_inherited', FILTER_VALIDATE_BOOLEAN ) : false;
			if ( $policy['policies_inherited'] ) {
				// getting master policy settings so they can be ported here.
				$master_policy = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_usersessions_policy' );
				if ( isset( $master_policy['auto_terminate'] ) && isset( $master_policy['sessions_error_message'] ) && isset( $master_policy['multisessions'] ) ) {
					$policy['multisessions']          = $master_policy['multisessions'];
					$policy['sessions_error_message'] = $master_policy['sessions_error_message'];
					$policy['auto_terminate']         = $master_policy['auto_terminate'];
				}
			}
		}

		$this->save_policy( $policy );
		// return true because we saved.
		return true;
	}

	/**
	 * Saves the policy.
	 *
	 * @param mixed $policy Policy.
	 */
	private function save_policy( $policy ) {
		// generate a suffix for a name to save the policy under.
		// NOTE: this should be empty if it's the base policy or "_{$role}" for role pages.
		$policy_suffix = rtrim( str_replace( self::$slug, '', $this->parent->requested_subtab ), '-' );
		if ( strlen( $policy_suffix ) > 0 ) {
			$policy_suffix = '_' . $policy_suffix;
		}

		\WSAL\Helpers\Settings_Helper::set_option_value( 'wsal_usersessions_policy' . $policy_suffix, $policy );
	}
}
