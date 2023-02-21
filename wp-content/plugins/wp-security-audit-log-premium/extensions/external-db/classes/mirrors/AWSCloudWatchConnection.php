<?php
/**
 * Class WSAL_Ext_Mirrors_AWSCloudWatchConnection.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */

namespace WSAL\Extensions\ExternalDB\Mirrors;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \WSAL\Helpers\PHP_Helper;

/**
 * AWS CloudWatch connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
class WSAL_Ext_Mirrors_AWSCloudWatchConnection extends \WSAL_Ext_AbstractConnection {

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return 'aws_cloudwatch';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return esc_html__( 'AWS CloudWatch', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_config_definition() {

		$aws_definition = array(
			'desc'   => esc_html__( 'General mirror connection description.', 'wp-security-audit-log' ),
			'fields' => array(
				'region'    => array(
					'label'   => esc_html__( 'Region', 'wp-security-audit-log' ),
					'type'    => 'select',
					'options' => array(
						'us-east-1'      => 'US East (N. Virginia)',
						'us-east-2'      => 'US East (Ohio)',
						'us-west-1'      => 'US West (N. California)',
						'us-west-2'      => 'US West (Oregon)',
						'af-south-1'     => 'Africa (Cape Town)',
						'ap-east-1'      => 'Asia Pacific (Hong Kong)',
						'ap-south-1'     => 'Asia Pacific (Mumbai)',
						'ap-northeast-3' => 'Asia Pacific (Osaka)',
						'ap-northeast-2' => 'Asia Pacific (Seoul)',
						'ap-southeast-1' => 'Asia Pacific (Singapore)',
						'ap-southeast-2' => 'Asia Pacific (Sydney)',
						'ap-northeast-1' => 'Asia Pacific (Tokyo)',
						'ca-central-1'   => 'Canada (Central)',
						'eu-central-1'   => 'Europe (Frankfurt)',
						'eu-west-1'      => 'Europe (Ireland)',
						'eu-west-2'      => 'Europe (London)',
						'eu-south-1'     => 'Europe (Milan)',
						'eu-west-3'      => 'Europe (Paris)',
						'eu-north-1'     => 'Europe (Stockholm)',
						'me-south-1'     => 'Middle East (Bahrain)',
						'sa-east-1'      => 'South America (São Paulo)',
					),
				),
				'key'       => array(
					'label'    => esc_html__( 'AWS Key', 'wp-security-audit-log' ),
					'type'     => 'text',
					'required' => true,
				),
				'secret'    => array(
					'label'    => esc_html__( 'AWS Secret', 'wp-security-audit-log' ),
					'type'     => 'text',
					'required' => true,
				),
				'token'     => array(
					'label' => esc_html__( 'AWS Session Token', 'wp-security-audit-log' ),
					'type'  => 'text',
					'desc'  => esc_html__( 'This is optional.', 'wp-security-audit-log' ),
				),
				'group'     => array(
					'label'      => esc_html__( 'Log group name', 'wp-security-audit-log' ),
					'type'       => 'text',
					'validation' => 'cloudWatchGroupName',
					'error'      => sprintf(
						esc_html__( 'Invalid AWS group name. It must satisfy regular expression pattern: %s', 'wp-security-audit-log' ), // phpcs:ignore
						'[\.\-_/#A-Za-z0-9]+'
					),
					'desc'       => sprintf(
						esc_html__( 'If you do not specify a group name, one will be created using the default group name "%s".', 'wp-security-audit-log' ), // phpcs:ignore
						'WP_Activity_Log'
					),
				),
				'stream'    => array(
					'label' => esc_html__( 'Log stream name', 'wp-security-audit-log' ),
					'type'  => 'text',
					'desc'  => esc_html__( 'If you do not specify a stream name, one will be created using the site name as stream name.', 'wp-security-audit-log' ),
				),
				'retention' => array(
					'label'   => esc_html__( 'Retention', 'wp-security-audit-log' ),
					'type'    => 'select',
					'options' => array(
						'0'    => 'indefinite',
						'1'    => '1',
						'3'    => '3',
						'5'    => '5',
						'7'    => '7',
						'14'   => '14',
						'30'   => '30',
						'60'   => '60',
						'90'   => '90',
						'120'  => '120',
						'150'  => '150',
						'180'  => '180',
						'365'  => '365',
						'400'  => '400',
						'545'  => '545',
						'731'  => '731',
						'1827' => '1827',
						'3653' => '3653',
					),
					'desc'    => esc_html__( 'Days to keep logs.', 'wp-security-audit-log' ),
				),
			),
		);

		if ( \WpSecurityAuditLog::is_multisite() ) {
			$aws_definition['fields']['stream'] = array(
				'label'    => esc_html__( 'Stream', 'wp-security-audit-log' ),
				'type'     => 'radio',
				'required' => true,
				'options'  => array(
					'single-stream'    => array(
						'label'     => esc_html__( 'Mirror the activity logs of all sub sites on the network to one Stream', 'wp-security-audit-log' ),
						'subfields' => array(
							'stream' => array(
								'label' => esc_html__( 'Log stream name', 'wp-security-audit-log' ),
								'type'  => 'text',
								'desc'  => esc_html__( 'If you do not specify a stream name, one will be created using the site name as stream name.', 'wp-security-audit-log' ),
							),
						),
					),
					'multiple-streams' => array(
						'label'     => esc_html__( 'Create a Stream for every individual sub site on the network. The Stream name should be the:', 'wp-security-audit-log' ),
						'subfields' => array(
							'stream-setting' => array(
								'label'    => false,
								'type'     => 'radio',
								'required' => false,
								'options'  => array(
									'sitename' => array(
										'label' => esc_html__( 'Sitename', 'wp-security-audit-log' ),
									),
									'fqdn'     => array(
										'label' => esc_html__( 'FQDN', 'wp-security-audit-log' ),
									),
								),
							),
						),
					),
				),
			);
		}

		return $aws_definition;
	}

	/**
	 * Displays a notice about missing AWS SDK library if needed.
	 *
	 * @param WpSecurityAuditLog $plugin Plugin.
	 *
	 * @since 4.3.2
	 */
	public static function display_no_aws_sdk_notice( $plugin ) {
		$should_notice_be_displayed = \WSAL\Helpers\Settings_Helper::get_boolean_option_value( 'show-aws-sdk-config-nudge-4_3_2', false );
		if ( ! $should_notice_be_displayed ) {
			return;
		}

		echo '<div class="notice notice-error is-dismissible" style="padding-bottom: .5em;" data-dismiss-action="wsal_dismiss_missing_aws_sdk_nudge" data-nonce="' . wp_create_nonce( 'dismiss_missing_aws_sdk_nudge' ) . '">'; // phpcs:ignore
		echo '<p>' . esc_html__( 'You have setup a mirroring connection to AWS CloudWatch in the WP Activity Log plugin. In this version we\'ve done some changes and you need to add the following lines to the wp-config.php file to enable the AWS library.', 'wp-security-audit-log' ) . '</p>';
		echo '<code>define( \'WSAL_LOAD_AWS_SDK\', \'true\' );</code>';
		echo '</div>';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.3.2
	 */
	protected static function add_extra_requirements() {
		if ( ! class_exists( '\Aws\CloudWatchLogs\CloudWatchLogsClient' ) ) {

			self::$error_message  = '<p>' . esc_html__( 'The AWS library is disabled. Please enable this library by adding the following to the wp-config.php file. Press continue when you are ready or cancel to stop the process.', 'wp-security-audit-log' ) . '</p>';
			self::$error_message .= '<code>define( \'WSAL_LOAD_AWS_SDK\', \'true\' );</code>';

			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_monolog_handler() {
		global $mirror_tags;

		if ( ! class_exists( '\Aws\CloudWatchLogs\CloudWatchLogsClient' ) ) {
			throw new \Exception( 'AWS SDK library is missing' );
		}

		$region     = array_key_exists( 'region', $this->connection ) ? $this->connection['region'] : 'eu-west-1';
		$aws_key    = array_key_exists( 'key', $this->connection ) ? $this->connection['key'] : '';
		$aws_secret = array_key_exists( 'secret', $this->connection ) ? $this->connection['secret'] : '';

		if ( empty( $aws_key ) || empty( $aws_secret ) ) {
			throw new \Exception( 'AWS key and secret missing.' );
		}

		$sdk_params = array(
			'region'      => $region,
			'version'     => 'latest',
			'credentials' => array(
				'key'    => $aws_key,
				'secret' => $aws_secret,
			),
		);

		// Token is optional.
		if ( array_key_exists( 'token', $this->connection ) && ! empty( $this->connection['token'] ) ) {
			$sdk_params['credentials']['token'] = $this->connection['token'];
		}

		// Instantiate AWS SDK CloudWatch Logs Client.
		$client = new \Aws\CloudWatchLogs\CloudWatchLogsClient( $sdk_params );

		// Log group name, will be created if none.
		$group_name = array_key_exists( 'group', $this->connection ) && ! empty( $this->connection['group'] ) ? $this->connection['group'] : 'WP_Activity_Log';

		if ( \WpSecurityAuditLog::is_multisite() ) {
			if ( 'single-stream' === $this->connection['stream'] ) {
				// Log stream name, will be created if none.
				$stream_name = array_key_exists( 'single-stream-stream', $this->connection ) && ! empty( $this->connection['single-stream-stream'] ) ? $this->connection['single-stream-stream'] : get_blog_option( 0, 'blogname' );
			} elseif ( 'multiple-streams' === $this->connection['stream'] ) {
				if ( 'sitename' === $this->connection['stream-subfield'] ) {
					$stream_name = get_bloginfo( 'name' );
				} else {
					$stream_name = preg_replace( '#^[^:/.]*[:/]+#i', '', preg_replace( '{/$}', '', urldecode( get_bloginfo( 'url' ) ) ) );
				}
			}
		} else {
			// Log stream name, will be created if none.
			$stream_name = array_key_exists( 'stream', $this->connection ) && ! empty( $this->connection['stream'] ) ? $this->connection['stream'] : get_bloginfo( 'name' );
		}

		// days to keep logs, 14 by default. Set to `null` to allow indefinite retention.
		$retention_days = 14;
		if ( array_key_exists( 'retention', $this->connection ) && strlen( $this->connection['retention'] ) > 0 ) {
			$retention_days = intval( $this->connection['retention'] );
			if ( $retention_days <= 0 ) {
				$retention_days = null;
			}
		}

		if ( empty( $mirror_tags ) ) {
			$tags = array();
		} else {
			$tags = PHP_Helper::string_to_array( $mirror_tags );
			$tags = array_combine( $tags, $tags );
		}

		// Instantiate handler (tags are optional).
		$handler = new \WSAL_Vendor\Maxbanton\Cwh\Handler\CloudWatch( $client, $group_name, $stream_name, $retention_days, 1, $tags );

		// Set the JsonFormatter to be able to access your log messages in a structured way.
		$handler->setFormatter( new \WSAL_Vendor\Monolog\Formatter\JsonFormatter() );

		return $handler;
	}
}
