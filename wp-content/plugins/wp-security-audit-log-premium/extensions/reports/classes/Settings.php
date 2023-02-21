<?php
/**
 * General settings for reports (Premium)
 *
 * Settings tab for reports' settings.
 *
 * @since      4.4.0
 * @package    wsal
 * @subpackage reports
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: WSAL_Rep_Settings
 *
 * Settings tab view class to handle settings page functions for the reports' extension.
 *
 * @since 4.4.0
 */
class WSAL_Rep_Settings {

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
		add_filter( 'wsal_setting_tabs', array( $this, 'add_tab' ), 10, 1 );
	}

	/**
	 * Adds a new tab to plugin settings.
	 *
	 * @param array $wsal_setting_tabs Array of WSAL setting tabs.
	 *
	 * @return array Array of updated WSAL setting tabs.
	 */
	public function add_tab( $wsal_setting_tabs ) {
		$wsal_setting_tabs['reports'] = array(
			'name'     => esc_html__( 'Reports', 'wp-security-audit-log' ),
			'link'     => add_query_arg( 'tab', 'reports' ),
			'render'   => array( $this, 'render' ),
			'save'     => array( $this, 'save' ),
			'priority' => 40,
		);

		return $wsal_setting_tabs;
	}

	/**
	 * Renders the settings tab content.
	 */
	public function render() {
		$disabled  = ! $this->is_active() ? 'disabled' : '';
		$admin_url = ! is_multisite() ? 'admin_url' : 'network_admin_url';
		$buy_now   = add_query_arg( 'page', 'wsal-auditlog-pricing', $admin_url( 'admin.php' ) );
		$html_tags = WpSecurityAuditLog::get_instance()->allowed_html_tags;

		$tab_info_msg = esc_html__( 'In this page you can configure the Reports module settings.', 'wp-security-audit-log' );
		if ( $disabled ) {
			/* Translators: Upgrade now hyperlink. */
			$tab_info_msg = sprintf( esc_html__( 'Reports are available in the Professional and Business Plans. %s to configure and receive SMS notifications.', 'wp-security-audit-log' ), '<a href="' . $buy_now . '">' . esc_html__( 'Upgrade now', 'wp-security-audit-log' ) . '</a>' );
		}

		$settings                 = $this->plugin->settings();
		$report_pruning_enabled   = $settings->is_report_pruning_enabled();
		$report_pruning_threshold = $settings->get_report_pruning_threshold();
		$periodic_reports_time    = $settings->get_periodic_reports_hour_of_day();
		$empty_email_allowed      = $settings->is_empty_email_for_periodic_reports_enabled();
		?>
		<p class="description"> <?php echo wp_kses( $tab_info_msg, $html_tags ); ?></p>
		<table class="form-table wsal-reports-settings">
			<tr>
				<th colspan="2">
					<h3><?php esc_html_e( 'Do you want the plugin to automatically delete old reports?', 'wp-security-audit-log' ); ?></h3>
					<p class="description" style="font-weight: normal;">
						<?php
						printf(
						/* translators: path to the reports' storage directory */
							esc_html__( 'Reports are saved in the %s directory. By default the plugin deletes reports that are older than 30 days. Use the settings below to change this behaviour.', 'wp-security-audit-log' ),
							\WSAL_Settings::get_working_dir_path_static( 'reports', true ) // @codingStandardsIgnoreLine
						);
						?>
					</p>
				</th>
			</tr>
			<tr>
				<th>
					<label for="report-pruning-enabled"><?php esc_html_e( 'Delete old reports', 'wp-security-audit-log' ); ?></label>
				</th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label for="report-pruning-enabled-yes">
							<input type="radio" name="ReportPruningEnabled" value="yes"
									id="report-pruning-enabled-yes"
									<?php checked( $report_pruning_enabled ); ?>
							/>
							<?php esc_html_e( 'Yes', 'wp-security-audit-log' ); ?>
						</label>
						<br>
						<label for="report-pruning-enabled_no">
							<input type="radio" name="ReportPruningEnabled" value="no"
									id="report-pruning-enabled-no"
									<?php checked( ! $report_pruning_enabled ); ?>
							/>
							<?php esc_html_e( 'No', 'wp-security-audit-log' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th>
					<label for="report-pruning-enabled"><?php esc_html_e( 'Delete reports older than', 'wp-security-audit-log' ); ?></label>
				</th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="text" name="ReportPruningThreshold" id="report-pruning-threshold"
								name="ReportPruningThreshold"
								style="width: 45px; text-align: right;"
								value="<?php echo esc_attr( $report_pruning_threshold ); ?>" /><?php echo '&nbsp;' . esc_html__( 'days', 'wp-security-audit-log' ); ?>
						<p class="description">
							<?php esc_html_e( 'The minimum allowed is 30 days and the maximum is 180 days.', 'wp-security-audit-log' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th colspan="2">
					<h3><?php esc_html_e( 'At what time should the periodic reports be sent?', 'wp-security-audit-log' ); ?></h3>
					<p class="description" style="font-weight: normal;">
						<?php esc_html_e( 'By default periodic reports are sent at 8:00AM on the first day of the period\'s termination.', 'wp-security-audit-log' ); ?>
					</p>
				</th>
			</tr>
			<tr>
				<th>
					<label for="periodic-reports-time"><?php esc_html_e( 'Time', 'wp-security-audit-log' ); ?></label>
				</th>
				<td>
					<fieldset>
						<?php
						$scan_hours = $this->get_hours_options();

						$use_am_pm_select  = $this->is_time_format_am_pm();
						$selected_hour     = $periodic_reports_time;
						$selected_day_part = $selected_hour >= 12 ? 'PM' : 'AM';
						if ( $use_am_pm_select && 'PM' === $selected_day_part ) {
							$selected_hour -= 12;
						}
						$selected_hour = str_pad( $selected_hour, 2, '0', STR_PAD_LEFT );
						if ( $use_am_pm_select ) {
							$scan_hours = array_slice( $scan_hours, 0, 12, true );
						}
						?>
						<select name="PeriodicReportsTime" id="periodic-reports-time">
							<?php foreach ( $scan_hours as $value => $html ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $selected_hour ); ?>><?php echo esc_html( $html ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php if ( $use_am_pm_select ) : ?>
							<select name="PeriodicReportsTimeAmPm">
								<?php foreach ( array( 'AM', 'PM' ) as $value ) : ?>
									<option value="<?php echo esc_attr( strtolower( $value ) ); ?>" <?php selected( $value, $selected_day_part ); ?>><?php echo esc_html( $value ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th colspan="2">
					<h3><?php esc_html_e( 'Periodic reports email summary', 'wp-security-audit-log' ); ?></h3>
					<p class="description" style="font-weight: normal;">
						<?php esc_html_e( 'Do you want to receive an email even if there are no event IDs that match the criteria for the periodic reports?', 'wp-security-audit-log' ); ?>
					</p>
				</th>
			</tr>
			<tr>
				<th>
					<label for="periodic_reports_empty_email"><?php esc_html_e( 'Send empty summary emails', 'wp-security-audit-log' ); ?></label>
				</th>
				<td>
					<fieldset>
						<label for="periodic_reports_empty_email_yes">
							<input type="radio" name="EmptyEmailForPeriodicReportsEnabled" value="yes"
									id="periodic_reports_empty_email_yes"
									<?php checked( $empty_email_allowed ); ?>
							/>
							<?php esc_html_e( 'Yes', 'wp-security-audit-log' ); ?>
						</label>
						<br>
						<label for="periodic_reports_empty_email_no">
							<input type="radio" name="EmptyEmailForPeriodicReportsEnabled" value="no"
									id="periodic_reports_empty_email_no"
									<?php checked( ! $empty_email_allowed ); ?>
							/>
							<?php esc_html_e( 'No', 'wp-security-audit-log' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Checks if the reporting extension is active.
	 *
	 * @return boolean True if extension is active.
	 */
	public function is_active() {
		return wsal_freemius()->is_plan_or_trial__premium_only( 'professional' );
	}

	/**
	 * Returns a list of options to display hour of the day in the scan frequency settings.
	 *
	 * @return array
	 */
	private function get_hours_options() {
		return array(
			'00' => _x( '00:00', 'a time string representing midnight', 'wp-security-audit-log' ),
			'01' => _x( '01:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'02' => _x( '02:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'03' => _x( '03:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'04' => _x( '04:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'05' => _x( '05:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'06' => _x( '06:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'07' => _x( '07:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'08' => _x( '08:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'09' => _x( '09:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'10' => _x( '10:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'11' => _x( '11:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'12' => _x( '12:00', 'a time string representing midday', 'wp-security-audit-log' ),
			'13' => _x( '13:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'14' => _x( '14:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'15' => _x( '15:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'16' => _x( '16:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'17' => _x( '17:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'18' => _x( '18:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'19' => _x( '19:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'20' => _x( '20:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'21' => _x( '21:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'22' => _x( '22:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
			'23' => _x( '23:00', 'a time string of hour followed by minutes', 'wp-security-audit-log' ),
		);
	}

	/**
	 * Determines if current WordPress time format is an AM/PM.
	 *
	 * @return bool True is current WordPress time format is an AM/PM
	 */
	private function is_time_format_am_pm() {
		return ( 1 === preg_match( '/[aA]$/', get_option( 'time_format' ) ) );
	}

	/**
	 * Handles saving of the submitted form.
	 *
	 * @throws \Exception Thrown if data validation fails. This is handled in WSAL_Views_Settings:Render().
	 */
	public function save() {
		// @codingStandardsIgnoreStart Nonce verification happens in WSAL_Views_Settings::Render
		$report_pruning_enabled = isset( $_POST['ReportPruningEnabled'] ) ? sanitize_text_field( wp_unslash( $_POST['ReportPruningEnabled'] ) ) : false;
		$this->plugin->settings()->set_report_pruning_enabled( $report_pruning_enabled );

		if ( isset( $_POST['ReportPruningThreshold'] ) ) {
			$threshold = filter_var( sanitize_text_field( wp_unslash( $_POST['ReportPruningThreshold'] ) ), FILTER_SANITIZE_NUMBER_INT );
			if ( empty( $threshold ) || $threshold < 30 || $threshold > 180 ) {
				throw new Exception( esc_html__( 'Number of days should be a number between 30 and 180.', 'wp-security-audit-log' ) );
			}
			$this->plugin->settings()->set_report_pruning_threshold( $threshold );
		}

		if ( isset( $_POST['PeriodicReportsTime'] ) ) {
			//  convert hours + AM/PM setting to the correct number of hours
			$hours = intval( sanitize_text_field( wp_unslash( $_POST['PeriodicReportsTime'] ) ) );

			if ( array_key_exists( 'PeriodicReportsTimeAmPm', $_POST ) && $this->is_time_format_am_pm() ) {
				$day_part = sanitize_text_field( wp_unslash( $_POST['PeriodicReportsTimeAmPm'] ) );
				if ( 'pm' === $day_part ) {
					$hours += 12;
				}
			}
			$this->plugin->settings()->set_periodic_reports_hour_of_day( $hours );
		}

		$empty_email_allowed = isset( $_POST['EmptyEmailForPeriodicReportsEnabled'] ) ? sanitize_text_field( wp_unslash( $_POST['EmptyEmailForPeriodicReportsEnabled'] ) ) : false;
		$this->plugin->settings()->set_empty_email_for_periodic_reports_enabled( $empty_email_allowed );
		// @codingStandardsIgnoreEnd
	}
}
