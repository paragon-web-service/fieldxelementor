<?php
/**
 * Class WSAL_Rep_WhiteLabellingSettings.
 *
 * @since      latest
 * @package    wsal
 * @subpackage reports
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * White labelling settings for reports (Premium)
 *
 * White labelling settings tab view class to handle settings page functions for the reports' extension.
 *
 * @since      latest
 * @package    wsal
 * @subpackage reports
 */
class WSAL_Rep_WhiteLabellingSettings {

	/**
	 * View slug.
	 *
	 * @var string
	 */
	public static $slug = 'settings';

	/**
	 * Instance of the plugin class.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * Class constructor.
	 *
	 * @param WpSecurityAuditLog $plugin Main plugin class instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'maybe_save_form' ) );
		add_action( 'admin_notices', array( $this, 'show_notices_if_needed' ) );

		add_filter( 'wsal_reports_views_nav_header_items', array( $this, 'add_tab' ), 10, 1 );
		add_action( 'wsal_reports_render_tab_' . self::$slug, array( $this, 'render' ) );
	}

	/**
	 * Enqueue some scripts on the settings screen only.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( preg_match( '/.*wsal\-rep\-views\-main$/', $hook_suffix ) ) { // @codingStandardsIgnoreLine
			wp_enqueue_media();
		}
	}

	/**
	 * Adds a new tab to the reporting screen.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array Array of updated tabs.
	 */
	public function add_tab( $tabs ) {
		if ( $this->plugin->settings()->current_user_can( 'edit' ) ) {
			$tabs[ self::$slug ] = $this->get_title();
		}

		return $tabs;
	}

	/**
	 * Returns a title to use for this tab/page.
	 *
	 * @return string
	 * @since  latest
	 */
	public function get_title() {
		return esc_html__( 'White labelling', 'wp-security-audit-log' );
	}

	/**
	 * Renders the settings tab content.
	 *
	 * @since 4.4.2.1
	 */
	public function render() {
		$settings         = $this->plugin->settings();
		$business_name    = $settings->get_business_name();
		$contact_name     = $settings->get_contact_name();
		$contact_email    = $settings->get_contact_email();
		$contact_phone    = $settings->get_contact_phone_number();
		$custom_logo_url  = $settings->get_custom_reports_logo();
		$custom_logo_link = $settings->get_custom_reports_logo_link();
		?>
		<form method="POST">
			<?php wp_nonce_field( 'wsal_reports_' . self::$slug ); ?>
			<table class="form-table wsal-reports-settings">
				<tr>
					<th colspan="2">
						<h3><?php esc_html_e( 'White labelling', 'wp-security-audit-log' ); ?></h3>
					</th>
				</tr>
				<tr>
					<th>
						<label for="report-business-name"><?php esc_html_e( 'Business name', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<input type="text" name="ReportsBusinessName" id="report-business-name"
								value="<?php echo esc_attr( $business_name ); ?>" class="regular-text"
								placeholder="<?php esc_html_e( 'Enter the business name', 'wp-security-audit-log' ); ?>"
						/>
					</td>
				</tr>
				<tr>
					<th colspan="2">
						<h3><?php esc_html_e( 'Contact details', 'wp-security-audit-log' ); ?></h3>
					</th>
				</tr>
				<tr>
					<th>
						<label for="report-contact-name"><?php esc_html_e( 'Name and surname', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<input type="text" name="ReportsContactName" id="report-contact-name"
								value="<?php echo esc_attr( $contact_name ); ?>" class="regular-text"
								placeholder="<?php esc_html_e( 'Enter name and surname', 'wp-security-audit-log' ); ?>"
						/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="report-contact-email"><?php esc_html_e( 'Email', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<input type="text" name="ReportsContactEmail" id="report-contact-email"
								value="<?php echo esc_attr( $contact_email ); ?>" class="regular-text"
								placeholder="<?php esc_html_e( 'Enter email', 'wp-security-audit-log' ); ?>"
						/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="report-contact-phone"><?php esc_html_e( 'Phone number', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<input type="text" name="ReportsContactPhone" id="report-contact-phone"
								value="<?php echo esc_attr( $contact_phone ); ?>" class="regular-text"
								placeholder="<?php esc_html_e( 'Enter phone number', 'wp-security-audit-log' ); ?>"
						/>
					</td>
				</tr>
				<tr>
					<th colspan="2">
						<p class="description">
							<?php esc_html_e( 'By default the HTML and PDF reports have a logo of the WP Activity Log plugin in them. Use the settings below to change this logo and also specify a URL that this logo should link to.', 'wp-security-audit-log' ); ?>
						</p>
					</th>
				</tr>
				<tr>
					<th>
						<label for="reports-custom-logo"><?php esc_html_e( 'Use custom logo for the reports', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<fieldset>
							<div class="wsal_upload_field_container">
								<span id="wsal_download_files2file-wrap">
									<input type="text"
											name="CustomReportsLogo" id="reports-custom-logo" autocomplete=""
											value="<?php echo esc_url( $custom_logo_url ); ?>"
											placeholder="<?php esc_html_e( 'Upload or enter the file URL', 'wp-security-audit-log' ); ?>"
											class="wsal_upload_field">
								</span>
								<span class="wsal_upload_file">
									<a href="#"
											data-uploader-title="<?php esc_html_e( 'Insert File', 'wp-security-audit-log' ); ?>"
											data-uploader-button-text="Insert"
											class="wsal_upload_file_button"
											onclick="return false;"><?php esc_html_e( 'Upload a File', 'wp-security-audit-log' ); ?></a>
								</span>
							</div>
							<p class="description"><?php esc_html_e( 'The logo size should be 440px x 90px.', 'wp-security-audit-log' ); ?></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<label for="report-custom-logo-link"><?php esc_html_e( 'Logo link URL', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<input type="text" name="CustomReportsLogoLink" id="report-custom-logo-link"
								value="<?php echo esc_url( $custom_logo_link ); ?>"
								placeholder="<?php esc_html_e( 'Enter the logo URL', 'wp-security-audit-log' ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'Specify the URL that the logo should link to, so when users click on the logo in the report, they are redirected to that URL.', 'wp-security-audit-log' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary"
						value="<?php esc_html_e( 'Save', 'wp-security-audit-log' ); ?>">
			</p>
		</form>
		<script type="application/javascript">
			// @formatter:off
			jQuery(document).ready(function($) {
				var file_frame;
				window.formfield = "";
				$(document.body).on("click", ".wsal_upload_file_button", function(e) {
					e.preventDefault();
					var button = $(this);
					window.formfield = $(this).closest(".wsal_upload_field_container"),
					file_frame || ((file_frame = wp.media.frames.file_frame = wp.media({
						frame: "post",
						state: "insert",
						title: button.data("uploader-title"),
						button: {
							text: button.data("uploader-button-text")
						},
						multiple: !1
					})).on("menu:render:default", function(view) {
						view.unset("library-separator");
						view.unset("gallery");
						view.unset("featured-image");
						view.unset("embed");
						view.set({});
					}),
					file_frame.on("insert", function() {
						file_frame.state().get("selection").each(function(attachment, index) {
							attachment = attachment.toJSON();
							window.formfield.find("input").val(attachment.url);
						});
					})),
					file_frame.open()
				});
			});
			// @formatter:on
		</script>
		<style type="text/css">
			.wsal_upload_field_container, input[name="CustomReportsLogoLink"] {
				position: relative;
				width: 65%;
			}

			.wsal_upload_field {
				width: 100%;
			}

			.wsal_upload_file {
				background: #fff;
				display: block;
				padding: 2px 8px 2px;
				position: absolute;
				top: 4px;
				right: 10px;
			}
		</style>
		<?php
}

	/**
	 * Handles saving of the submitted form on admin_init.
	 *
	 * Redirect to the same page with settings_updated=yes in the URL in case of success.
	 *
	 * Otherwise, it populates an array of errors.
	 *
	 * @since 4.4.2.1
	 */
	public function maybe_save_form() {
		// Bail if nonce check fails.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wsal_reports_' . self::$slug ) ) {
			return;
		}

		// Bail early if current user doesn't have the right privileges.
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			return;
		}

		$business_name = isset( $_POST['ReportsBusinessName'] ) ? sanitize_text_field( wp_unslash( $_POST['ReportsBusinessName'] ) ) : '';
		$this->plugin->settings()->set_business_name( $business_name );

		$contact_name = isset( $_POST['ReportsContactName'] ) ? sanitize_text_field( wp_unslash( $_POST['ReportsContactName'] ) ) : '';
		$this->plugin->settings()->set_contact_name( $contact_name );

		$contact_email = isset( $_POST['ReportsContactEmail'] ) ? sanitize_email( wp_unslash( $_POST['ReportsContactEmail'] ) ) : '';
		$this->plugin->settings()->set_contact_email( $contact_email );

		$contact_phone = isset( $_POST['ReportsContactPhone'] ) ? sanitize_text_field( wp_unslash( $_POST['ReportsContactPhone'] ) ) : '';
		$this->plugin->settings()->set_contact_phone_number( $contact_phone );

		$custom_logo_url = isset( $_POST['CustomReportsLogo'] ) ? esc_url_raw( wp_unslash( $_POST['CustomReportsLogo'] ) ) : '';
		$this->plugin->settings()->set_custom_reports_logo( $custom_logo_url );

		$custom_logo_link = isset( $_POST['CustomReportsLogoLink'] ) ? esc_url_raw( wp_unslash( $_POST['CustomReportsLogoLink'] ) ) : '';
		$this->plugin->settings()->set_custom_reports_logo_link( $custom_logo_link );

		// Indicate that we updated settings.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'             => 'wsal-rep-views-main',
					'settings_updated' => 'yes',
				),
				network_admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Shows notices communicating the result of saving the settings.
	 *
	 * @return void
	 * @since 4.4.2.1
	 */
	public function show_notices_if_needed() {
		if ( array_key_exists( 'settings_updated', $_GET ) && 'yes' === sanitize_text_field( $_GET['settings_updated'] ) ) { // phpcs:ignore
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings updated', 'wp-security-audit-log' ) . '</p></div>';

			return;
		}

		settings_errors( 'wsal_reports_' . self::$slug );
	}
}
