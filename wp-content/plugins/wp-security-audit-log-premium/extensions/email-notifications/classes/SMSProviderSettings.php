<?php
/**
 * SMS Provider Settings (Premium)
 *
 * Settings tab for SMS provider settings.
 *
 * @since 3.4
 * @package wsal
 * @subpackage email-notifications
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: WSAL_NP_SMSProviderSettings
 *
 * SMS provider settings tab view class to handle settings page functions.
 */
class WSAL_NP_SMSProviderSettings {

	/**
	 * Twilio Account SID.
	 *
	 * @var string
	 */
	private $account_sid = '';

	/**
	 * Twilio Auth Token.
	 *
	 * @var string
	 */
	private $auth_token = '';

	/**
	 * Twilio From Number.
	 *
	 * @var string
	 */
	private $from_number = '';

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		// Get WSAL instance.
		$wsal = WpSecurityAuditLog::get_instance();

		// Get SID and Token.
		$this->account_sid = \WSAL\Helpers\Settings_Helper::get_option_value( 'twilio-account-sid', false );
		$this->auth_token  = \WSAL\Helpers\Settings_Helper::get_option_value( 'twilio-auth-token', false );
		$this->from_number = \WSAL\Helpers\Settings_Helper::get_option_value( 'twilio-number', false );

		// Add SMS settings tab.
		add_filter( 'wsal_setting_tabs', array( $this, 'add_sms_settings_tab' ), 10, 1 );
	}

	/**
	 * Set Account ID Twilio Setting.
	 *
	 * @param string $account_sid - Account SID.
	 */
	private function set_account_sid( $account_sid ) {
		$this->account_sid = $account_sid;
		$this->set_twilio_option( 'twilio-account-sid', $account_sid );
	}

	/**
	 * Set Auth Token Twilio Setting.
	 *
	 * @param string $auth_token - Auth Token.
	 */
	private function set_auth_token( $auth_token ) {
		$this->auth_token = $auth_token;
		$this->set_twilio_option( 'twilio-auth-token', $auth_token );
	}

	/**
	 * Set Twilio Number Setting.
	 *
	 * @param string $from_number - From Twilio Number.
	 */
	private function set_from_number( $from_number ) {
		$this->from_number = $from_number;
		$this->set_twilio_option( 'twilio-number', $from_number );
	}

	/**
	 * Set Twilio Settings.
	 *
	 * @param string $option_name  - Setting name.
	 * @param string $option_value - Setting value.
	 */
	private function set_twilio_option( $option_name, $option_value ) {
		\WSAL\Helpers\Settings_Helper::set_option_value( $option_name, $option_value );
	}

	/**
	 * Check to see if SMS module is active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return wsal_freemius()->is_plan_or_trial__premium_only( 'starter' );
	}

	/**
	 * SMS Settings Tab.
	 *
	 * @param array $wsal_setting_tabs – Array of WSAL Setting Tabs.
	 * @return array
	 */
	public function add_sms_settings_tab( $wsal_setting_tabs ) {
		$wsal_setting_tabs['sms-provider'] = array(
			'name'     => esc_html__( 'SMS Provider', 'wp-security-audit-log' ),
			'link'     => add_query_arg( 'tab', 'sms-provider' ),
			'render'   => array( $this, 'sms_settings_tab' ),
			'save'     => array( $this, 'sms_settings_tab_save' ),
			'priority' => 50,
		);
		return $wsal_setting_tabs;
	}

	/**
	 * SMS Provider Settings Tab.
	 */
	public function sms_settings_tab() {
		if ( ! WSAL_Extension_Manager::is_messaging_available() ) {
			WSAL_Extension_Manager::render_helper_plugin_notice( esc_html__( 'To configure the SMS provider and be able to send SMS notifications you need to install an extension. Please click the button below to automatically install and activate the plugin extension so you can configure the SMS provider and send SMS notifications.', 'wp-security-audit-log' ) );
			echo '<style type="text/css">p.submit input[type=submit] { display: none !important; }</style>';

			return;
		}

		// @codingStandardsIgnoreStart
		$current = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'settings';
		// @codingStandardsIgnoreEnd

		$sub_tab_args = array(
			'page' => 'wsal-settings',
			'tab'  => 'sms-provider',
		);
		$settings_tab = add_query_arg( array_merge( $sub_tab_args, array( 'section' => 'settings' ) ), network_admin_url( 'admin.php' ) );
		$test_tab     = add_query_arg( array_merge( $sub_tab_args, array( 'section' => 'test' ) ), network_admin_url( 'admin.php' ) );
		?>
		<ul class="nav-sub-tabs">
			<li><a href="<?php echo esc_url( $settings_tab ); ?>" class="<?php echo 'settings' === $current ? 'current' : false; ?>"><?php esc_html_e( 'Settings', 'wp-security-audit-log' ); ?></a> | </li>
			<li>
				<?php if ( $this->is_active() && $this->account_sid && $this->auth_token ) : ?>
					<a href="<?php echo esc_url( $test_tab ); ?>" class="<?php echo 'test' === $current ? 'current' : false; ?>"><?php esc_html_e( 'Test', 'wp-security-audit-log' ); ?></a>
				<?php else : ?>
					<span><?php esc_html_e( 'Test', 'wp-security-audit-log' ); ?></span>
				<?php endif; ?>
			</li>
		</ul>
		<?php
		if ( 'settings' === $current ) {
			$this->settings_tab();
		} elseif ( 'test' === $current ) {
			$this->testing_tab();
		}
	}

	/**
	 * SMS Provider Settings Render.
	 */
	private function settings_tab() {
		$disabled  = ! $this->is_active() ? 'disabled' : '';
		$admin_url = ! is_multisite() ? 'admin_url' : 'network_admin_url';
		$buy_now   = add_query_arg( 'page', 'wsal-auditlog-pricing', $admin_url( 'admin.php' ) );
		$html_tags = WpSecurityAuditLog::get_instance()->allowed_html_tags;

		$tab_info_msg = esc_html__( 'Configure your Twilio account details to be able to configure and send SMS notifications.', 'wp-security-audit-log' );
		if ( $disabled ) {
			/* Translators: Upgrade now hyperlink. */
			$tab_info_msg = sprintf( esc_html__( 'SMS notifications are available in the Professional and Business Plans. %s to configure and receive SMS notifications.', 'wp-security-audit-log' ), '<a href="' . $buy_now . '">' . esc_html__( 'Upgrade now', 'wp-security-audit-log' ) . '</a>' );
		}
		?>
		<p class="description"> <?php echo wp_kses( $tab_info_msg, $html_tags ); ?></p>
		<table class="form-table wsal-tab wsal-twilio-settings-tab">
			<tr>
				<th><label for="account-sid"><?php esc_html_e( 'Account SID', 'wp-security-audit-log' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="text" name="account-sid" id="account-sid" placeholder="<?php esc_attr_e( 'Enter Account SID', 'wp-security-audit-log' ); ?>" value="<?php echo esc_attr( $this->account_sid ); ?>">
						<p class="description">
							<?php
							/* Translators: Twilio console link */
							echo sprintf( esc_html__( 'To view API credentials visit %s', 'wp-security-audit-log' ), '<a href="https://www.twilio.com/console" target="_blank">https://www.twilio.com/console</a>' );
							?>
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- Account SID -->
			<tr>
				<th><label for="auth-token"><?php esc_html_e( 'Auth token', 'wp-security-audit-log' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="text" name="auth-token" id="auth-token" placeholder="<?php esc_attr_e( 'Enter Auth Token', 'wp-security-audit-log' ); ?>" value="<?php echo esc_attr( $this->auth_token ); ?>">
						<p class="description">
							<?php
							/* Translators: Twilio console link */
							echo sprintf( esc_html__( 'To view API credentials visit %s', 'wp-security-audit-log' ), '<a href="https://www.twilio.com/console" target="_blank">https://www.twilio.com/console</a>' );
							?>
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- Auth Token -->
			<tr>
				<th><label for="twilio-number"><?php esc_html_e( 'Twilio number / Alphanumeric ID', 'wp-security-audit-log' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="text" name="twilio-number" id="twilio-number" placeholder="<?php esc_attr_e( '+16175551212', 'wp-security-audit-log' ); ?>" value="<?php echo esc_attr( $this->from_number ); ?>">
						<p class="description"><?php esc_html_e( 'Specify a Twilio phone number including the country code (e.g. +16175551212) or a valid Alphanumeric ID (e.g. WSAL)', 'wp-security-audit-log' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Twilio Number -->
		</table>
		<?php
	}

	/**
	 * SMS Provider Test Render.
	 */
	private function testing_tab() {
		$disabled = $this->is_active() && $this->account_sid && $this->auth_token ? false : 'disabled';

		if ( $disabled ) :
			$twilio_settings = add_query_arg(
				array(
					'page' => 'wsal-settings',
					'tab'  => 'sms-provider',
				),
				admin_url( 'admin.php' )
			);

			/* Translators: Twilio settings hyperlink. */
			$phone_help = sprintf( esc_html__( 'Click %s to configure Twilio integration for SMS notifications.', 'wp-security-audit-log' ), '<a href="' . esc_url( $twilio_settings ) . '">' . esc_html__( 'here', 'wp-security-audit-log' ) . '</a>' );

			$allowed_tags = array( 'a' => array( 'href' => array() ) );
			?>
			<p class="description"><?php echo wp_kses( $phone_help, $allowed_tags ); ?></p>
			<?php
		endif;
		?>
		<table class="form-table wsal-tab">
			<tr>
				<th><label for="twilio-test-number"><?php esc_html_e( 'Recipient Number', 'wp-security-audit-log' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="text" name="twilio-test-number" id="twilio-test-number" placeholder="<?php esc_attr_e( '+16175551212', 'wp-security-audit-log' ); ?>">
						<p class="description"><?php esc_html_e( 'Country code + 10-digit Twilio phone number (i.e. +16175551212)', 'wp-security-audit-log' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Auth Token -->
			<tr>
				<th><label for="twilio-test-message"><?php esc_html_e( 'Message Body', 'wp-security-audit-log' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<textarea type="text" name="twilio-test-message" id="twilio-test-message" maxlength="1600" rows="7" cols="50"></textarea>
						<p class="description"><?php esc_html_e( 'The text of the message you want to send, limited to 1600 characters.', 'wp-security-audit-log' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Message Body -->
		</table>
		<?php
	}

	/**
	 * SMS Provider Settings Save.
	 */
	public function sms_settings_tab_save() {
		// @codingStandardsIgnoreStart
		$current = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'settings';
		// @codingStandardsIgnoreEnd

		if ( 'settings' === $current ) {
			// @codingStandardsIgnoreStart
			isset( $_POST['account-sid'] ) ? $this->set_account_sid( sanitize_text_field( wp_unslash( $_POST['account-sid'] ) ) ) : false;
			isset( $_POST['auth-token'] ) ? $this->set_auth_token( sanitize_text_field( wp_unslash( $_POST['auth-token'] ) ) ) : false;
			isset( $_POST['twilio-number'] ) ? $this->set_from_number( sanitize_text_field( wp_unslash( $_POST['twilio-number'] ) ) ) : false;
			// @codingStandardsIgnoreEnd
		} elseif ( 'test' === $current ) {
			// @codingStandardsIgnoreStart
			$twilio_test_number  = isset( $_POST['twilio-test-number'] ) ? sanitize_text_field( wp_unslash( $_POST['twilio-test-number'] ) ) : false;
			$twilio_test_message = isset( $_POST['twilio-test-message'] ) ? sanitize_text_field( wp_unslash( $_POST['twilio-test-message'] ) ) : false;
			// @codingStandardsIgnoreEnd
			$this->send_sms( $twilio_test_number, $twilio_test_message );
		}
	}

	/**
	 * Send SMS to a specific number.
	 *
	 * @param string $to_number - Phone number to which SMS should be sent.
	 * @param string $message   - SMS body.
	 * @return boolean
	 */
	public function send_sms( $to_number, $message ) {
		if ( $to_number && $message && $this->account_sid && $this->auth_token ) {
			$client  = new WSAL_Vendor\Twilio\Rest\Client( $this->account_sid, $this->auth_token );
			$message = $client->messages->create(
				$to_number, // To number.
				array(
					'from' => $this->from_number, // From number.
					'body' => $message, // Message.
				)
			);
			return $message->sid ? true : false;
		}
		return false;
	}
}
