<?php
/**
 * View: Connection Tab
 *
 * External DB connection tab view.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      3.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * External DB connection tab class.
 *
 * @package    wsal
 * @subpackage external-db
 */
final class WSAL_Ext_Connections {

	/**
	 * Instance of WSAL.
	 *
	 * @var WpSecurityAuditLog
	 */
	private $plugin;

	/**
	 * Holds the error message
	 *
	 * @var string
	 *
	 * @since 4.4.2.1
	 */
	protected $error_message;

	/**
	 * Constructor.
	 *
	 * @param WpSecurityAuditLog $wsal – Instance of WSAL.
	 */
	public function __construct( $wsal ) {
		$this->plugin = $wsal;

		add_action( 'wsal_ext_db_header', array( $this, 'enqueue_styles' ) );
		add_action( 'wsal_ext_db_footer', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wsal_delete_connection', array( $this, 'delete_connection' ) );
		add_action( 'wp_ajax_wsal_connection_test', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_wsal_check_requirements', array( $this, 'check_requirements' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'deregister_scripts' ), PHP_INT_MAX );
	}

	/**
	 * Tab Connections Render.
	 *
	 * phpcs:disable WordPress.Security.NonceVerification.Recommended
	 */
	public function render() {

		$page       = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$tab        = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false;
		$connection = isset( $_GET['connection'] ) ? sanitize_text_field( wp_unslash( $_GET['connection'] ) ) : false;

		// Check if configuring a connection.
		if ( ! empty( $page ) && ! empty( $tab ) && ! empty( $connection ) && 'wsal-ext-settings' === $page && 'connections' === $tab ) :
			$this->configure_connection( $connection );
		else :
			// Get connections.
			$connections = \WSAL\Helpers\Settings_Helper::get_all_connections();
			?>
			<?php
			$allowed_tags     = array(
				'a' => array(
					'href'   => true,
					'target' => true,
				),
			);
			$description_text = sprintf(
				/* translators: A string wrapped in a link saying to create and configure databases and services connections. */
				__( 'In this section you can %s. Database connections can be used as an external database and for activity log archiving. Third party services connections can be used to mirror the activity logs into them. You can have multiple connections. Please note that connections that are in use cannot be deleted.', 'wp-security-audit-log' ),
				sprintf(
					'<a href="%1$s" rel="noopener noreferrer" target="_blank">%2$s</a>',
					esc_url( 'https://wpactivitylog.com/support/kb/getting-started-external-databases-third-party-services/?utm_source=plugin&utm_medium=referral&utm_campaign=WSAL&utm_content=settings+pages' ),
					__( 'create and configure databases and services connections', 'wp-security-audit-log' )
				)
			);
			?>
			<p><?php echo wp_kses( $description_text, $allowed_tags ); ?></p>
			<p>
				<button id="wsal-create-connection"
						class="button button-hero button-primary"><?php esc_html_e( 'Create a Connection', 'wp-security-audit-log' ); ?></button>
			</p>
			<!-- Create a Connection -->
			<h3><?php esc_html_e( 'Connections', 'wp-security-audit-log' ); ?></h3>
			<table id="wsal-external-connections" class="wp-list-table widefat fixed striped logs">
				<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Type', 'wp-security-audit-log' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Used for', 'wp-security-audit-log' ); ?></th>
					<th scope="col"></th>
					<th scope="col"></th>
					<th scope="col"></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( ! $connections ) : ?>
					<tr class="no-items">
						<td class="colspanchange"
							colspan="6"><?php esc_html_e( 'No connections so far.', 'wp-security-audit-log' ); ?></td>
					</tr>
					<?php
				else :
					foreach ( $connections as $connection ) :
						$conf_args     = array(
							'page'       => 'wsal-ext-settings',
							'tab'        => 'connections',
							'connection' => $connection['name'],
						);
						$configure_url = add_query_arg( $conf_args, network_admin_url( 'admin.php' ) );

						$mirroring_libraries_available = WSAL_Extension_Manager::is_mirroring_available();
						$button_disabled_class         = ( ! $mirroring_libraries_available && 'mysql' !== $connection['type'] ) ? ' disabled' : '';
						?>
						<tr>
							<td><?php echo isset( $connection['name'] ) ? esc_html( $connection['name'] ) : false; ?></td>
							<td><?php echo isset( $connection['type'] ) ? esc_html( $connection['type'] ) : false; ?></td>
							<td><?php echo isset( $connection['used_for'] ) ? esc_html( $connection['used_for'] ) : false; ?></td>
							<td>
								<a href="<?php echo esc_url( $configure_url ); ?>"
									class="button-primary<?php echo $button_disabled_class; // phpcs:ignore ?>"><?php esc_html_e( 'Configure', 'wp-security-audit-log' ); ?></a>
							</td>
							<!-- Configure -->
							<td>
								<?php
								/**
								 * Sets the text to use for the test button.
								 *
								 * For syslog it's not correct to imply that
								 * a full test was completed since connect
								 * is UDP.
								 */
								if ( 'syslog' === $connection['type'] ) {
									$button_text = __( 'Send a test message', 'wp-security-audit-log' );
								} else {
									$button_text = __( 'Test', 'wp-security-audit-log' );
								}
								?>
								<a href="javascript:;"
									data-connection="<?php echo esc_attr( $connection['name'] ); ?>"
									data-nonce="<?php echo esc_attr( wp_create_nonce( $connection['name'] . '-test' ) ); ?>"
									class="button button-secondary wsal-conn-test<?php echo $button_disabled_class; // phpcs:ignore ?>"><?php echo esc_html( $button_text ); ?></a>
							</td>
							<!-- Test -->
							<td>
								<button type="button"
										data-connection="<?php echo esc_attr( $connection['name'] ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( $connection['name'] . '-delete' ) ); ?>"
										class="button button-danger wsal-conn-delete"
									<?php disabled( isset( $connection['used_for'] ) && ! empty( $connection['used_for'] ) ); ?>
								><?php esc_html_e( 'Delete', 'wp-security-audit-log' ); ?></button>
							</td>
							<!-- Delete -->
						</tr>
						<?php
					endforeach;
				endif;
				?>
				</tbody>
			</table>
			<?php
			// Create connection wizard.
			$this->wizard();
		endif;
	}

	/**
	 * Configure Connection View.
	 *
	 * @param string $conn_name - Connection name.
	 */
	private function configure_connection( $conn_name ) {
		if ( ! $conn_name ) {
			esc_html_e( 'No connection name specified!', 'wp-security-audit-log' );

			return;
		}

		$connection = $this->plugin->external_db_util->get_connection( $conn_name );

		if ( 'mysql' === $connection['type'] ) {
			$mirror_type = 'mysql';
		} else {

			$mirror_type  = null;
			$mirror_types = $this->plugin->external_db_util->get_mirror_types();
			if ( array_key_exists( $connection['type'], $mirror_types ) ) {
				$mirror_type = $mirror_types[ $connection['type'] ];
			}
		}
		?>
		<h1><?php echo esc_html__( 'Configure Connection → ', 'wp-security-audit-log' ) . esc_html( $connection['name'] ); ?></h1>
		<br>
		<?php
		if ( 'mysql' !== $mirror_type && ! WSAL_Extension_Manager::is_mirroring_available() ) {
			WSAL_Extension_Manager::render_helper_plugin_notice( esc_html__( 'Helper plugin is required to edit existing mirror connections.', 'wp-security-audit-log' ) );
			return;
		}
		?>
			<form method="POST" class="js-wsal-connection-form">
				<?php wp_nonce_field( 'wsal-connection-configure' ); ?>
				<?php $this->print_connection_form_field( $connection, 'name' ); ?>
				<h3><?php esc_html_e( 'Configure the connection', 'wp-security-audit-log' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Configure the connection details.', 'wp-security-audit-log' ); ?></p>
				<?php $this->print_connection_form_field( $connection, $connection['type'], $mirror_type ); ?>
				<input type="hidden" name="connection[type]" value="<?php echo esc_attr( $connection['type'] ); ?>"/>
				<input type="hidden" name="connection[update]" value="1"/>
				<?php submit_button( esc_html__( 'Save Connection', 'wp-security-audit-log' ) ); ?>
			</form>
		<?php
	}

	/**
	 * Get Connection Field.
	 *
	 * @param array  $connection Connection details (configuration).
	 * @param string $connection_type Connection type. Special connection type "name" can be passed to print form field for entering the connection name.
	 * @param array  $mirror_type Mirror type definition.
	 *
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function print_connection_form_field( $connection, $connection_type, $mirror_type = null ) {
		$connection_name = is_array( $connection ) && array_key_exists( 'name', $connection ) ? $connection['name'] : '';
		if ( 'name' === $connection_type ) :
			?>
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<label for="connection-name"><?php esc_html_e( 'Connection Name', 'wp-security-audit-log' ); ?></label>
					</th>
					<td>
						<fieldset>
							<input type="text" name="connection[name]" id="connection-name" class="required connection" value="<?php echo esc_attr( $connection_name ); ?>"/>
						</fieldset>
					</td>
				</tr>
				</tbody>
			</table>
		<?php elseif ( 'mysql' === $connection_type ) : ?>
			<div class="details-mysql">
				<table class="form-table">
					<tbody>
					<tr>
						<th>
							<label for="db-name"><?php esc_html_e( 'Database Name', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<input type="text" name="connection[mysql][dbName]" id="db-name" class="required"
									value="<?php echo isset( $connection['db_name'] ) ? esc_attr( $connection['db_name'] ) : false; ?>"/>
								<p class="description"><?php esc_html_e( 'Specify the name of the database where you will store the WordPress activity log.', 'wp-security-audit-log' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="db-user"><?php esc_html_e( 'Database User', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<input type="text" name="connection[mysql][dbUser]" id="db-user" class="required"
									value="<?php echo isset( $connection['user'] ) ? esc_attr( $connection['user'] ) : false; ?>"/>
								<p class="description"><?php esc_html_e( 'Specify the username to be used to connect to the database.', 'wp-security-audit-log' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="db-password"><?php esc_html_e( 'Database Password', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<input type="password" name="connection[mysql][dbPassword]" id="db-password" class="required" />
								<p class="description"><?php esc_html_e( 'Specify the password each time you want to submit new changes. For security reasons, the plugin does not store the password in this form.', 'wp-security-audit-log' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="db-hostname"><?php esc_html_e( 'Database Hostname', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<input type="text" name="connection[mysql][dbHostname]" id="db-hostname" class="required"
									value="<?php echo isset( $connection['hostname'] ) ? esc_attr( $connection['hostname'] ) : false; ?>"/>
								<p class="description"><?php esc_html_e( 'Specify the hostname or IP address of the database server.', 'wp-security-audit-log' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="db-base-prefix"><?php esc_html_e( 'Database Base Prefix', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<input type="text" name="connection[mysql][dbBasePrefix]" id="db-base-prefix" class="required"
									value="<?php echo isset( $connection['baseprefix'] ) ? esc_attr( $connection['baseprefix'] ) : false; ?>"
									<?php disabled( isset( $connection['url_prefix'] ) && '1' === $connection['url_prefix'] ); ?>
								/>
								<p class="description"><?php esc_html_e( 'Specify a prefix for the database tables of the activity log. Ideally this prefix should be different from the one you use for WordPress so it is not guessable.', 'wp-security-audit-log' ); ?></p>
								<br>
								<label for="db-url-base-prefix">
									<input type="checkbox" name="connection[mysql][dbUrlBasePrefix]"
										id="db-url-base-prefix"
										value="1" <?php checked( isset( $connection['url_prefix'] ) && $connection['url_prefix'] ); ?> />
									<?php esc_html_e( 'Use website URL as table prefix', 'wp-security-audit-log' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th><label for="db-ssl"><?php esc_html_e( 'SSL/TLS', 'wp-security-audit-log' ); ?></label></th>
						<td>
							<fieldset>
								<label for="db-ssl">
									<input type="checkbox" name="connection[mysql][dbSSL]" id="db-ssl"
										value="1" <?php isset( $connection['is_ssl'] ) ? checked( $connection['is_ssl'] ) : false; ?> />
									<?php esc_html_e( 'Enable to use SSL/TLS to connect with the MySQL server.', 'wp-security-audit-log' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="db-ssl-cc"><?php esc_html_e( 'Client Certificate', 'wp-security-audit-log' ); ?></label>
						</th>
						<td>
							<fieldset>
								<label for="db-ssl-cc">
									<input type="checkbox" name="connection[mysql][sslCC]" id="db-ssl-cc"
										value="1" <?php isset( $connection['is_cc'] ) ? checked( $connection['is_cc'] ) : false; ?> />
									<?php esc_html_e( 'Enable to use SSL/TLS certificates below to connect with the MySQL server.', 'wp-security-audit-log' ); ?>
								</label>
							</fieldset>
							<fieldset>
								<input type="text" name="connection[mysql][sslCA]" id="db-ssl-ca"
									placeholder="<?php esc_attr_e( 'CA SSL Certificate (--ssl-ca)', 'wp-security-audit-log' ); ?>"
									value="<?php echo isset( $connection->ssl_ca ) ? esc_attr( $connection->ssl_ca ) : false; ?>"/>
							</fieldset>
							<fieldset>
								<input type="text" name="connection[mysql][sslCert]" id="db-ssl-cert"
									placeholder="<?php esc_attr_e( 'Server SSL Certificate (--ssl-cert)', 'wp-security-audit-log' ); ?>"
									value="<?php echo isset( $connection->ssl_cert ) ? esc_attr( $connection->ssl_cert ) : false; ?>"/>
							</fieldset>
							<fieldset>
								<input type="text" name="connection[mysql][sslKey]" id="db-ssl-key"
									placeholder="<?php esc_attr_e( 'Client Certificate (--ssl-key)', 'wp-security-audit-log' ); ?>"
									value="<?php echo isset( $connection->ssl_key ) ? esc_attr( $connection->ssl_key ) : false; ?>"/>
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		<?php elseif ( is_array( $mirror_type ) ) : ?>
			<?php if ( array_key_exists( 'fields', $mirror_type['config'] ) && ! empty( $mirror_type['config']['fields'] ) ) : ?>
				<div class="details-<?php echo $connection_type; ?>">
					<table class="form-table">
						<tbody>
						<?php foreach ( $mirror_type['config']['fields'] as $field_key => $field ) : ?>
							<?php
							$input_css_classes = array();
							if ( array_key_exists( 'required', $field ) && true === $field['required'] ) {
								array_push( $input_css_classes, 'required' );
							}

							if ( array_key_exists( 'validation', $field ) && ! empty( $field['validation'] ) ) {
								array_push( $input_css_classes, $field['validation'] );
							}
							?>
							<tr>
								<th>
									<label for="<?php echo $connection_type; ?>-<?php echo $field_key; ?>"><?php echo $field['label']; ?></label>
								</th>
								<td>
									<fieldset>
										<?php if ( 'text' === $field['type'] ) : ?>
											<input type="text"
												<?php if ( ! empty( $input_css_classes ) ) : ?>
													class="<?php echo implode( ' ', $input_css_classes ); ?>"
												<?php endif; ?>
												<?php if ( array_key_exists( 'error', $field ) ) : ?>
													data-msg="<?php echo $field['error']; ?>"
												<?php endif; ?>
													name="connection[<?php echo $connection_type; ?>][<?php echo $field_key; ?>]"
													id="<?php echo $connection_type; ?>-<?php echo $field_key; ?>"
													value="<?php echo isset( $connection[ $field_key ] ) ? esc_attr( $connection[ $field_key ] ) : false; ?>"/>
										<?php elseif ( 'checkbox' === $field['type'] ) : ?>
											<label for="<?php echo $connection_type; ?>-<?php echo $field_key; ?>">
												<input type="checkbox" value="yes"
													<?php if ( ! empty( $input_css_classes ) ) : ?>
														class="<?php echo implode( ' ', $input_css_classes ); ?>"
													<?php endif; ?>
													<?php if ( array_key_exists( 'error', $field ) ) : ?>
														data-msg="<?php echo $field['error']; ?>"
													<?php endif; ?>
														name="connection[<?php echo $connection_type; ?>][<?php echo $field_key; ?>]"
														id="<?php echo $connection_type; ?>-<?php echo $field_key; ?>"
													<?php checked( isset( $connection[ $field_key ] ) && 'yes' === $connection[ $field_key ] ); ?> />
												<?php echo $field['text']; ?>
											</label>
										<?php elseif ( 'select' === $field['type'] ) : ?>
											<select name="connection[<?php echo $connection_type; ?>][<?php echo $field_key; ?>]"
												<?php if ( ! empty( $input_css_classes ) ) : ?>
													class="<?php echo implode( ' ', $input_css_classes ); ?>"
												<?php endif; ?>>
												<?php if ( array_key_exists( 'error', $field ) ) : ?>
													data-msg="<?php echo $field['error']; ?>"
												<?php endif; ?>
												<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
													<option value="<?php echo $option_value; ?>"
														<?php selected( isset( $connection[ $field_key ] ) && $option_value === $connection[ $field_key ] ); ?>><?php echo $option_label; ?></option>
												<?php endforeach; ?>
											</select>
										<?php elseif ( 'radio' === $field['type'] ) : ?>
											<?php if ( array_key_exists( 'options', $field ) && ! empty( $field['options'] ) ) : ?>
												<?php foreach ( $field['options'] as $option_key => $option_data ) : ?>
													<label for="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $option_key; ?>">
														<input type="radio"
																name="connection[<?php echo $connection_type; ?>][<?php echo $field_key; ?>]"
																id="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $option_key; ?>"
																value="<?php echo $option_key; ?>"
															<?php if ( ! empty( $input_css_classes ) ) : ?>
																class="<?php echo implode( ' ', $input_css_classes ); ?>"
															<?php endif; ?>
															<?php if ( array_key_exists( 'error', $field ) ) : ?>
																data-msg="<?php echo $field['error']; ?>"
															<?php endif; ?>
															<?php checked( (isset( $connection[ $field_key ] ) && $connection[ $field_key ] == $option_key) || (!isset( $connection[ $field_key ]) && isset($option_data['checked']))); // phpcs:ignore ?> />
														<?php echo $option_data['label']; ?>
														<?php if ( array_key_exists( 'subfields', $option_data ) && ! empty( $option_data['subfields'] ) ) : ?>
															
															<?php foreach ( $option_data['subfields'] as $subfield_key => $subfield ) : ?>
																<?php if ( 'radio' === $subfield['type'] ) : ?>
																	<br/>
																	<br/>
																	<span class="subfield-label"><?php echo $subfield['label']; ?></span>
																	<?php foreach ( $subfield['options'] as $sub_option_key => $sub_option_data ) : ?>
																		<label for="<?php echo $connection_type; ?>-<?php echo $subfield_key; ?>-<?php echo $sub_option_key; ?>">
																			<input type="radio"
																				name="connection[<?=$connection_type?>][<?php echo $option_key; ?>-<?php echo $subfield_key; ?>]"
																				id="<?php echo $connection_type; ?>-<?php echo $subfield_key; ?>-<?php echo $sub_option_key; ?>"
																				class="subfield" 
																				value="<?php echo $sub_option_key; ?>"
																				<?php if ( array_key_exists( 'error', $field ) ) : ?>
																					data-msg="<?php echo $field['error']; ?>"
																				<?php endif; ?>
																				<?php 
																				if (isset( $connection[ $option_key . '-' . $subfield_key ] )) {
																				
																				checked( isset( $connection[ $option_key . '-' . $subfield_key ] ) && $connection[ $option_key . '-' . $subfield_key ] == $sub_option_key ); // phpcs:ignore 
																				} else {
																					if (isset ($sub_option_data['checked']) && true===$sub_option_data['checked']) {

																						echo 'checked="checked"';

																					}
																				}
																				
																				?> />
																			<?php echo $sub_option_data['label']; ?>
																		</label>
																	<?php endforeach; ?>
																<?php endif; ?>
															<?php endforeach; ?>
														<?php endif; ?>

													</label>
													<br/>
													<?php if ( array_key_exists( 'subfields', $option_data ) && ! empty( $option_data['subfields'] ) ) : ?>
														<?php foreach ( $option_data['subfields'] as $subfield_key => $subfield ) : ?>
															<?php if ( 'text' === $subfield['type'] ) : ?>
																<label for="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $subfield_key; ?>">														
																	<?php if ( array_key_exists( 'label', $subfield ) ) : ?>
																		<span class="subfield-label"><?php echo $subfield['label']; ?></span>
																	<?php endif; ?>
																	<input type="text"
																		<?php if ( array_key_exists( 'validation', $subfield ) && ! empty( $subfield['validation'] ) ) : ?>
																			class="<?php echo $subfield['validation']; ?>"
																		<?php endif; ?>
																		<?php if ( array_key_exists( 'required', $subfield ) && true === $subfield['required'] ) : ?>
																			data-required-if="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $option_key; ?>"
																		<?php endif; ?>
																		<?php if ( array_key_exists( 'error', $subfield ) ) : ?>
																			data-msg="<?php echo $subfield['error']; ?>"
																		<?php endif; ?>
																		name="connection[<?php echo $connection_type; ?>][<?php echo $option_key; ?>-<?php echo $subfield_key; ?>]"
																		id="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $subfield_key; ?>"
																		value="<?php echo isset( $connection[ $option_key . '-' . $subfield_key ] ) ? esc_attr( $connection[ $option_key . '-' . $subfield_key ] ) : false; ?>"/>
																</label>																
																<br/>
															<?php endif; ?>
															<?php if ( 'checkbox' === $subfield['type'] ) : ?>
																<label for="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $subfield_key; ?>">														
																	<?php if ( array_key_exists( 'label', $subfield ) ) : ?>
																		<span class="subfield-label"><?php echo $subfield['label']; ?></span>
																	<?php endif; ?>
																	<input type="checkbox"
																		<?php if ( array_key_exists( 'validation', $subfield ) && ! empty( $subfield['validation'] ) ) : ?>
																			class="<?php echo $subfield['validation']; ?>"
																		<?php endif; ?>
																		<?php if ( array_key_exists( 'required', $subfield ) && true === $subfield['required'] ) : ?>
																			data-required-if="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $option_key; ?>"
																		<?php endif; ?>
																		<?php if ( array_key_exists( 'error', $subfield ) ) : ?>
																			data-msg="<?php echo $subfield['error']; ?>"
																		<?php endif; ?>
																		name="connection[<?php echo $connection_type; ?>][<?php echo $option_key; ?>-<?php echo $subfield_key; ?>]"
																		<?php checked( isset( $connection[ $option_key . '-' . $subfield_key ] ) && 'yes' === $connection[ $option_key . '-' . $subfield_key ] ); ?>
																		id="<?php echo $connection_type; ?>-<?php echo $field_key; ?>-<?php echo $subfield_key; ?>"
																		value="yes" />
																</label>																
																<br/>
															<?php endif; ?>
														<?php endforeach; ?>
													<?php endif; ?>
												<?php endforeach; ?>
											<?php endif; ?>
										<?php endif; ?>
										<?php if ( array_key_exists( 'desc', $field ) ) : ?>
											<p class="description">
												<?php echo $field['desc']; ?>
											</p>
										<?php endif; ?>
									</fieldset>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php
		endif;
	}

	/**
	 * Connection Wizard
	 *
	 * @param string $connection_name – Connection name.
	 */
	private function wizard( $connection_name = '' ) {
		// Check connection parameter.
		$connection = array();
		if ( $connection_name ) {
			// Get connection settings.
			$connection = $this->plugin->external_db_util->get_connection( $connection_name );
		}

		$mirror_types = $this->plugin->external_db_util->get_mirror_types();

		// Convert the mirror types to a list of alphabetically sorted connection types.
		$connection_type_options = array(
			'mysql' => esc_html__( 'MySQL Database', 'wp-security-audit-log' ),
		);

		foreach ( $mirror_types as $mirror_type => $mirror_config ) {
			$connection_type_options[ $mirror_type ] = $mirror_config['name'];
		}

		asort( $connection_type_options );

		$mirroring_available = WSAL_Extension_Manager::is_mirroring_available();
		if ( ! $mirroring_available && empty( $connection ) ) {
			// This will force the MySQL option to be selected by default when the mirroring is not available. Some
			// older browsers would consider the first disabled option as selected without this.
			$connection['type'] = 'mysql';
		}
		?>
			<div id="wsal-connection-wizard" class="hidden">
				<form method="POST">
					<?php wp_nonce_field( 'wsal-connection-wizard' ); ?>
					<input type="hidden" name="connection" value="<?php esc_attr__( 'Save Connection', 'wp-security-audit-log' ); ?>"/>
					<h3 class="step-title"><?php echo str_replace( ' ', '<br />', esc_html__( 'Select type', 'wp-security-audit-log' ) ); ?></h3>
					<div class="step-content">
						<h3><?php esc_html_e( 'Select the type of connection', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Select the type of connection you would like to setup.', 'wp-security-audit-log' ); ?></p>
						<table class="form-table">
							<tbody>
							<tr>
								<th>
									<label for="connection-type"><?php esc_html_e( 'Type of Connection', 'wp-security-audit-log' ); ?></label>
								</th>
								<td>
									<fieldset>
										<select name="connection[type]" id="connection-type" class="required">
											<?php foreach ( $connection_type_options as $type_id => $type_name ) : ?>
												<?php
												$is_disabled = 'mysql' !== $type_id && ! $mirroring_available;
												$is_selected = isset( $connection['type'] ) && $connection['type'] === $type_id;
												?>
												<option value="<?php echo esc_attr( $type_id ); ?>" <?php echo ( $is_disabled ) ? 'data-notification="show"' : ''; ?> <?php selected( $is_selected ); ?>><?php echo $type_name; ?></option>
											<?php endforeach; ?>
										</select>
									</fieldset>
								</td>
							</tr>
							</tbody>
						</table>
						<?php
						if ( ! $mirroring_available ) {
							WSAL_Extension_Manager::render_helper_plugin_notice(
								esc_html__( 'To create a connection to a third party service such as AWS Cloudwatch, Loggly and Papertrail you need to install an extension. Please click the button below to automatically install and activate the plugin extension so you can create the connection.', 'wp-security-audit-log' ),
								'margin-left: 0;display: none;',
								true
							);
						}
						?>
					</div>

					<h3 class="step-title"><?php esc_html_e( 'Check requirements', 'wp-security-audit-log' ); ?></h3>
					<div class="step-content">
						<h3><?php esc_html_e( 'Requirements check', 'wp-security-audit-log' ); ?></h3>
						<input type="hidden" name="connection[requirements]" class="requirements">
						<div class="progress-pane"></div>
					</div>

					<h3 class="step-title"><?php esc_html_e( 'Configure connection', 'wp-security-audit-log' ); ?></h3>
					<div class="step-content" data-next-enabled-by-default="yes">
						<h3><?php esc_html_e( 'Configure the connection', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Configure the connection details.', 'wp-security-audit-log' ); ?></p>
						<?php $this->print_connection_form_field( $connection, 'mysql' ); ?>
						<?php foreach ( $mirror_types as $mirror_id => $mirror_type ) : ?>
							<?php $this->print_connection_form_field( $connection, $mirror_id, $mirror_type ); ?>
						<?php endforeach; ?>
					</div>

					<h3 class="step-title"><?php echo str_replace( ' ', '<br />', esc_html__( 'Test connection', 'wp-security-audit-log' ) ); ?></h3>
					<div class="step-content">
						<h3><?php esc_html_e( 'Connectivity test', 'wp-security-audit-log' ); ?></h3>
						<input type="hidden" name="connection[test]" class="connectionTest">
						<div class="progress-pane"></div>
					</div>

					<h3 class="step-title"><?php esc_html_e( 'Name the connection', 'wp-security-audit-log' ); ?></h3>
					<div class="step-content">
						<h3><?php esc_html_e( 'Name the connection', 'wp-security-audit-log' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Please specify a friendly name for the connection. Connection names can be 25 characters long and can only contain letters, numbers and underscores.', 'wp-security-audit-log' ); ?></p>
						<?php $this->print_connection_form_field( $connection, 'name' ); // Get connection name field. ?>
					</div>
				</form>
			</div>
		<?php
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'jquery-steps',
			WSAL_Ext_Common::get_extension_base_url() . 'css/dist/jquery.steps.css',
			array(),
			WSAL_VERSION
		);

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style(
			'wsal-connections-css',
			WSAL_Ext_Common::get_extension_base_url() . 'css/connections.css',
			array( 'wp-jquery-ui-dialog', 'jquery-steps' ),
			WSAL_VERSION
		);
	}

	/**
	 * That is needed because the wizard has problem with the jquery form (which is in fact deprecated) so we have to removed it to be sure that the wizard will keep working
	 *
	 * @return void
	 *
	 * @since 4.4.2.1
	 */
	public static function deregister_scripts() {
		global $pagenow;

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wsal-ext-settings' === $_GET['page'] ) {

			\wp_dequeue_script( 'jquery-form' );
			\wp_deregister_script( 'jquery-form' );

			/**
			 * If Divi theme is in use - we know for sure that it tries to use jquery-form - remove its JS - its not needed on the wizard pages
			 */
			if ( class_exists( 'ET_Theme_Builder_Request' ) ) {
				\wp_dequeue_script( 'et-core-admin' );
				\wp_deregister_script( 'et-core-admin' );
				\wp_dequeue_script( 'et-core-version-rollback' );
				\wp_deregister_script( 'et-core-version-rollback' );
			}
		}
	}

	/**
	 * Enqueue tab scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_script(
			'jquery-validation',
			WSAL_Ext_Common::get_extension_base_url() . 'js/dist/jquery.validate.js',
			array( 'jquery' ),
			WSAL_VERSION,
			true
		);

		wp_enqueue_script(
			'jquery-steps',
			WSAL_Ext_Common::get_extension_base_url() . 'js/dist/jquery.steps.js',
			array( 'jquery-validation', 'jquery-ui-dialog' ),
			WSAL_VERSION,
			true
		);

		// Connections script file.
		wp_register_script(
			'wsal-connections-js',
			WSAL_Ext_Common::get_extension_base_url() . 'js/connections.js',
			array( 'jquery-steps' ),
			WSAL_VERSION,
			true
		);

		// @codingStandardsIgnoreStart
		$connection = isset( $_GET['connection'] ) ? sanitize_text_field( wp_unslash( $_GET['connection'] ) ) : false;
		// @codingStandardsIgnoreEnd

		$script_data = array(
			'ajaxURL'                 => admin_url( 'admin-ajax.php' ),
			'cancelLabel'             => esc_html__( 'Cancel', 'wp-security-audit-log' ),
			'checking_requirements'   => esc_html__( 'Checking requirements...', 'wp-security-audit-log' ),
			'connection'              => $connection,
			'confirm'                 => esc_html__( 'Are you sure that you want to delete this connection?', 'wp-security-audit-log' ),
			'connFailed'              => esc_html__( 'Connection failed!', 'wp-security-audit-log' ),
			'connFailedMessage'       => esc_html__( 'Connection test failed! Please check the connection configuration or try again later.', 'wp-security-audit-log' ),
			'connSuccess'             => esc_html__( 'Connected', 'wp-security-audit-log' ),
			'connTest'                => esc_html__( 'Testing...', 'wp-security-audit-log' ),
			'deleting'                => esc_html__( 'Deleting...', 'wp-security-audit-log' ),
			'finishLabel'             => esc_html__( 'Save Connection', 'wp-security-audit-log' ),
			'nextLabel'               => esc_html__( 'Next', 'wp-security-audit-log' ),
			'previousLabel'           => esc_html__( 'Previous', 'wp-security-audit-log' ),
			'requirementsCheckFailed' => esc_html__( 'Unable to check the requirements at the moment. Communication with the server failed. Try again later.', 'wp-security-audit-log' ),
			'sendingTestMessage'      => esc_html__( 'Sending a test message...', 'wp-security-audit-log' ),
			'urlBasePrefix'           => WSAL_Ext_Common::get_url_for_db(),
			'wizardTitle'             => esc_html__( 'Connections Wizard', 'wp-security-audit-log' ),
			'wpNonce'                 => wp_create_nonce( 'wsal-create-connections' ),
		);

		wp_localize_script( 'wsal-connections-js', 'wsalConnections', $script_data );
		wp_enqueue_script( 'wsal-connections-js' );
	}

	/**
	 * Save Connections Form.
	 */
	public function save() {
		// Only run the function on audit log custom page.
		global $pagenow;
		if ( 'admin.php' !== $pagenow ) {
			return;
		}

		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Current page.
		// @codingStandardsIgnoreEnd

		if ( 'wsal-ext-settings' !== $page ) { // Page is admin.php, now check auditlog page.
			return; // Return if the current page is not auditlog's.
		}

		// Check if submitting.
		if ( ! isset( $_POST['connection'] ) && ! isset( $_POST['submit'] ) ) {
			return;
		}

		// Check nonce.
		if ( isset( $_POST['connection']['update'] ) ) {
			check_admin_referer( 'wsal-connection-configure' );
		} else {
			check_admin_referer( 'wsal-connection-wizard' );
		}

		// Get connection details.
		$type      = isset( $_POST['connection']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['connection']['type'] ) ) : false;
		$details   = isset( $_POST['connection'][ $type ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['connection'][ $type ] ) ) : false;
		$conn_name = isset( $_POST['connection']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['connection']['name'] ) ) : false;

		if ( 'mysql' === $type ) {
			$db_name       = isset( $details['dbName'] ) ? sanitize_text_field( wp_unslash( $details['dbName'] ) ) : false;
			$db_user       = isset( $details['dbUser'] ) ? sanitize_text_field( wp_unslash( $details['dbUser'] ) ) : false;
			$db_password   = isset( $details['dbPassword'] ) ? sanitize_text_field( wp_unslash( $details['dbPassword'] ) ) : false;
			$db_password   = $this->plugin->external_db_util->encrypt_password( $db_password );
			$db_hostname   = isset( $details['dbHostname'] ) ? sanitize_text_field( wp_unslash( $details['dbHostname'] ) ) : false;
			$db_baseprefix = isset( $details['dbBasePrefix'] ) ? sanitize_text_field( wp_unslash( $details['dbBasePrefix'] ) ) : false;
			$db_urlbasepre = isset( $details['dbUrlBasePrefix'] ) ? sanitize_text_field( wp_unslash( $details['dbUrlBasePrefix'] ) ) : false;
			$is_ssl        = isset( $details['dbSSL'] ) ? sanitize_text_field( wp_unslash( $details['dbSSL'] ) ) : false;
			$is_cc         = isset( $details['sslCC'] ) ? sanitize_text_field( wp_unslash( $details['sslCC'] ) ) : false;
			$ssl_ca        = isset( $details['sslCA'] ) ? sanitize_text_field( wp_unslash( $details['sslCA'] ) ) : false;
			$ssl_cert      = isset( $details['sslCert'] ) ? sanitize_text_field( wp_unslash( $details['sslCert'] ) ) : false;
			$ssl_key       = isset( $details['sslKey'] ) ? sanitize_text_field( wp_unslash( $details['sslKey'] ) ) : false;

			if ( ! empty( $db_urlbasepre ) ) {
				$db_baseprefix = WSAL_Ext_Common::get_url_for_db();
			}
			// Create the connection object.
			$connection = array(
				'name'       => $conn_name,
				'type'       => $type,
				'user'       => $db_user,
				'password'   => $db_password,
				'db_name'    => $db_name,
				'hostname'   => $db_hostname,
				'baseprefix' => $db_baseprefix,
				'url_prefix' => $db_urlbasepre,
				'is_ssl'     => $is_ssl,
				'is_cc'      => $is_cc,
				'ssl_ca'     => $ssl_ca,
				'ssl_cert'   => $ssl_cert,
				'ssl_key'    => $ssl_key,
			);

			try {
				$result = WSAL_Connector_ConnectorFactory::check_config( $connection );
				if ( true === $result ) {
					// Install tables.
					$this->plugin->get_connector( $connection )->install_all( true );
				}
			} catch ( Exception $ex ) {
				add_action( 'admin_notices', array( $this, 'connection_failed_notice' ), 10 );

				return;
			}
		} else {

			$mirror_types = $this->plugin->external_db_util->get_mirror_types();
			if ( ! array_key_exists( $type, $mirror_types ) ) {
				// Unsupported mirror type (this should actually never happen).
				return;
			}

			$mirror_type = $mirror_types[ $type ];
			if ( array_key_exists( 'config', $mirror_type ) && array_key_exists( 'fields', $mirror_type['config'] ) ) {
				// @todo validate fields (only JS validation was present as this happens in modal in non-AJAX fashion)
				$connection = array_merge(
					array(
						'name' => $conn_name,
						'type' => $type,
					),
					$details
				);
			}
		}

		if ( ! isset( $_POST['connection']['update'] ) ) {

			$connection_name = ( $connection instanceof stdClass ) ? $connection->name : $connection['name'];
			$this->plugin->alerts->trigger_event_if(
				6320,
				array(
					'EventType' => 'added',
					'type'      => ( $connection instanceof stdClass ) ? $connection->type : $connection['type'],
					'name'      => $connection_name,
				)
			);

			// Add new option for connection.
			$this->plugin->external_db_util->save_connection( $connection );
		} elseif ( isset( $_POST['connection']['update'] ) && isset( $_GET['connection'] ) ) {
			// Get original connection name.
			$ogc_name            = sanitize_text_field( wp_unslash( $_GET['connection'] ) );
			$original_connection = $this->plugin->external_db_util->get_connection( $ogc_name );

			// If the option name is changed then delete the previous one.
			$new_connection_name = $connection['name'];
			if ( $new_connection_name !== $ogc_name ) {
				$this->plugin->external_db_util->delete_connection( $ogc_name );

				if ( 'mysql' === $type ) {
					// Check if the connection was used as an external database.
					$external_db_connection_name = \WSAL\Helpers\Settings_Helper::get_option_value( 'adapter-connection' );
					if ( $ogc_name === $external_db_connection_name ) {
						\WSAL\Helpers\Settings_Helper::set_option_value( 'adapter-connection', $new_connection_name, true );
					}

					// Check if the connection was used as an archive database.
					$archive_db_connection_name = \WSAL\Helpers\Settings_Helper::get_option_value( 'archive-connection' );
					if ( $ogc_name === $archive_db_connection_name ) {
						\WSAL\Helpers\Settings_Helper::set_option_value( 'archive-connection', $new_connection_name );
					}
				}

				// Check if the connection was used for mirroring and update the mirrors.
				$mirrors = $this->plugin->external_db_util->get_mirrors_by_connection_name( $ogc_name );
				if ( ! empty( $mirrors ) ) {
					foreach ( $mirrors as $mirror ) {
						$mirror['connection'] = $new_connection_name;
						$this->plugin->external_db_util->save_mirror( $mirror );
					}
				}
			}

			if ( isset( $mirror_type ) && ! empty( $mirror_type ) ) {
				foreach ( $mirror_type['config']['fields'] as $name => $field ) {
					if ( 'checkbox' === $field['type'] ) {
						if ( ! isset( $connection[ $name ] ) ) {
							unset( $original_connection[ $name ] );
						}
					}
					if ( isset( $field['options'] ) && is_array( $field['options'] ) && ! empty( $field['options'] ) ) {
						foreach ( $field['options'] as $o_name => $o_value ) {
							if ( isset( $o_value['type'] ) && 'checkbox' === $o_value['type'] ) {
								if ( ! isset( $connection[ $o_name ] ) ) {
									unset( $original_connection[ $o_name ] );
								}
							}
							if ( isset( $o_value['subfields'] ) && is_array( $o_value['subfields'] ) && ! empty( $o_value['subfields'] ) ) {
								foreach ( $o_value['subfields'] as $s_name => $s_value ) {
									if ( isset( $s_value['type'] ) && 'checkbox' === $s_value['type'] ) {
										if ( ! isset( $connection[ $o_name . '-' . $s_name ] ) ) {
											unset( $original_connection[ $o_name . '-' . $s_name ] );
										}
									}
								}
							}
						}
					}
				}
			}

			// Data from original connection needs to be merged in (this is because the "used_for" is not sent from the form).
			$new_connection = array_merge( $original_connection, $connection );
			$this->plugin->external_db_util->save_connection( $new_connection );

			$this->plugin->alerts->trigger_event(
				6321,
				array(
					'type' => $type,
					'name' => $new_connection_name,
				)
			);
		}

		if ( isset( $_GET['connection'] ) ) {
			$redirect_args = array(
				'page' => 'wsal-ext-settings',
				'tab'  => 'connections',
			);
			// If current site is multisite then redirect to network audit log.
			$admin_url = ( WpSecurityAuditLog::is_multisite() ) ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );

			$redirect_url = add_query_arg( $redirect_args, $admin_url );
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Admin notice for failed connection.
	 */
	public function connection_failed_notice() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'Connection failed. Please check the configuration again.', 'wp-security-audit-log' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Delete Connection Handler.
	 */
	public function delete_connection() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access Denied.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		$nonce      = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$connection = isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : false;
		if ( ! $nonce || ! $connection || ! wp_verify_nonce( $nonce, $connection . '-delete' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ),
				)
			);
			exit();
		}

		$this->plugin->external_db_util->delete_connection( $connection );
		echo wp_json_encode( array( 'success' => true ) );
		exit();
	}

	/**
	 * Test Connection Handler.
	 */
	public function test_connection() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			wp_send_json_error( esc_html__( 'Access Denied.', 'wp-security-audit-log' ) );
		}

		// Check if nonce value is set (further down we figure out what value to check against).
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		if ( ! $nonce ) {
			wp_send_json_error( esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ) );
		}

		$connection      = array();
		$connection_name = '';
		$nonce_to_check  = 'wsal-connection-wizard';
		if ( isset( $_POST['connection'] ) ) {
			// We have a name of existing connection.
			$connection_name = isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : false;
			$nonce_to_check  = $connection_name . '-test';
		}

		if ( ! wp_verify_nonce( $nonce, $nonce_to_check ) ) {
			wp_send_json_error( esc_html__( 'Access Denied.', 'wp-security-audit-log' ) );
		}

		$connection = array();
		if ( isset( $_POST['connection'] ) ) {
			// We have a name of existing connection, let's load the details from the db.
			$connection = $this->plugin->external_db_util->get_connection( $connection_name );
		} else {
			// This is a request from connection test wizard slide, all the connection settings are in the request.
			parse_str( $_POST['config'], $post_config ); // phpcs:ignore
			if ( is_array( $post_config ) && array_key_exists( 'connection', $post_config ) ) {
				$connection = $post_config['connection'];
				// The actual config is at this point nested under key matching the connection type, we need to pull it out.
				$connection = array_merge( $connection, $connection[ $connection['type'] ] );
				unset( $connection[ $connection['type'] ] );
			}
		}

		$connection = \map_deep( \wp_unslash( $connection ), 'rawurldecode' );

		if ( isset( $connection['type'] ) && 'mysql' === $connection['type'] ) {
			try {
				$connection_test_result = false;
				if ( empty( $connection_name ) ) {
					$db_name           = isset( $connection['dbName'] ) ? $connection['dbName'] : false;
					$db_user           = isset( $connection['dbUser'] ) ? $connection['dbUser'] : false;
					$db_password       = isset( $connection['dbPassword'] ) ? $connection['dbPassword'] : false;
					$db_password       = $this->plugin->external_db_util->encrypt_password( $db_password );
					$db_hostname       = isset( $connection['dbHostname'] ) ? $connection['dbHostname'] : false;
					$db_baseprefix     = isset( $connection['dbBasePrefix'] ) ? $connection['dbBasePrefix'] : false;
					$db_url_baseprefix = isset( $connection['dbUrlBasePrefix'] ) ? $connection['dbUrlBasePrefix'] : false;
					$db_ssl            = isset( $connection['dbSSL'] ) ? $connection['dbSSL'] : false;
					$ssl_cc            = isset( $connection['sslCC'] ) ? $connection['sslCC'] : false;
					$ssl_ca            = isset( $connection['sslCA'] ) ? $connection['sslCA'] : false;
					$ssl_cert          = isset( $connection['sslCert'] ) ? $connection['sslCert'] : false;
					$ssl_key           = isset( $connection['sslKey'] ) ? $connection['sslKey'] : false;

					// Convert string values to boolean.
					$db_url_baseprefix = \WSAL\Helpers\Options::string_to_bool( $db_url_baseprefix );
					$db_ssl            = \WSAL\Helpers\Options::string_to_bool( $db_ssl );
					$ssl_cc            = \WSAL\Helpers\Options::string_to_bool( $ssl_cc );

					if ( ! empty( $db_url_baseprefix ) ) {
						$db_baseprefix = WSAL_Ext_Common::get_url_for_db();
					}

					$connection_test_result = WSAL_Connector_ConnectorFactory::check_config(
						array(
							'type'       => 'mysql',
							'user'       => $db_user,
							'password'   => $db_password,
							'db_name'    => $db_name,
							'hostname'   => $db_hostname,
							'baseprefix' => $db_baseprefix,
							'is_ssl'     => $db_ssl,
							'is_cc'      => $ssl_cc,
							'ssl_ca'     => $ssl_ca,
							'ssl_cert'   => $ssl_cert,
							'ssl_key'    => $ssl_key,
						)
					);
				} else {
					$connection_test_result = WSAL_Connector_ConnectorFactory::check_config( $connection );
				}

				if ( false === $connection_test_result ) {
					wp_send_json_error( esc_html__( 'Connection failed.', 'wp-security-audit-log' ) );
				}
				wp_send_json_success( esc_html__( 'Connection successful.', 'wp-security-audit-log' ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		} else {
			$mirror_types = $this->plugin->external_db_util->get_mirror_types();
			if ( array_key_exists( $connection['type'], $mirror_types ) ) {

				// Get website info.
				if ( WpSecurityAuditLog::is_multisite() ) {
					$site_id = get_current_blog_id();
					$info    = get_blog_details( $site_id, true );
					$website = ( ! $info ) ? 'Unknown_site_' . $site_id : str_replace( ' ', '_', $info->blogname );
				} else {
					$website = str_replace( ' ', '_', get_bloginfo( 'name' ) );
				}

				$current_date = WSAL_Utilities_DateTimeFormatter::instance()->get_formatted_date_time( time(), 'datetime', true, false, false );
				$log_message  = $current_date . ' ' . $website . ' Security_Audit_Log:Test message by WP Activity Log plugin';

				$monolog_helper = $this->plugin->external_db_util->get_monolog_helper();
				try {
					// We pass null here as mirror, the logging code is written so that it only uses it if it is available.
					$monolog_helper->log(
						$connection,
						null,
						9999,
						$log_message,
						array(
							'paramA' => 'test',
							'paramB' => 123,
							'paramC' => array(
								'key'    => 'value',
								'plugin' => 'random',
							),
						)
					);

					wp_send_json_success( esc_html__( 'Connection successful.', 'wp-security-audit-log' ) );
				} catch ( Exception $exception ) {
					wp_send_json_error( $exception->getMessage() );
				}
			} else {
				wp_send_json_error( esc_html__( 'Unknown connection type.', 'wp-security-audit-log' ) );
			}
		}
	}

	/**
	 * Handles AJAX calls from the connection setup wizard for checking the requirements.
	 *
	 * @since 4.3.0
	 */
	public function check_requirements() {
		if ( ! $this->plugin->settings()->current_user_can( 'edit' ) ) {
			wp_send_json_error( esc_html__( 'Access Denied.', 'wp-security-audit-log' ) );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wsal-connection-wizard' ) ) {
			wp_send_json_error( esc_html__( 'Nonce verification failed.', 'wp-security-audit-log' ) );
		}

		// Get connection type.
		if ( ! array_key_exists( 'type', $_POST ) || empty( $_POST['type'] ) ) {
			wp_send_json_error( esc_html__( 'Connection type is missing.', 'wp-security-audit-log' ) );
		}

		$connection_type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
		$errors          = array();
		if ( 'mysql' === $connection_type ) {
			if ( ! $this->check_mysqli_exists() ) {
				$errors[]            = esc_html__( 'Mysqli extension is not loaded.', 'wp-security-audit-log' );
				self::$error_message = \esc_html__( 'mysqli extension is not loaded', 'wp-security-audit-log' );
			}
		} else {

			$mirror_types = $this->plugin->external_db_util->get_mirror_types();
			if ( ! array_key_exists( $connection_type, $mirror_types ) ) {
				// Unrecognized mirror type.
				wp_send_json_error( esc_html__( 'Unrecognized mirror type.', 'wp-security-audit-log' ) );
			}

			try {
				$mirror_type = $mirror_types[ $connection_type ];
				if ( ! $mirror_type['class']::check_requirements() ) {
					$errors[] = \esc_html__( 'System requirements are not met.', 'wp-security-audit-log' );
				}
			} catch ( Exception $exception ) {
				wp_send_json_error( esc_html__( 'Requirements check failed.', 'wp-security-audit-log' ) . ' ' . $exception->getMessage() );
			}
		}

		if ( empty( $errors ) ) {
			wp_send_json_success( esc_html__( 'All requirements are met. Your system is ready to use the selected connection type.', 'wp-security-audit-log' ) );
		}

		$error_message = esc_html__( 'Selected connection type cannot be used on your system at the moment. The following requirements are not met.', 'wp-security-audit-log' );

		wp_send_json_error(
			array(
				'message' => $error_message,
				'errors'  => array( $mirror_type['class']::get_alternative_error_message() ),
			)
		);
	}

	/**
	 * Checks software requirements for MySQLi connection.
	 *
	 * @return array
	 * @since 4.3.0
	 */
	private function check_mysqli_exists(): bool {
		if ( \extension_loaded( 'mysqli' ) ) {
			return true;
		}

		return false;
	}
}
