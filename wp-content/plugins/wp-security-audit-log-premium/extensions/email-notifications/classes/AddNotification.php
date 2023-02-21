<?php
/**
 * View: Add Notification
 *
 * Add notification view class file.
 *
 * @since 1.0.0
 * @package wsal
 * @subpackage email-notifications
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_NP_AddNotification for Add notification Page.
 *
 * @package wsal
 * @subpackage email-notifications
 */
class WSAL_NP_AddNotification extends WSAL_AbstractView {

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

	/**
	 * Method: Constructor.
	 *
	 * @param WpSecurityAuditLog $wsal - Instance of WpSecurityAuditLog.
	 * @since 2.7.0
	 */
	public function __construct( WpSecurityAuditLog $wsal ) {
		// Set the paths.
		$this->base_dir = WSAL_BASE_DIR . 'extensions/email-notifications';
		$this->base_url = WSAL_BASE_URL . 'extensions/email-notifications';
		parent::__construct( $wsal );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_title() {
		return esc_html__( 'Add New Email Notification', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_icon() {
		return 'dashicons-admin-generic';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return esc_html__( 'Add notification', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_weight() {
		return 9;
	}

	/**
	 * {@inheritDoc}
	 */
	public function header() {
		wp_enqueue_style(
			'wsal-jq-timepick-css',
			$this->base_url . '/js/jquery.timeentry/jquery.timeentry.css',
			array(),
			'2.0.0'
		);

		wp_enqueue_style(
			'wsal-notif-css',
			$this->base_url . '/css/styles.css',
			array(),
			WSAL_VERSION
		);

		wp_enqueue_script(
			'wsal-markup-js',
			$this->base_url . '/js/markup.js/src/markup.min.js',
			array( 'jquery' ),
			WSAL_VERSION,
			false
		);

		echo "<script type='text/javascript'>";
		echo "var dateFormat = '" . esc_html( $this->plugin->notifications_util->date_valid_format() ) . "';";
		echo "var show24Hours = '" . esc_html( $this->plugin->notifications_util->show_24_hours() ) . "';";
		echo '</script>';

		wp_enqueue_script(
			'wsal-notif-utils-js',
			$this->base_url . '/js/wsal-notification-utils.js',
			array( 'jquery' ),
			WSAL_VERSION,
			false
		);

		wp_enqueue_script(
			'wsal-validator-js',
			$this->base_url . '/js/wsal-form-validator.js',
			array( 'jquery' ),
			WSAL_VERSION,
			false
		);

		wp_enqueue_script(
			'wsal-groups-js',
			$this->base_url . '/js/wsal-groups.js',
			array( 'jquery' ),
			WSAL_VERSION,
			false
		);
		?>
		<script type="text/javascript">
			<?php
			include realpath( dirname( __FILE__ ) . '/../' ) . '/js/wsal-translator.js';

			// Get WP Post Types.
			$post_types = get_post_types( array(), 'names' );
			unset( $post_types['attachment'] );
			$post_types = implode( ', ', $post_types );
			$post_types = strtoupper( $post_types );

			// Get WP user roles.
			$wp_user_roles = $this->plugin->notifications_util->get_wp_user_roles();
			foreach ( $wp_user_roles as $role => $details ) {
				$user_roles[ $role ] = translate_user_role( $details['name'] );
			}
			$user_roles = implode( ', ', $user_roles );
			$user_roles = strtoupper( $user_roles );

			// Get events 'Object' data.
			$objects = array_keys( $this->plugin->alerts->get_event_objects_data() );
			$objects = array_map(
				function( $object ) {
					return str_replace( '-', ' ', $object );
				},
				$objects
			);
			$objects = implode( ', ', $objects );
			$objects = strtoupper( $objects );

			// Get events 'Event Type' data.
			$type = array_keys( $this->plugin->alerts->get_event_type_data() );
			$type = array_map(
				function( $single_type ) {
					return str_replace( '-', ' ', $single_type );
				},
				$type
			);
			$type = implode( ', ', $type );
			$type = strtoupper( $type );
			?>
			var WsalPostTypes = {
				post_types : "<?php echo esc_html( $post_types ); ?>"
			};
			var WsalUserRoles = {
				user_roles : "<?php echo esc_html( $user_roles ); ?>"
			};
			var WsalObjects = {
				objects : "<?php echo esc_html( $objects ); ?>"
			};
			var WsalTypes = {
				type : "<?php echo esc_html( $type ); ?>"
			};
		</script>
		<?php
		WSAL_Helpers_Assets::load_datepicker();
		wp_enqueue_script(
			'wsal-jq-timepick-js',
			$this->base_url . '/js/jquery.timeentry/jquery.timeentry.min.js',
			array( 'jquery', 'wsal-datepick-plugin-js' ),
			WSAL_VERSION,
			false
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function footer() {}

	/**
	 * {@inheritDoc}
	 */
	public function render_title() {
		$back_link = add_query_arg(
			array(
				'page' => 'wsal-np-notifications',
				'tab'  => 'custom',
			),
			admin_url( 'admin.php' )
		);
		?>
		<h2>
			<?php echo esc_html( $this->get_title() ); ?>
			<a href="<?php echo esc_url( $back_link ); ?>" class="add-new-h2"><?php esc_html_e( 'Back', 'wp-security-audit-log' ); ?></a>
		</h2>
		<?php
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
		}

		if ( ! $this->plugin->notifications_util->can_add_notification() ) {
			?>
			<div class="error">
				<p><?php esc_html_e( 'You have reached the maximum number of notifications you can add.', 'wp-security-audit-log' ); ?></p>
			</div>
			<?php
			return;
		}

		// Flag for postbacks.
		$__wsal_is_postback = false;
		$rm                 = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : false;

		if ( 'POST' === $rm && isset( $_POST['wsal_add_notification_field'] ) ) {
			// Verify nonce.
			check_admin_referer( 'wsal_add_notification_action', 'wsal_add_notification_field' );

			if ( isset( $_POST['wsal_form_data'] ) ) {
				$notif_builder = new WSAL_NP_NotificationBuilder();
				$notification  = $notif_builder->decode_from_string( $_POST['wsal_form_data'] ); // phpcs:ignore

				if ( ! empty( $_POST['subject'] ) && ! empty( $_POST['body'] ) ) {
					$notification->info->subject = trim( $_POST['subject'] ); // phpcs:ignore
					$notification->info->body    = wpautop( wp_unslash( $_POST['body'] ) ); // phpcs:ignore
				}

				$this->plugin->notifications_util->save_notification( $notif_builder, $notification );
				$__wsal_is_postback = true;
			} else {
				// Not a valid request.
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
			}
		}

		$phone_disabled = ! $this->plugin->notifications_util->is_twilio_configured() ? 'disabled' : false;
		$phone_help     = false;

		if ( $phone_disabled ) {
			// Twilio settings tab link.
			$twilio_settings = add_query_arg(
				array(
					'page' => 'wsal-settings',
					'tab'  => 'sms-provider',
				),
				admin_url( 'admin.php' )
			);

			/* Translators: Twilio settings hyperlink. */
			$phone_help = sprintf( esc_html__( 'Click %s to configure Twilio integration for SMS notifications.', 'wp-security-audit-log' ), '<a href="' . esc_url( $twilio_settings ) . '">' . esc_html__( 'here', 'wp-security-audit-log' ) . '</a>' );
		}
		?>
		<div class="wrap">
			<div id="wsal-error-container" class="invalid" style="display:none;"><p></p></div>
			<form id="wsal-trigger-form" method="post">
				<div id="wsal-section-title"></div>
				<div class="wsal-helpbox">
					<p class="description"><?php /* Translators: Trigger groups documentation hyperlink */ echo sprintf( esc_html__( 'Configure the triggers that should match for an email and / or SMS notification to be sent. You can add up to 20 triggers, use the AND and OR operands, and also group triggers together. Refer to the %s for more information.', 'wpsal-notifications' ), '<a href="https://wpactivitylog.com/support/kb/getting-started-email-notifications-add/#grouping-triggers" target="_blank">' . esc_html__( 'Trigger groups documentation', 'wp-security-audit-log' ) . '</a>' ); ?></p>
				</div>
				<div class="postbox">
					<div id="wsal-triggers-view" class="inside">
						<h3 id="wsal-sub-heading" class="f-container">
							<span class="f-left" style="margin-top: 4px;"><?php esc_html_e( 'Triggers', 'wp-security-audit-log' ); ?></span>
							<span class="f-left" style="margin-left: 36px;"><input id="wsal-button-add-trigger" type="button" class="button-secondary" value="+ <?php esc_attr_e( 'Add Trigger', 'wp-security-audit-log' ); ?>"/></span>
						</h3>
						<!-- <div id="wsal-header-top-bar"></div> -->
						<div class="wsal-notification-triggers">
							<?php /*[ Content dynamically added here ]*/ ?>
							<div id="wsal_content_js"></div>
						</div>
					</div>
				</div>
				<pre id="wsal_error_triggers" style="display: none;"></pre>
				<div id="wsal-section-email"></div>
				<div id="wsal-section-radio">
					<label for="default">
						<input id="default" type="radio" name="template" value="default" checked>
						<span><?php esc_html_e( 'Use default email template', 'wp-security-audit-log' ); ?></span>
					</label>
					<br>
					<label for="specific">
						<input id="specific" type="radio" name="template" value="specific">
						<span><?php esc_html_e( 'Use event specific email template', 'wp-security-audit-log' ); ?></span>
					</label>
				</div>
				<div id="wsal-section-template" class="hidden">
					<?php $this->plugin->notifications_util->specific_template(); ?>
				</div>

				<input type="hidden" id="wsal-form-data" name="wsal_form_data"/>
				<?php wp_nonce_field( 'wsal_add_notification_action', 'wsal_add_notification_field' ); ?>
			</form>

			<script type="text/javascript" id="wsalModel">
				// This object will only be populated on POST
				var wsalModelWp = wsalModelWp ? JSON.parse( wsalModelWp ) : null;
				<?php include $this->base_dir . '/js/wsal-notification-model.inc.js'; ?>

				jQuery(document).ready(function($){
					// so we can repopulate fields in case of errors
					jQuery.WSAL_EDIT_VIEW = <?php echo $__wsal_is_postback ? 1 : 0; ?>;
					jQuery.WSAL_MULTISITE = <?php echo WpSecurityAuditLog::is_multisite() ? 1 : 0; ?>;
					<?php include $this->base_dir . '/js/wsal-notifications-view.inc.js'; ?>
				});
			</script>

			<script type="text/template" id="scriptTitle">
				<label for="wsal-notif-title"><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></label>
				<input type="text" size="30" autocomplete="off" id="wsal-notif-title" placeholder="<?php esc_attr_e( 'Title', 'wp-security-audit-log' ); ?> *" value="{{info.title|clean}}" maxlength="125"/>
				{{if errors.titleMissing}}<label class="error" for="wsal-notif-title">{{errors.titleMissing}}</label>{{/if}}
				{{if errors.titleInvalid}}<label class="error" for="wsal-notif-title">{{errors.titleInvalid}}</label>{{/if}}
			</script>

			<script type="text/template" id="scriptEmail">
				<p>
					<span>{{info.emailLabel}}</span>
					<input type="text" id="wsal-notif-email" placeholder="<?php esc_attr_e( 'Email', 'wp-security-audit-log' ); ?>" value="{{info.email|clean}}" />
					{{if errors.emailMissing}}<label class="error" for="wsal-notif-email">{{errors.emailMissing}}</label>{{/if}}
					{{if errors.emailInvalid}}<label class="error" for="wsal-notif-email">{{errors.emailInvalid}}</label>{{/if}}
				</p>
				<p>
					<span>{{info.phoneLabel}}</span>
					<input type="text" id="wsal-notif-phone" placeholder="<?php esc_attr_e( 'Mobile Number', 'wp-security-audit-log' ); ?>" value="{{info.phone|clean}}" <?php echo esc_attr( $phone_disabled ); ?>/>
					{{if errors.phoneMissing}}<label class="error" for="wsal-notif-phone">{{errors.phoneMissing}}</label>{{/if}}
					{{if errors.phoneInvalid}}<label class="error" for="wsal-notif-phone">{{errors.phoneInvalid}}</label>{{/if}}
				</p>
				<p class="description"><?php esc_html_e( 'Specify the email address, WordPress username or a phone number of those who should receive the notification once the trigger is matched.', 'wpsal-notifications' ); ?></p>
				<?php echo $phone_help ? '<p class="description">' . wp_kses( $phone_help, $this->plugin->allowed_html_tags ) . '</p>' : false; ?>
				<p><input type="submit" id="wsal-submit" name="wsal-submit" value="{{buttons.addNotifButton}}" class="button-primary" /></p>
			</script>

			<script type="text/template" id="scriptTrigger">
				<div id="trigger_id_{{lastId}}" class="wsal_trigger">
					<div class="wsal-fly">
						<div class="wsal-s1">
							{{if numTriggers|ormore>2}}
								<span class="custom-dropdown custom-dropdown--default custom-dropdown--small">
									<select id="select_1_{{lastId}}" class="js_s1 custom-dropdown__select custom-dropdown__select--default">
										{{select1.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select1.data}}
									</select>
									<input type="hidden" id="select_1_{{lastId}}_hidden" value="0"/>
								</span>
							{{/if}}
						</div>

						<div class="wsal-s2">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small">
								<select id="select_2_{{lastId}}" class="js_s2 custom-dropdown__select custom-dropdown__select--default">
									{{select2.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select2.data}}
								</select>
								<input type="hidden" id="select_2_{{lastId}}_hidden" value="0"/>
							</span>
						</div>

						<div class="wsal-s3">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small">
								<select id="select_3_{{lastId}}" class="js_s3 custom-dropdown__select custom-dropdown__select--default">
									{{select3.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select3.data}}
								</select>
								<input type="hidden" id="select_3_{{lastId}}_hidden" value="0"/>
							</span>
						</div>

						<div class="wsal-s4 custom-dropdown__hide">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small custom-dropdown__status">
								<select id="select_4_{{lastId}}" class="js_s4 custom-dropdown__select custom-dropdown__select--default">
									{{select4.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select4.data}}
								</select>
								<input type="hidden" id="select_4_{{lastId}}_hidden" value="0"/>
							</span>
						</div>
						<!-- /.wsal-s4 -->

						<div class="wsal-s5 custom-dropdown__hide">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small custom-dropdown__post_type">
								<select id="select_5_{{lastId}}" class="js_s5 custom-dropdown__select custom-dropdown__select--default">
									{{select5.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select5.data}}
								</select>
								<input type="hidden" id="select_5_{{lastId}}_hidden" value="0"/>
							</span>
						</div>
						<!-- /.wsal-s5 -->

						<div class="wsal-s6 custom-dropdown__hide">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small custom-dropdown__user_role">
								<select id="select_6_{{lastId}}" class="js_s6 custom-dropdown__select custom-dropdown__select--default">
									{{select6.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select6.data}}
								</select>
								<input type="hidden" id="select_6_{{lastId}}_hidden" value="0"/>
							</span>
						</div>
						<!-- /.wsal-s6 -->

						<div class="wsal-s7 custom-dropdown__hide">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small custom-dropdown__objects">
								<select id="select_7_{{lastId}}" class="js_s7 custom-dropdown__select custom-dropdown__select--default">
									{{select7.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select7.data}}
								</select>
								<input type="hidden" id="select_7_{{lastId}}_hidden" value="0"/>
							</span>
						</div>
						<!-- /.wsal-s7 -->

						<div class="wsal-s8 custom-dropdown__hide">
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small custom-dropdown__type">
								<select id="select_8_{{lastId}}" class="js_s8 custom-dropdown__select custom-dropdown__select--default">
									{{select8.data}}<option value="{{.|upcase|clean}}" {{if #|first}}selected="selected"{{/if}}>{{.|upcase|clean}}</option>{{/select8.data}}
								</select>
								<input type="hidden" id="select_8_{{lastId}}_hidden" value="0"/>
							</span>
						</div>
						<!-- /.wsal-s8 -->
					</div>
					<div class="wsal-fly dd">
						<input id="input_1_{{lastId}}" class="wsal-trigger-input" value="{{input1|clean}}" placeholder="Required *" maxlength="50"/>
						<input type="button" id="deleteButton_{{lastId}}" value="{{deleteButton}}" data-removeid="trigger_id_{{lastId}}" class="button-secondary"/>
						{{if numTriggers|ormore>2}}
						<div class="wsal_options_dd">
							<div>
							<span class="custom-dropdown custom-dropdown--default custom-dropdown--small">
								<select id="wsal_options_{{lastId}}" class="custom-dropdown__select custom-dropdown__select--default wsal_dd_options"></select>
							</span>
							</div>
						</div>
						{{/if}}
					</div>
				</div>
			</script>
		</div>
		<?php
	}
}
