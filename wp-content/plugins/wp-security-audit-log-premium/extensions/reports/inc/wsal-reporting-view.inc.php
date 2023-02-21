<?php
/**
 * View: Reports Main
 *
 * Main reports view.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage reports
 *
 * phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 * phpcs:disable WordPress.PHP.StrictComparisons.LooseComparison
 */

use WSAL\Helpers\WP_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	return;
}

// Class mapping.
/** @var WSAL_Rep_Common $wsal_common */
$wsal_common = $this->plugin->reports_util;

// Get available alert categories.
$alerts = $this->plugin->alerts->get_categorized_alerts();

// Get the Request method.
$requested_method = strtoupper( $_SERVER['REQUEST_METHOD'] ); // phpcs:ignore

// region >>>  PREPARE DATA FOR JS
// ## SITES
// Limit 0f 100.
$wsal_a         = WSAL_Rep_Common::get_sites( 100 );
$wsal_rep_sites = array();
foreach ( $wsal_a as $entry ) {
	// entry.blog_id, entry.domain.
	$c       = new stdClass();
	$c->id   = $entry->blog_id;
	$c->text = $entry->blogname;
	array_push( $wsal_rep_sites, $c );
}

// ## IPs
// limit 0f 100
$wsal_ips     = WSAL_Rep_Common::get_ip_addresses( 100 );
$wsal_rep_ips = array_map(
	function ( $item ) {
		return array(
			'id'   => $item,
			'text' => $item,
		);
	},
	$wsal_ips
);

// ## ALERT IDS AND GROUPS
$event_ids        = array();
$event_ids_nested = array();
foreach ( $alerts as $cname => $group ) {
	foreach ( $group as $subname => $_entries ) {
		$t           = new stdClass();
		$t->text     = $cname;
		$t->children = array();

		$event_ids = array_merge( $event_ids, wp_list_pluck( $_entries, 'code' ) );
		foreach ( $_entries as $i => $_arr_obj ) {
			$c       = new stdClass();
			$c->id   = $_arr_obj->code;
			$c->text = $c->id . ' (' . $_arr_obj->desc . ')';
			array_push( $t->children, $c );
		}
		array_push( $event_ids_nested, $t );
	}
}

$alert_groups = array();
foreach ( $alerts as $cname => $_entries ) {
	$alert_groups[] = array(
		'id'   => $cname,
		'text' => $cname,
	);
}
$wsal_rep_alert_groups = wp_json_encode( $alert_groups, JSON_HEX_APOS );

// Post Types.
$post_types     = get_post_types( array(), 'names' );
$post_types_arr = array();
foreach ( $post_types as $post_type ) {
	// Skip attachment post type.
	if ( 'attachment' === $post_type ) {
		continue;
	}

	$type       = new stdClass();
	$type->id   = $post_type;
	$type->text = str_replace( '_', ' ', ucfirst( $post_type ) );
	array_push( $post_types_arr, $type );
}

// Post Statuses.
$post_statuses           = get_post_statuses();
$post_statuses['future'] = 'Future';
$post_status_arr         = array();
foreach ( $post_statuses as $key => $post_status ) {
	$status       = new stdClass();
	$status->id   = $key;
	$status->text = $post_status;
	array_push( $post_status_arr, $status );
}
$wsal_rep_post_statuses = wp_json_encode( $post_status_arr, JSON_HEX_APOS );

// Event objects.
$event_objects     = $this->plugin->alerts->get_event_objects_data();
$event_objects_arr = array();

foreach ( $event_objects as $key => $event_object ) {
	$object       = new \stdClass();
	$object->id   = $key;
	$object->text = $event_object;
	array_push( $event_objects_arr, $object );
}

// Event types.
$event_types     = $this->plugin->alerts->get_event_type_data();
$event_types_arr = array();

foreach ( $event_types as $key => $event_type ) {
	$e_type       = new \stdClass();
	$e_type->id   = $key;
	$e_type->text = $event_type;
	array_push( $event_types_arr, $e_type );
}
$wsal_rep_event_types = wp_json_encode( $event_types_arr, JSON_HEX_APOS );

// Endregion >>>  PREPARE DATA FOR JS.
// The final filter array to use to filter alerts.
$filters = array(
	// Option #1 - By Site(s).
	'sites'         => array(), // By default, all sites.

	// Option #2 - By user(s).
	'users'         => array(), // By default, all users.

	// Option #3 - By Role(s).
	'roles'         => array(), // By default, all roles.

	// Option #4 - By IP Address(es).
	'ip-addresses'  => array(), // By default, all IPs.

	// Option #5 - By Alert Code(s).
	'alert_codes'   => array(
		'groups' => array(),
		'alerts' => array(),
	),

	// Option #6 - Date range.
	'date_range'    => array(
		'start' => null,
		'end'   => null,
	),

	// Option #7 - Report format.
	'report_format' => WSAL_Rep_DataFormat::get_default(),

	// By event objects.
	'objects'       => array(),

	// By event types.
	'event-types'   => array(),

	// By post status.
	'post_status'   => array(),

	// By post type.
	'post_types'    => array(),
);

// Figure out the currently active tab.
$active_tab = 'reports';

if ( 'POST' === $requested_method && isset( $_POST['wsal_reporting_view_field'] ) ) {
	// Verify nonce.
	if ( ! wp_verify_nonce( $_POST['wsal_reporting_view_field'], 'wsal_reporting_view_action' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page - rep plugin.', 'reports-wsal' ) );
	}

	// The default error message to display if the form is not valid.
	$message_form_not_valid = __( 'Invalid Request. Please refresh the page and try again.', 'wp-security-audit-log' );

	// Inspect the form data.
	$form_data = $_POST;

	// Region >>>> By Site(s).
	if ( WpSecurityAuditLog::is_multisite() ) {
		if ( isset( $form_data['wsal-rb-sites'] ) ) {
			$rbs = intval( $form_data['wsal-rb-sites'] );
			if ( 2 === $rbs ) {
				// The textbox must be here and have values - these will be validated later on.
				if ( ! isset( $form_data['wsal-rep-sites'] ) || empty( $form_data['wsal-rep-sites'] ) ) {
					echo '<div class="error"><p>' . esc_html__( 'Please select at least one site to include in the report.', 'wp-security-audit-log' ) . '</p></div>';
				} else {
					$filters['sites'] = $form_data['wsal-rep-sites'];
				}
			} elseif ( 3 === $rbs ) {
				// The textbox must be here and have values - these will be validated later on.
				if ( ! isset( $form_data['wsal-rep-sites-exclude'] ) || empty( $form_data['wsal-rep-sites-exclude'] ) ) {
					echo '<div class="error"><p>' . esc_html__( 'Please select at least one site to exclude from the report.', 'wp-security-audit-log' ) . '</p></div>';
				} else {
					$filters['sites-exclude'] = $form_data['wsal-rep-sites-exclude'];
				}
			}
		} else {
			?>
			<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
			<?php
		}
	}
	// endregion >>>> By Site(s).

	// Region >>>> By User(s).
	if ( isset( $form_data['wsal-rb-users'] ) ) {
		$rbs = intval( $form_data['wsal-rb-users'] );
		if ( 2 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-users'] ) || empty( $form_data['wsal-rep-users'] ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Please select at least one user to include in the report.', 'wp-security-audit-log' ) . '</p></div>';
			} else {
				$filters['users'] = $form_data['wsal-rep-users'];
			}
		} elseif ( 3 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-users-exclude'] ) || empty( $form_data['wsal-rep-users-exclude'] ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Please select at least one user to exclude from the report.', 'wp-security-audit-log' ) . '</p></div>';
			} else {
				$filters['users-exclude'] = $form_data['wsal-rep-users-exclude'];
			}
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}
	// endregion >>>> By User(s).

	// Region >>>> By Role(s).
	if ( isset( $form_data['wsal-rb-roles'] ) ) {
		$rbs = intval( $form_data['wsal-rb-roles'] );
		if ( 1 == $rbs ) { // phpcs:ignore
			/*[ already implemented in the $filters array ]*/
		} elseif ( 2 == $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-roles'] ) || empty( $form_data['wsal-rep-roles'] ) ) {
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one role', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			} else {
				$user_roles   = $form_data['wsal-rep-roles'];
				$filter_roles = array();
				if ( ! empty( $user_roles ) ) {
					global $wp_roles;
					foreach ( $user_roles as $index => $urole ) {
						$role_name = strtolower( $urole );
						// if role contains a space try convert it to valid slug.
						if ( strpos( $urole, ' ' ) ) {
							// get the role slug from the passed role nicename.
							$match = false;
							foreach ( $wp_roles->roles as $key => $single_role ) {
								if ( $urole === $single_role['name'] ) {
									$role_name = $key;
									$match     = true;
									break;
								}
							}
							if ( ! $match ) {
								// if we reached this point without a match use
								// lowercase and swap spaces to underscores.
								$role_name = str_replace( ' ', '_', $role_name );

							}
						}
						$filter_roles[] = $role_name;
					}
				}
				$filters['roles'] = $filter_roles;
			}
		} elseif ( 3 == $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-roles-exclude'] ) || empty( $form_data['wsal-rep-roles-exclude'] ) ) {
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one role', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			} else {
				$user_roles   = $form_data['wsal-rep-roles-exclude'];
				$filter_roles = array();
				if ( ! empty( $user_roles ) ) {
					global $wp_roles;
					foreach ( $user_roles as $index => $urole ) {
						$role_name = strtolower( $urole );
						// if role contains a space try convert it to valid slug.
						if ( strpos( $urole, ' ' ) ) {
							// get the role slug from the passed role nicename.
							$match = false;
							foreach ( $wp_roles->roles as $key => $single_role ) {
								if ( $urole === $single_role['name'] ) {
									$role_name = $key;
									$match     = true;
									break;
								}
							}
							if ( ! $match ) {
								// if we reached this point without a match use
								// lowercase and swap spaces to underscores.
								$role_name = str_replace( ' ', '_', $role_name );

							}
						}
						$filter_roles[] = $role_name;
					}
				}
				$filters['roles-exclude'] = $filter_roles;
			}
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}
	// endregion >>>> By Role(s)
	// Region >>>> By IP(s).gw.
	if ( isset( $form_data['wsal-rb-ip-addresses'] ) ) {
		$rbs = intval( $form_data['wsal-rb-ip-addresses'] );
		if ( 1 == $rbs ) { // phpcs:ignore
			/*[ already implemented in the $filters array ]*/
		} elseif ( 2 == $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-ip-addresses'] ) || empty( $form_data['wsal-rep-ip-addresses'] ) ) {
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one IP address', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			} else {
				$filters['ip-addresses'] = $form_data['wsal-rep-ip-addresses'];
			}
		} elseif ( 3 == $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-ip-addresses-exclude'] ) || empty( $form_data['wsal-rep-ip-addresses-exclude'] ) ) {
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one IP address', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			} else {
				$filters['ip-addresses-exclude'] = $form_data['wsal-rep-ip-addresses-exclude'];
			}
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}
	// endregion >>>> By IP(s).
	if ( isset( $form_data['wsal-rb-event-objects'] ) ) {
		$rbs = intval( $form_data['wsal-rb-event-objects'] );
		if ( 2 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-event-objects'] ) || empty( $form_data['wsal-rep-event-objects'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one object', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['objects'] = $form_data['wsal-rep-event-objects'];
			endif;
		} elseif ( 3 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-event-objects-exclude'] ) || empty( $form_data['wsal-rep-event-objects-exclude'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one object', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['objects-exclude'] = $form_data['wsal-rep-event-objects-exclude'];
			endif;
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}
	if ( isset( $form_data['wsal-rb-event-types'] ) ) {
		$rbs = intval( $form_data['wsal-rb-event-types'] );
		if ( 2 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-event-types'] ) || empty( $form_data['wsal-rep-event-types'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event object', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['event-types'] = $form_data['wsal-rep-event-types'];
			endif;
		} elseif ( 3 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-event-types-exclude'] ) || empty( $form_data['wsal-rep-event-types-exclude'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event object', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['event-types-exclude'] = $form_data['wsal-rep-event-types-exclude'];
			endif;
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}
	// Region >>>> By Alert Code(s).
	if ( isset( $form_data['wsal-rb-alert-codes'] ) ) {
		$rbs = intval( $form_data['wsal-rb-alert-codes'] );
		if ( 1 === $rbs ) {
			$filters['alert_codes']['alerts'] = '';
		} elseif ( 2 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-alert-codes'] ) || empty( $form_data['wsal-rep-alert-codes'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event ID', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['alert_codes']['alerts'] = $form_data['wsal-rep-alert-codes'];
			endif;
		} elseif ( 3 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-alert-codes-exclude'] ) || empty( $form_data['wsal-rep-alert-codes-exclude'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event ID', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['alert_codes']['alerts-exclude'] = $form_data['wsal-rep-alert-codes-exclude'];
			endif;
		} elseif ( 4 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-alert-groups'] ) || empty( $form_data['wsal-rep-alert-groups'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event group', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['alert_codes']['groups'] = $form_data['wsal-rep-alert-groups'];
			endif;
		} elseif ( 5 === $rbs ) {
			// The textbox must be here and have values - these will be validated later on.
			if ( ! isset( $form_data['wsal-rep-alert-groups-exclude'] ) || empty( $form_data['wsal-rep-alert-groups-exclude'] ) ) :
				?>
				<div class="error">
					<p><?php esc_html_e( 'Error: Please select at least one event group', 'wp-security-audit-log' ); ?></p>
				</div>
				<?php
			else :
				$filters['alert_codes']['groups-exclude'] = $form_data['wsal-rep-alert-groups-exclude'];
			endif;
		}
	} else {
		?>
		<div class="error"><p><?php echo esc_html( $message_form_not_valid ); ?></p></div>
		<?php
	}

	// Check for selected post IDs.
	if ( isset( $form_data['wsal-rb-post-ids'] ) && 2 === intval( $form_data['wsal-rb-post-ids'] )
		&& isset( $form_data['wsal-rep-post-ids'] ) && ! empty( $form_data['wsal-rep-post-ids'] ) ) {
		// Get selected post IDs.
		$filters['post_ids'] = $form_data['wsal-rep-post-ids'];
	}

	// Check for selected post IDs.
	if ( isset( $form_data['wsal-rb-post-ids'] ) && 3 === intval( $form_data['wsal-rb-post-ids'] )
		&& isset( $form_data['wsal-rep-post-ids-exclude'] ) && ! empty( $form_data['wsal-rep-post-ids-exclude'] ) ) {
		// Get selected post IDs.
		$filters['post_ids-exclude'] = $form_data['wsal-rep-post-ids-exclude'];
	}

	// Check for selected post types.
	if ( isset( $form_data['wsal-rb-post-types'] ) && 2 === intval( $form_data['wsal-rb-post-types'] )
		&& isset( $form_data['wsal-rep-post-types'] ) && ! empty( $form_data['wsal-rep-post-types'] ) ) {
		// Get selected post types.
		$filters['post_types'] = $form_data['wsal-rep-post-types'];
	}

	// Check for selected post types.
	if ( isset( $form_data['wsal-rb-post-types'] ) && 3 === intval( $form_data['wsal-rb-post-types'] )
		&& isset( $form_data['wsal-rep-post-types-exclude'] ) && ! empty( $form_data['wsal-rep-post-types-exclude'] ) ) {
		// Get selected post types.
		$filters['post_types-exclude'] = $form_data['wsal-rep-post-types-exclude'];
	}

	// Check for selected post statuses.
	if ( isset( $form_data['wsal-rb-post-types'] ) && 2 === intval( $form_data['wsal-rb-post-statuses'] )
		&& isset( $form_data['wsal-rb-post-statuses'] ) && isset( $form_data['wsal-rep-post-statuses'] ) && ! empty( $form_data['wsal-rep-post-statuses'] ) ) {
		// Get selected post status.
		$filters['post_statuses'] = $form_data['wsal-rep-post-statuses'];
	}

	// Check for selected post statuses.
	if ( isset( $form_data['wsal-rb-post-types'] ) && 3 === intval( $form_data['wsal-rb-post-statuses'] )
		&& isset( $form_data['wsal-rep-post-statuses-exclude'] ) && ! empty( $form_data['wsal-rep-post-statuses-exclude'] ) ) {
		// Get selected post statuses.
		$filters['post_statuses-exclude'] = $form_data['wsal-rep-post-statuses-exclude'];
	}

	// Report Number of logins.
	if ( isset( $form_data['number_logins'] ) ) {
		$filters['number_logins']         = true;
		$filters['alert_codes']['alerts'] = array( 1000 );
	}

	// Report Number and list of unique IP.
	if ( isset( $form_data['unique_ip'] ) ) {
		$filters['unique_ip'] = true;
	}

	// Region >>>> By Date Range(s).
	if ( isset( $form_data['wsal-start-date'] ) ) {
		$filters['date_range']['start'] = trim( $form_data['wsal-start-date'] );
	}
	if ( isset( $form_data['wsal-end-date'] ) ) {
		$filters['date_range']['end'] = trim( $form_data['wsal-end-date'] );
	}
	// endregion >>>> By Date Range(s).

	// Region >>>> Reporting Format.
	if ( isset( $form_data['wsal-rb-report-type'] ) ) {
		$report_format = intval( $form_data['wsal-rb-report-type'] );
		if ( WSAL_Rep_DataFormat::is_valid( $report_format ) ) {
			$filters['report_format'] = $report_format;
		} else {
			$filters['report_format'] = WSAL_Rep_DataFormat::get_default();
		}
	} else {
		echo '<div class="error"><p>' . esc_html( $message_form_not_valid ) . '</p></div>';
	}
	// Endregion >>>> Reporting Format.

	if ( isset( $form_data['report-custom-title'] ) ) {
		$length = strlen( $form_data['report-custom-title'] );
		if ( $length > 120 ) {
			$errors['report-custom-title'] = sprintf(
				/* translators: number of allowed characters. */
				esc_html__( 'Custom title cannot be longer than %d characters.', 'wp-security-audit-log' ),
				120
			);
		} elseif ( $length > 0 ) {
			$filters['custom_title'] = wp_strip_all_tags( wp_unslash( sanitize_text_field( $form_data['report-custom-title'] ) ) );
		}
	}

	$errors = array();
	if ( isset( $form_data['report-custom-comment'] ) ) {
		$length = strlen( $form_data['report-custom-comment'] );
		if ( $length > 400 ) {
			$errors['report-custom-comment'] = sprintf(
				/* translators: number of allowed characters. */
				esc_html__( 'Comment cannot be longer than %d characters.', 'wp-security-audit-log' ),
				400
			);
		} elseif ( $length > 0 ) {
			$filters['comment'] = wp_strip_all_tags( wp_unslash( sanitize_text_field( $form_data['report-custom-comment'] ) ) );
		}
	}

	if ( isset( $form_data['wsal-reporting-submit'] ) ) {
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error ) {
				echo '<div class="error"><p>' . $error . '</p></div>';
			}
		} else {
			// Button Generate Report Now.
			?>
			<script type="text/javascript">
				var filters = <?php echo wp_json_encode( $filters, JSON_HEX_QUOT ); ?>;
				jQuery(document).ready(function () {
					AjaxCheckArchiveMatch(filters)
					AjaxGenerateReport(filters)
				})
			</script>
			<div class="updated">
				<p id="ajax-response">
					<span id="response-message">
						<img alt="<?php esc_html_e( 'Loading', 'wp-security-audit-log' ); ?>"
								src="<?php echo esc_url( WSAL_BASE_URL . 'extensions/reports/css/loading.gif' ); ?>">
						<?php esc_html_e( ' Your report will be generated in the background. We will send you an email once the report is ready. You can navigate away from this page.', 'wp-security-audit-log' ); ?>
						<span id="ajax-response-counter"></span>
					</span>
					<span id="events-progress" style="display:none">
						<?php esc_html_e( 'Searching events, ', 'wp-security-audit-log' ); ?><span
								id="events-progress-found">0</span><?php esc_html_e( ' currently found.', 'wp-security-audit-log' ); ?>
					</span>
				</p>
			</div>
			<?php
			// Delete the JSON file if it exists.
			$this->uploads_dir_path = \WSAL_Settings::get_working_dir_path_static( 'reports' );
			$filename               = $this->uploads_dir_path . 'report-user' . get_current_user_id() . '.json';
			if ( file_exists( $filename ) ) {
				@unlink( $filename ); // phpcs:ignore
			}
		}
	} elseif ( isset( $form_data['wsal-periodic'] ) ) {
		// Buttons Configure Periodic Reports.
		$filters['frequency'] = $form_data['wsal-periodic'];
		if ( isset( $form_data['wsal-notif-email'] ) && isset( $form_data['wsal-notif-name'] ) ) {
			$filters['email'] = '';
			$arr_emails       = explode( ',', $form_data['wsal-notif-email'] );
			foreach ( $arr_emails as $email ) {
				$filters['email'] .= filter_var( trim( $email ), FILTER_SANITIZE_EMAIL ) . ',';
			}
			$filters['email'] = rtrim( $filters['email'], ',' );
			$filters['name']  = \sanitize_text_field( \wp_unslash( trim( $form_data['wsal-notif-name'] ) ) );
			// By Criteria.
			if ( isset( $form_data['unique_ip'] ) ) {
				$filters['unique_ip'] = true;
			}
			if ( isset( $form_data['number_logins'] ) ) {
				$filters['number_logins'] = true;
			}
			$this->save_periodic_report( $filters );
			?>
			<div class="updated">
				<p><?php esc_html_e( 'Periodic Report successfully saved.', 'wp-security-audit-log' ); ?></p>
			</div>
			<?php
		}
	}
}
// Send Now Periodic Report button.
if ( 'POST' === $requested_method && isset( $_POST['report-send-now'] ) ) {
	if ( isset( $_POST['report-name'] ) ) {
		$report_name = str_replace( WSAL_PREFIX, '', $_POST['report-name'] ); // phpcs:ignore
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				AjaxSendPeriodicReport("<?php echo $report_name; ?>")
			})
		</script>
		<div class="updated">
			<p>
				<span id="response-message">
					<img alt="<?php esc_html_e( 'Loading', 'wp-security-audit-log' ); ?>"
							src="<?php echo esc_url( WSAL_BASE_URL . 'extensions/reports/css/loading.gif' ); ?>">
					<?php esc_html_e( ' Your report will be generated in the background. We will send you an email once the report is ready. You can navigate away from his page.', 'wp-security-audit-log' ); ?>
					<span id="ajax-response-counter"></span>
				</span>
				<span id="events-progress" style="display:none">
					<?php esc_html_e( 'Searching events, ', 'wp-security-audit-log' ); ?><span
							id="events-progress-found">0</span><?php esc_html_e( ' currently found.', 'wp-security-audit-log' ); ?>
				</span>
			</p>
		</div>
		<?php
	}
}

// Modify Periodic Report button.
$current_report = null;
if ( 'POST' === $requested_method && isset( $_POST['report-modify'] ) ) {
	if ( isset( $_POST['report-name'] ) ) {
		$report_name    = str_replace( WSAL_PREFIX, '', $_POST['report-name'] );
		$current_report = $wsal_common->get_periodic_report( $report_name );
	}
}

// Delete Periodic Report button.
if ( 'POST' === $requested_method && isset( $_POST['delete-periodic-report'] ) && isset( $_POST['report-name'] ) ) {
	$wsal_common->delete_global_setting( $_POST['report-name'] );
	?>
	<div class="updated">
		<p><?php esc_html_e( 'Periodic Report successfully deleted.', 'wp-security-audit-log' ); ?></p>
	</div>
	<?php
}

// Delete Saved Report button.
if ( 'POST' === $requested_method && isset( $_POST['delete-saved-report'] ) && isset( $_POST['report-name'] ) ) {
	$wsal_common->delete_saved_report( $_POST['report-name'] );
	$active_tab = 'saved-reports';
	?>
	<div class="updated">
		<p><?php esc_html_e( 'Report successfully deleted.', 'wp-security-audit-log' ); ?></p>
	</div>
	<?php
}

if ( 'POST' === $requested_method && isset( $_POST['wsal-statistics-submit'] ) ) {
	if ( isset( $_POST['wsal-summary-type'] ) ) {
		$report_format = intval( $_POST['wsal-summary-type'] );
		if ( WSAL_Rep_DataFormat::is_valid( $report_format ) ) {
			$filters['report_format'] = $report_format;
		} else {
			$filters['report_format'] = WSAL_Rep_DataFormat::get_default();
		}
	}

	// Statistics report generator.
	$this->generate_statistics_report( $filters );
}

$saved_reports = $wsal_common->get_all_reports();

// Populate list of navigation tabs.
$nav_tabs = array(
	'reports' => esc_html__( 'Generate & configure', 'wp-security-audit-log' ),
	'summary' => esc_html__( 'Statistics reports', 'wp-security-audit-log' ),
);

if ( ! empty( $saved_reports ) ) {
	$nav_tabs['saved-reports'] = esc_html__( 'Saved reports', 'wp-security-audit-log' );
}

$fixed_tabs_slugs = array_keys( $nav_tabs );
$nav_tabs         = apply_filters( 'wsal_reports_views_nav_header_items', $nav_tabs );

$datetime_formatter = WSAL_Utilities_DateTimeFormatter::instance();
?>
<div id="wsal-rep-container">
	<h2 id="wsal-tabs" class="nav-tab-wrapper">
		<?php
		foreach ( $nav_tabs as $tab_id => $tab_label ) {
			$css_class_attr = 'nav-tab';
			if ( $tab_id === $active_tab ) {
				$css_class_attr .= ' nav-tab-active';
			}
			echo '<a href="#tab-' . $tab_id . '" class="' . $css_class_attr . '">' . $tab_label . '</a>';
		}
		?>
	</h2>
	<div class="nav-tabs">
		<div class="wsal-tab wrap" id="tab-reports">

			<p style="clear:both; margin-top: 30px"></p>

			<div class="card" style="max-width: 100%;">
				<form id="wsal-rep-form" action="<?php echo esc_url( $this->get_url() ); ?>" method="post">
					<h3><?php esc_html_e( 'Generate a report', 'wp-security-audit-log' ); ?></h3>

					<?php
					$allowed_tags     = array(
						'a' => array(
							'href'   => true,
							'target' => true,
						),
					);
					$description_text = sprintf(
						'Refer to the %1$sgetting started with WordPress reports%2$s for detailed information on how to generate reports.',
						'<a href="https://wpactivitylog.com/support/kb/getting-started-reports-wordpress/" target="_blank">',
						'</a>'
					);
					?>
					<p><?php echo wp_kses( $description_text, $allowed_tags ); ?></p>

					<hr />

					<!-- SECTION #1 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 1: Select the type of report', 'wp-security-audit-log' ); ?></h4>

					<div class="wsal-rep-form-wrapper">

						<?php if ( WpSecurityAuditLog::is_multisite() ) : ?>
							<!--// BY SITE -->
							<div class="wsal-rep-section">
								<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Site(s)', 'wp-security-audit-log' ); ?></label>
								<table class="wsal-rep-section-fl">
									<tr class="wsal-rep-clear">
										<td>
											<input type="radio" name="wsal-rb-sites" id="wsal-rb-sites-1" value="1"
													checked="checked" />
											<label for="wsal-rb-sites-1"><?php esc_html_e( 'All sites', 'wp-security-audit-log' ); ?></label>
										</td>
									</tr>
									<tr class="wsal-rep-clear">
										<td>
											<input type="radio" name="wsal-rb-sites" id="wsal-rb-sites-2" value="2" />
											<label for="wsal-rb-sites-2"><?php esc_html_e( 'These specific sites', 'wp-security-audit-log' ); ?></label>
										</td>
										<td>
											<?php
											$this->render_generic_selection_field(
												esc_html__( 'Select site(s)', 'wp-security-audit-log' ),
												$wsal_rep_sites,
												'wsal-rep-sites',
												$current_report,
												'sites'
											);
											?>
										</td>
									</tr>
									<tr class="wsal-rep-clear">
										<td>
											<input type="radio" name="wsal-rb-sites" id="wsal-rb-sites-3" value="3" />
											<label for="wsal-rb-sites-3"><?php esc_html_e( 'All sites except these', 'wp-security-audit-log' ); ?></label>
										</td>
										<td>
											<?php
											$this->render_generic_selection_field(
												esc_html__( 'Select site(s)', 'wp-security-audit-log' ),
												$wsal_rep_sites,
												'wsal-rep-sites-exclude',
												$current_report,
												'sites_excluded'
											);
											?>
										</td>
									</tr>
								</table>
							</div>
						<?php endif; ?>

						<!--// BY USER -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By User(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl wsal-rep-section-users">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-users" id="wsal-rb-users-1" value="1"
												checked="checked" />
										<label for="wsal-rb-users-1"><?php esc_html_e( 'All users', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-users" id="wsal-rb-users-2" value="2" />
										<label for="wsal-rb-users-2"><?php esc_html_e( 'These specific users', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-rep-users', $current_report, 'users' ); ?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td width="180">
										<input type="radio" name="wsal-rb-users" id="wsal-rb-users-3" value="3" />
										<label for="wsal-rb-users-3"><?php esc_html_e( 'All users except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-rep-users-exclude', $current_report, 'users_excluded' ); ?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY ROLE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Role(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-roles" id="wsal-rb-roles-1" value="1"
												checked="checked" />
										<label for="wsal-rb-roles-1"><?php esc_html_e( 'All roles', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-roles" id="wsal-rb-roles-2" value="2" />
										<label for="wsal-rb-roles-2"><?php esc_html_e( 'These specific roles', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-rep-roles', $current_report, 'roles' ); ?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-roles" id="wsal-rb-roles-3" value="3" />
										<label for="wsal-rb-roles-3"><?php esc_html_e( 'All roles except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-rep-roles-exclude', $current_report, 'roles_excluded' ); ?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY IP ADDRESS -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By IP address(es)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-ip-addresses" id="wsal-rb-ip-addresses-1"
												value="1" checked="checked" />
										<label for="wsal-rb-ip-addresses-1"><?php esc_html_e( 'All IP addresses', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-ip-addresses" id="wsal-rb-ip-addresses-2"
												value="2" />
										<label for="wsal-rb-ip-addresses-2"><?php esc_html_e( 'These specific IP addresses', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select IP address(es)', 'wp-security-audit-log' ),
											$wsal_rep_ips,
											'wsal-rep-ip-addresses',
											$current_report,
											'ipAddresses'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-ip-addresses" id="wsal-rb-ip-addresses-3"
												value="3" />
										<label for="wsal-rb-ip-addresses-3"><?php esc_html_e( 'All IP addresses except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select IP address(es)', 'wp-security-audit-log' ),
											$wsal_rep_ips,
											'wsal-rep-ip-addresses-exclude',
											$current_report,
											'ipAddresses_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY OBJECT -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Object(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-objects" id="wsal-rb-event-objects-1"
												value="1" checked="checked" />
										<label for="wsal-rb-event-objects-1"><?php esc_html_e( 'All objects', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-objects" id="wsal-rb-event-objects-2"
												value="2" />
										<label for="wsal-rb-event-objects-2"><?php esc_html_e( 'These specific objects', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select object(s)', 'wp-security-audit-log' ),
											$event_objects_arr,
											'wsal-rep-event-objects',
											$current_report,
											'objects'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-objects" id="wsal-rb-event-objects-3"
												value="3" />
										<label for="wsal-rb-event-objects-3"><?php esc_html_e( 'All objects except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select object(s)', 'wp-security-audit-log' ),
											$event_objects_arr,
											'wsal-rep-event-objects-exclude',
											$current_report,
											'objects_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY EVENT TYPE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Event type(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-types" id="wsal-rb-event-types-1"
												value="1" checked="checked" />
										<label for="wsal-rb-event-types-1"><?php esc_html_e( 'All event types', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-types" id="wsal-rb-event-types-2"
												value="2" />
										<label for="wsal-rb-event-types-2"><?php esc_html_e( 'These specific event types', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select type(s)', 'wp-security-audit-log' ),
											$event_types_arr,
											'wsal-rep-event-types',
											$current_report,
											'event_types'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-event-types" id="wsal-rb-event-types-3"
												value="3" />
										<label for="wsal-rb-event-types-3"><?php esc_html_e( 'All event types except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select type(s)', 'wp-security-audit-log' ),
											$event_types_arr,
											'wsal-rep-event-types-exclude',
											$current_report,
											'event_types_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY POST ID -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Post title(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-ids" id="wsal-rb-post-ids-1" value="1"
												checked="checked" />
										<label for="wsal-rb-post-ids-1"><?php esc_html_e( 'All posts', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-ids" id="wsal-rb-post-ids-2" value="2" />
										<label for="wsal-rb-post-ids-2"><?php esc_html_e( 'These specific posts', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_post_selection_field( 'wsal-rep-post-ids', true, $current_report, 'post_ids' ); ?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-ids" id="wsal-rb-post-ids-3" value="3" />
										<label for="wsal-rb-post-ids-3"><?php esc_html_e( 'All post titles except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php $this->render_post_selection_field( 'wsal-rep-post-ids-exclude', true, $current_report, 'post_ids_excluded' ); ?>
									</td>
								</tr>
							</table>
						</div>
						<!--// BY POST TYPE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Post type(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-types" id="wsal-rb-post-types-1"
												value="1" checked="checked" />
										<label for="wsal-rb-post-types-1"><?php esc_html_e( 'All post types', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-types" id="wsal-rb-post-types-2"
												value="2" />
										<label for="wsal-rb-post-types-2"><?php esc_html_e( 'These specific post types', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select post type(s)', 'wp-security-audit-log' ),
											$post_types_arr,
											'wsal-rep-post-types',
											$current_report,
											'post_types'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-types" id="wsal-rb-post-types-3"
												value="3" />
										<label for="wsal-rb-post-types-3"><?php esc_html_e( 'All post types except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select post type(s)', 'wp-security-audit-log' ),
											$post_types_arr,
											'wsal-rep-post-types-exclude',
											$current_report,
											'post_types_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY POST STATUS -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Post status(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-statuses" id="wsal-rb-post-statuses-1"
												value="1" checked="checked" />
										<label for="wsal-rb-post-statuses-1"><?php esc_html_e( 'All post statuses', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-statuses" id="wsal-rb-post-statuses-2"
												value="2" />
										<label for="wsal-rb-post-statuses-2"><?php esc_html_e( 'These specific post statuses', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select post status(s)', 'wp-security-audit-log' ),
											$post_status_arr,
											'wsal-rep-post-statuses',
											$current_report,
											'post_statuses'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-post-statuses" id="wsal-rb-post-statuses-3"
												value="3" />
										<label for="wsal-rb-post-statuses-3"><?php esc_html_e( 'All post statuses except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select post status(s)', 'wp-security-audit-log' ),
											$post_status_arr,
											'wsal-rep-post-statuses-exclude',
											$current_report,
											'post_statuses_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>

						<!--// BY ALERT GROUPS/CODE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'By Event ID(s)', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl">

								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-alert-codes" id="wsal-rb-alert-codes-1"
												value="1" checked="checked" />
										<label for="wsal-rb-alert-codes-1"><?php esc_html_e( 'All event IDs', 'wp-security-audit-log' ); ?></label>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-alert-codes" id="wsal-rb-alert-codes-2"
												value="2" />
										<label for="wsal-rb-alert-codes-2"><?php esc_html_e( 'These specific event IDs', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										// This is a workaround to make sure the alert IDs are not printed if all events are supposed to be part of the report.
										if ( is_null( $current_report )
											|| ! property_exists( $current_report, 'alert_ids' )
											|| ! is_array( $current_report->alert_ids )
											|| count( $current_report->alert_ids ) === count( $event_ids )
										) {
											$this->render_generic_selection_field(
												esc_html__( 'Select event codes(s)', 'wp-security-audit-log' ),
												$event_ids_nested,
												'wsal-rep-alert-codes'
											);
										} else {
											$this->render_generic_selection_field(
												esc_html__( 'Select event codes(s)', 'wp-security-audit-log' ),
												$event_ids_nested,
												'wsal-rep-alert-codes',
												$current_report,
												'alert_ids'
											);
										}
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-alert-codes" id="wsal-rb-alert-codes-3"
												value="3" />
										<label for="wsal-rb-alert-codes-3"><?php esc_html_e( 'All event IDs except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
											$this->render_generic_selection_field(
												esc_html__( 'Select event codes(s)', 'wp-security-audit-log' ),
												$event_ids_nested,
												'wsal-rep-alert-codes-exclude',
												$current_report,
												'alert_ids_excluded'
											);
											?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-alert-codes" id="wsal-rb-alert-codes-4"
												value="4" />
										<label for="wsal-rb-alert-codes-4"><?php esc_html_e( 'These specific event groups', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select event group(s)', 'wp-security-audit-log' ),
											$alert_groups,
											'wsal-rep-alert-groups',
											$current_report,
											'alert_groups'
										);
										?>
									</td>
								</tr>
								<tr class="wsal-rep-clear">
									<td>
										<input type="radio" name="wsal-rb-alert-codes" id="wsal-rb-alert-codes-5"
												value="5" />
										<label for="wsal-rb-alert-codes-5"><?php esc_html_e( 'All event groups except these', 'wp-security-audit-log' ); ?></label>
									</td>
									<td>
										<?php
										$this->render_generic_selection_field(
											esc_html__( 'Select event group(s)', 'wp-security-audit-log' ),
											$alert_groups,
											'wsal-rep-alert-groups-exclude',
											$current_report,
											'alert_groups_excluded'
										);
										?>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<hr />

					<!-- SECTION #2 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 2: Select the date range', 'wp-security-audit-log' ); ?></h4>

					<div class="wsal-note"><?php esc_html_e( 'Note: Do not specify any dates if you are creating a scheduled report or if you want to generate a report from when you started the audit trail.', 'wp-security-audit-log' ); ?></div>

					<div class="wsal-rep-form-wrapper">
						<!--// BY DATE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl label-datepick"><?php esc_html_e( 'Start Date', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<p class="wsal-rep-clear">
									<input type="text" class="date-range" id="wsal-start-date" name="wsal-start-date"
											placeholder="<?php esc_html_e( 'Select start date', 'wp-security-audit-log' ); ?>" />
									<span class="description"> (<?php echo WSAL_Helpers_Assets::DATEPICKER_DATE_FORMAT; ?>)</span>
								</p>
							</div>
						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl label-datepick"><?php esc_html_e( 'End Date', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<p class="wsal-rep-clear">
									<input type="text" class="date-range" id="wsal-end-date" name="wsal-end-date"
											placeholder="<?php esc_html_e( 'Select end date', 'wp-security-audit-log' ); ?>" />
									<span class="description"> (<?php echo WSAL_Helpers_Assets::DATEPICKER_DATE_FORMAT; ?>)</span>
								</p>
							</div>
						</div>
						<script type="text/javascript">
							jQuery(document).ready(function ($) {
								wsal_CreateDatePicker($, $('#wsal-start-date'), null)
								wsal_CreateDatePicker($, $('#wsal-end-date'), null)
							})
						</script>
					</div>

					<hr />

					<!-- SECTION #3 -->
					<?php
					$selected_format = ! empty( $current_report ) ? $current_report->type : WSAL_Rep_DataFormat::get_default();
					$this->render_data_format_select( 'wsal-rb-report-type', $selected_format, 3, array( WSAL_Rep_DataFormat::PDF ) );
					?>

					<hr />

					<?php
					$custom_title   = ! empty( $current_report ) && property_exists( $current_report, 'custom_title' ) ? $current_report->custom_title : '';
					$custom_comment = ! empty( $current_report ) && property_exists( $current_report, 'comment' ) ? $current_report->comment : '';
					?>
					<!-- SECTION #4 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 4: Add a title and a comment to the report', 'wp-security-audit-log' ); ?></h4>
					<p><?php esc_html_e( 'Use the below placeholders to specify a title for the report and add a comment in the report\'s title page. Leave empty for default values. Please note the comment is limited to a maximum of 400 characters.', 'wp-security-audit-log' ); ?></p>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
								<label class="wsal-rep-label-fl wsal-rep-label-fl-offset" for="report-custom-title"><?php esc_html_e( 'Title', 'wp-security-audit-log' ); ?></label>
								<div class="wsal-rep-section-fl">
									<input type="text" id="report-custom-title" name="report-custom-title"
										class="regular-text" placeholder="<?php esc_html_e( 'Enter custom report title', 'wp-security-audit-log' ); ?>" value="<?php echo esc_attr( $custom_title ); ?>">
								</div>

						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl wsal-rep-label-fl-offset" for="report-custom-comment"><?php esc_html_e( 'Comment', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<textarea id="report-custom-comment" name="report-custom-comment" class="regular-text" rows="4"
										placeholder="<?php esc_html_e( 'Enter comment', 'wp-security-audit-log' ); ?>"><?php echo $custom_comment; ?></textarea>
							</div>
						</div>
					</div>

					<hr />

					<!-- SECTION #5 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 5: Generate the report now or configure it as periodic report', 'wp-security-audit-log' ); ?></h4>
					<p><?php esc_html_e( 'Click the button below to generate the report now, or specify an email address, a report name and select the periodic\'s report frequency from daily, weekly, monthly and quarterly to receive the periodic report automatically in your mailbox.', 'wp-security-audit-log' ); ?></p>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<input type="submit" name="wsal-reporting-submit" id="wsal-reporting-submit"
									class="button-primary"
									value="<?php esc_html_e( 'Generate Report Now', 'wp-security-audit-log' ); ?>">
						</div>
						<hr />
						<div class="wsal-rep-section">
							<span class="description"><?php esc_html_e( ' Use the buttons below to use the above criteria for a daily, weekly and monthly summary report which is sent automatically via email.', 'wp-security-audit-log' ); ?></span>
						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl wsal-rep-label-fl-offset"><?php esc_html_e( 'Email address(es)', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<input type="text" id="wsal-notif-email" style="min-width:270px;border: 1px solid #aaa;"
										name="wsal-notif-email"
										placeholder="<?php esc_html_e( 'Email', 'wp-security-audit-log' ); ?> *"
										value="<?php echo ! empty( $current_report ) ? esc_html( $current_report->email ) : false; ?>">
							</div>
						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl wsal-rep-label-fl-offset"><?php esc_html_e( 'Report Name', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<input type="text" id="wsal-notif-name" style="min-width:270px;border: 1px solid #aaa;"
										name="wsal-notif-name"
										placeholder="<?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?>"
										value="<?php echo ! empty( $current_report ) ? esc_html( $current_report->title ) : false; ?>">
								<p class="description"
										style="margin-left: 125px; margin-top: 5px;"><?php esc_html_e( 'Report names can only include numbers, letters and underscore and hyphens.', 'wp-security-audit-log' ); ?></p>
							</div>
						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl wsal-rep-label-fl-offset"><?php esc_html_e( 'Frequency', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<input type="submit" name="wsal-periodic" class="button-primary"
										value="<?php esc_html_e( 'Daily', 'wp-security-audit-log' ); ?>">
								<input type="submit" name="wsal-periodic" class="button-primary"
										value="<?php esc_html_e( 'Weekly', 'wp-security-audit-log' ); ?>">
								<input type="submit" name="wsal-periodic" class="button-primary"
										value="<?php esc_html_e( 'Monthly', 'wp-security-audit-log' ); ?>">
								<input type="submit" name="wsal-periodic" class="button-primary"
										value="<?php esc_html_e( 'Quarterly', 'wp-security-audit-log' ); ?>">
							</div>
						</div>
					</div>

					<?php wp_nonce_field( 'wsal_reporting_view_action', 'wsal_reporting_view_field' ); ?>
				</form>
			</div>

			<!-- SECTION Configured Periodic Reports -->
			<?php
			$periodic_reports = $wsal_common->get_periodic_reports();
			if ( ! empty( $periodic_reports ) ) :
				?>
				<div class="card" style="max-width: 100%;">
					<h3 class="wsal-reporting-subheading"><?php esc_html_e( 'Configured Periodic Reports', 'wp-security-audit-log' ); ?></h3>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<span class="description"><?php esc_html_e( 'Below is the list of configured periodic reports. Click on Modify to load the criteria and configure it above. To save the new criteria as a new report change the report name and save it. Do not change the report name to overwrite the existing periodic report.', 'wp-security-audit-log' ); ?></span>
							<br />
							<br />
							<span class="description"><?php esc_html_e( 'Note: Use the Send Now button to generate a report with data from the last 90 days if a quarterly report is configured, 30 days if monthly report is configured and 7 days if weekly report is configured.', 'wp-security-audit-log' ); ?></span>
						</div>
						<table class="wp-list-table widefat fixed striped">
							<thead>
							<tr>
								<th><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></th>
								<th><?php esc_html_e( 'Email address(es)', 'wp-security-audit-log' ); ?></th>
								<th><?php esc_html_e( 'Frequency', 'wp-security-audit-log' ); ?></th>
								<th><?php esc_html_e( 'Format', 'wp-security-audit-log' ); ?></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
							</thead>
							<tbody>
							<?php
							foreach ( $periodic_reports as $key => $report ) {
								$arr_emails = explode( ',', $report->email );
								?>
								<tr>
									<form action="<?php echo $this->get_url(); ?>" method="post">
										<input type="hidden" name="report-name" value="<?php echo $key; ?>">
										<td><?php echo $report->title; ?></td>
										<td>
											<?php
											foreach ( $arr_emails as $email ) {
												echo $email . '<br>';
											}
											?>
										</td>
										<td><?php echo $report->frequency; ?></td>
										<td><?php echo WSAL_Rep_DataFormat::get_label( $report->type ); ?></td>
										<td><input type="submit" name="report-send-now" class="button-secondary"
													value="Send Now"></td>
										<td><input type="submit" name="report-modify" class="button-secondary"
													value="Modify"></td>
										<td><input type="submit" name="delete-periodic-report" class="button-secondary"
													value="Delete"></td>
									</form>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!-- Tab Built-in Summary-->
		<div class="wsal-tab wrap" id="tab-summary">
			<div class="card" style="max-width: 100%;">
				<p style="clear:both; margin-top: 30px"></p>
				<form id="wsal-summary-form" method="post">
					<!-- SECTION #1 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 1: Choose Date Range', 'wp-security-audit-log' ); ?></h4>
					<div class="wsal-rep-form-wrapper">
						<!--// BY DATE -->
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl label-datepick"><?php esc_html_e( 'From', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<p class="wsal-rep-clear">
									<input type="text" class="date-range" id="wsal-from-date" name="wsal-from-date"
											placeholder="<?php esc_html_e( 'Select start date', 'wp-security-audit-log' ); ?>" />
									<span class="description"> (<?php echo WSAL_Helpers_Assets::DATEPICKER_DATE_FORMAT; ?>)</span>
								</p>
							</div>
						</div>
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl label-datepick"><?php esc_html_e( 'To', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<p class="wsal-rep-clear">
									<input type="text" class="date-range" id="wsal-to-date" name="wsal-to-date"
											placeholder="<?php esc_html_e( 'Select end date', 'wp-security-audit-log' ); ?>" />
									<span class="description"> (<?php echo WSAL_Helpers_Assets::DATEPICKER_DATE_FORMAT; ?>)</span>
								</p>
							</div>
						</div>
						<script type="text/javascript">
							jQuery(document).ready(function ($) {
								wsal_CreateDatePicker($, $('#wsal-from-date'), null)
								wsal_CreateDatePicker($, $('#wsal-to-date'), null)
							})
						</script>
					</div>

					<hr />

					<!-- SECTION #3 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 2: Select data sorting type', 'wp-security-audit-log' ); ?></h4>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'Time period', 'wp-security-audit-log' ); ?></label>
							<div class="wsal-rep-section-fl">
								<?php $this->render_grouping_period_select( 'wsal-grouping-period', 'day' ); ?>
							</div>
						</div>
					</div>

					<hr />

					<!-- SECTION #2 -->
					<h4 class="wsal-reporting-subheading"><?php esc_html_e( 'Step 3: Choose Criteria', 'wp-security-audit-log' ); ?></h4>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<label class="wsal-rep-label-fl"><?php esc_html_e( 'Report for', 'wp-security-audit-log' ); ?></label>
							<table class="wsal-rep-section-fl" style="max-width: 1050px;">
								<tr>
									<td colspan="2">
										<label for="criteria_10" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_10" checked="checked"
													value="<?php echo WSAL_Rep_Common::LOGIN_ALL; ?>" />
											<span class="name-criteria"><?php esc_html_e( 'Number of logins for all users', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="number-new-users" class="bulky">
											<input type="radio" name="wsal-criteria" id="number-new-users" 
													value="<?php echo WSAL_Rep_Common::NEW_USERS; ?>" />
											<span class="name-criteria"><?php esc_html_e( 'Number of newly registered users', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_1">
											<input type="radio" name="wsal-criteria" id="criteria_1"
													value="<?php echo WSAL_Rep_Common::LOGIN_BY_USER; ?>" />
											<span class="name-criteria"><?php esc_html_e( 'Number of logins for user(s)', 'wp-security-audit-log' ); ?></span>

										</label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-summary-field_1' ); ?>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_2">
											<input type="radio" name="wsal-criteria" id="criteria_2"
													value="<?php echo WSAL_Rep_Common::LOGIN_BY_ROLE; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of logins for users with the role(s) of', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-summary-field_2' ); ?>
									</td>
								</tr>
								<!-- User Profile changes start -->
								<tr>
									<td colspan="2">
										<label for="profile-changes-all" class="bulky">
											<input type="radio" name="wsal-criteria" id="profile-changes-all"
													value="<?php echo WSAL_Rep_Common::PROFILE_CHANGES_ALL; ?>" />
											<span class="name-criteria"><?php esc_html_e( 'Number of profile changes for all users', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<label for="profile-changes-users">
											<input type="radio" name="wsal-criteria" id="profile-changes-users"
													value="<?php echo WSAL_Rep_Common::PROFILE_CHANGES_BY_USER; ?>" />
											<span class="name-criteria"><?php esc_html_e( 'Number of profile changes for user(s)', 'wp-security-audit-log' ); ?></span>

										</label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-summary-field_' . WSAL_Rep_Common::PROFILE_CHANGES_BY_USER ); ?>
									</td>
								</tr>
								<tr>
									<td>
										<label for="profile-changes-roles">
											<input type="radio" name="wsal-criteria" id="profile-changes-roles"
													value="<?php echo WSAL_Rep_Common::PROFILE_CHANGES_BY_ROLE; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of profile changes for users with the role(s) of', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-summary-field_' . WSAL_Rep_Common::PROFILE_CHANGES_BY_ROLE ); ?>
									</td>
								</tr>
								<!-- User Profile changes end -->
								<tr>
									<td colspan="2">
										<label for="criteria_20" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_20"
													value="<?php echo WSAL_Rep_Common::VIEWS_ALL; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of views for all posts', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_3">
											<input type="radio" name="wsal-criteria" id="criteria_3"
													value="<?php echo WSAL_Rep_Common::VIEWS_BY_USER; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of views for user(s)', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-summary-field_3' ); ?>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_4">
											<input type="radio" name="wsal-criteria" id="criteria_4"
													value="<?php echo WSAL_Rep_Common::VIEWS_BY_ROLE; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of views for users with the role(s) of', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-summary-field_4' ); ?>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_25">
											<input type="radio" name="wsal-criteria" id="criteria_25"
													value="<?php echo WSAL_Rep_Common::VIEWS_BY_POST; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of views for a specific post', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_post_selection_field( 'wsal-summary-field_25', false ); ?>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="criteria_30" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_30"
													value="<?php echo WSAL_Rep_Common::PUBLISHED_ALL; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of published content for all users', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_5">
											<input type="radio" name="wsal-criteria" id="criteria_5"
													value="<?php echo WSAL_Rep_Common::PUBLISHED_BY_USER; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of published content for user(s)', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_user_selection_field( 'wsal-summary-field_5' ); ?>
									</td>
								</tr>
								<tr>
									<td>
										<label for="criteria_6">
											<input type="radio" name="wsal-criteria" id="criteria_6"
													value="<?php echo WSAL_Rep_Common::PUBLISHED_BY_ROLE; ?>">
											<span class="name-criteria"><?php esc_html_e( 'Number of published content for users with the role(s) of', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
									<td>
										<?php $this->render_role_selection_field( 'wsal-summary-field_6' ); ?>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="criteria_60" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_60"
													value="<?php echo WSAL_Rep_Common::PASSWORD_CHANGES; ?>">
											<span><?php esc_html_e( 'User password changes and password resets', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="criteria_7" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_7"
													value="<?php echo WSAL_Rep_Common::DIFFERENT_IP; ?>">
											<span><?php esc_html_e( 'Different IP addresses for Usernames', 'wp-security-audit-log' ); ?></span>
										</label>

										<div class="sub-options">
											<label for="only_login" class="bulky">
												<input type="checkbox" name="only_login" id="only_login"
														style="margin: 2px;">
												<span><?php esc_html_e( 'List only IP addresses used during login', 'wp-security-audit-log' ); ?></span>
											</label>
											<p class="description"><?php esc_html_e( 'If the above option is enabled the report will only include the IP addresses from where the user logged in. If it is disabled it will list all the IP addresses from where the plugin recorded activity originating from the user.', 'wp-security-audit-log' ); ?></p>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="criteria_8" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_8"
													value="<?php echo WSAL_Rep_Common::ALL_IPS; ?>">
											<span><?php esc_html_e( 'List of IP addresses that accessed the website', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<label for="criteria_9" class="bulky">
											<input type="radio" name="wsal-criteria" id="criteria_9"
													value="<?php echo WSAL_Rep_Common::ALL_USERS; ?>">
											<span><?php esc_html_e( 'List of users who accessed the website', 'wp-security-audit-log' ); ?></span>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<hr />

					<!-- SECTION #4 -->
					<?php $this->render_data_format_select( 'wsal-summary-type', WSAL_Rep_DataFormat::get_default(), 4, array( WSAL_Rep_DataFormat::JSON ) ); ?>

					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<div class="wsal-rep-section-fl">
								<?php
								if ( WP_Helper::check_for_cron_job( WSAL_Rep_Views_Main::REPORT_CRON_NAME ) ) {
									esc_html_e( 'Your previous report is still processing', 'wp-security-audit-log' );
								} else {
									?>
								<input type="submit" id="wsal-submit-now" name="wsal-statistics-submit"
										value="Generate Report" class="button-primary">
										<?php } ?>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<?php if ( ! empty( $saved_reports ) ) : ?>
			<div class="wsal-tab wrap" id="tab-saved-reports">

				<p style="clear:both; margin-top: 30px"></p>

				<!-- SECTION Previously generated reports -->
				<div class="card" style="max-width: 100%;">
					<h3 class="wsal-reporting-subheading"><?php esc_html_e( 'Generated reports', 'wp-security-audit-log' ); ?></h3>
					<div class="wsal-rep-form-wrapper">
						<div class="wsal-rep-section">
							<span class="description"><?php esc_html_e( 'Below is the list of reports previously generated and are now saved in the reports directory on your website. By default the plugin automatically deletes saved reports that are older than 30 days. You can disable or change this from the Reports settings.', 'wp-security-audit-log' ); ?></span>
						</div>
						<table class="wp-list-table widefat fixed striped">
							<thead>
							<tr>
								<th width="45%"><?php esc_html_e( 'Name', 'wp-security-audit-log' ); ?></th>
								<th width="20%"><?php esc_html_e( 'Date', 'wp-security-audit-log' ); ?></th>
								<th><?php esc_html_e( 'Format', 'wp-security-audit-log' ); ?></th>
								<th></th>
								<th></th>
							</tr>
							</thead>
							<tbody>
							<?php
							/** @var WSAL_Rep_Report $report */
							foreach ( $saved_reports as $report ) :
								?>
								<tr>
									<td><?php echo $report->get_filename(); ?></td>
									<td><?php echo $datetime_formatter->get_formatted_date_time( $report->get_timestamp() ); ?></td>
									<td><?php echo $report->get_format(); ?></td>
									<td>
										<a href="<?php echo $report->get_download_url(); ?>" target="_blank"
												class="button-secondary"><?php esc_html_e( 'Download', 'wp-security-audit-log' ); ?></a>
									</td>
									<td>
										<form action="<?php echo $this->get_url(); ?>" method="post">
											<input type="hidden" name="report-name"
													value="<?php echo $report->get_filename(); ?>">
											<input type="submit" name="delete-saved-report" class="button-secondary"
													value="<?php esc_html_e( 'Delete', 'wp-security-audit-log' ); ?>">
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		foreach ( $nav_tabs as $tab_key => $tab_title ) {
			if ( in_array( $tab_key, $fixed_tabs_slugs, true ) ) {
				continue;
			}

			echo '<div class="wsal-tab wrap" id="tab-' . $tab_key . '">';
			echo '<div class="card" style="max-width: 100%;">';
			do_action( 'wsal_reports_render_tab_' . $tab_key );
			echo '</div>';
		}
		?>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('#wsal-rep-form').on('submit', function () {
			//#! Sites
			if ($('#wsal-rb-sites-2').is(':checked') && $('#wsal-rep-sites').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one site', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-sites-3').is(':checked') && $('#wsal-rep-sites-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one site', 'wp-security-audit-log' ); ?>")
				return false
			}

			//#! Users
			if ($('#wsal-rb-users-2').is(':checked') && $('#wsal-rep-users').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one user', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-users-3').is(':checked') && $('#wsal-rep-users-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one user', 'wp-security-audit-log' ); ?>")
				return false
			}

			//#! Roles
			if ($('#wsal-rb-roles-2').is(':checked') && $('#wsal-rep-roles').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one role', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-roles-3').is(':checked') && $('#wsal-rep-roles-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one role', 'wp-security-audit-log' ); ?>")
				return false
			}

			//#! IP addresses
			if ($('#wsal-rb-ip-addresses-2').is(':checked') && $('#wsal-rep-ip-addresses').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one IP address', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-ip-addresses-3').is(':checked') && $('#wsal-rep-ip-addresses-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one IP address', 'wp-security-audit-log' ); ?>")
				return false
			}

			//#! Event Objects
			if ($('#wsal-rb-event-objects-2').is(':checked') && $('#wsal-rep-event-objects').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one object', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-event-objects-3').is(':checked') && $('#wsal-rep-event-objects-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one object', 'wp-security-audit-log' ); ?>")
				return false
			}

			//#! Event types
			if ($('#wsal-rb-event-types-2').is(':checked') && $('#wsal-rep-event-types').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one event type', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ($('#wsal-rb-event-types-3').is(':checked') && $('#wsal-rep-event-types-exclude').val().length === 0) {
				alert("<?php esc_html_e( 'Please specify at least one event type', 'wp-security-audit-log' ); ?>")
				return false
			}

			if ( $('#report-custom-title').length ) {
				var title_length = $('#report-custom-title').val().length;
				if ( title_length > 120 ) {
					alert("<?php printf(
						/* translators: number of allowed characters. */
						esc_html__( 'Custom title cannot be longer than %d characters.', 'wp-security-audit-log' ),
						120
					); ?>");
					return false;
				}
			}

			if ( $('#report-custom-comment').length ) {
				var comment_length = $('#report-custom-comment').val().length;
				if ( comment_length > 400 ) {
					alert("<?php printf(
						/* translators: number of allowed characters. */
						esc_html__( 'Comment cannot be longer than %d characters', 'wp-security-audit-log' ),
						400
					); ?>");
					return false;
				}
			}

			return true
		})

		$('#wsal-summary-form').on('submit', function () {
			var sel = $('input[name=\'wsal-criteria\']:checked').val()
			var field = $('input[name=\'wsal-summary-field_' + sel + '\']').val()
			// field required
			if (field != '') {
				return true
			} else {
				alert('Add User(s)/Role(s) for the report.')
				return false
			}
		})
	})
</script>
