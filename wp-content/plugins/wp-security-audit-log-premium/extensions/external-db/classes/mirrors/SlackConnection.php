<?php
/**
 * Class WSAL_Ext_Mirrors_SlackConnection.
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

/**
 * Slack connection class.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.0
 */
class WSAL_Ext_Mirrors_SlackConnection extends \WSAL_Ext_AbstractConnection {

	/**
	 * {@inheritDoc}
	 */
	public static function get_type() {
		return 'slack';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name() {
		return esc_html__( 'Slack', 'wp-security-audit-log' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_config_definition() {
		return array(
			'desc'   => esc_html__( 'General mirror connection description.', 'wp-security-audit-log' ),
			'fields' => array(
				'webhook' => array(
					'label'      => esc_html__( 'Webhook URL', 'wp-security-audit-log' ),
					'type'       => 'text',
					'desc'       => sprintf(
					/* translators: hyperlink to the Slack webhook documentation page */
						esc_html__( 'If you are not familiar with incoming WebHooks on Slack, please refer to %s.', 'wp-security-audit-log' ),
						sprintf(
							'<a href="%1$s" rel="noopener noreferrer" target="_blank">%2$s</a>',
							esc_url( 'https://api.slack.com/messaging/webhooks' ),
							esc_html__( 'Slack webhooks documentation', 'wp-security-audit-log' )
						)
					),
					'validation' => 'slackWebhook',
					'required'   => true,
					'error'      => esc_html__( 'Invalid Webhook URL', 'wp-security-audit-log' ),
				),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected static function add_extra_requirements() {
		if ( ! extension_loaded( 'curl' ) ) { 
			self::$error_message = \esc_html__( 'PHP extension curl is required', 'wp-security-audit-log' );
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_monolog_handler() {
		$webhook = array_key_exists( 'webhook_url', $this->connection ) ? $this->connection['webhook_url'] : $this->connection['webhook'];

		return new \WSAL_Vendor\Monolog\Handler\SlackWebhookHandler(
			$webhook,
			null,
			null,
			true,
			null,
			false,
			true,
			\WSAL_Vendor\Monolog\Logger::DEBUG
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function pre_process_metadata( $metadata, $mirror ) {
		unset( $metadata['Severity'] );

		if ( is_array( $mirror ) && array_key_exists( 'source', $mirror ) ) {
			// Prepend the mirror identifier (the label is not translated on purpose).
			$metadata = array(
				'Identifier' => $mirror['source'],
			) + $metadata;
		}

		return $metadata;
	}
}
