<?php
/**
 * Class: Helper for the mirroring operations.
 *
 * Helper class used for extraction / loading classes.
 *
 * @package wsal
 */

declare(strict_types=1);

namespace WSAL\Helpers;

use WSAL_Ext_Common;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Helpers\Mirroring_Helper' ) ) {
	/**
	 * Responsible for the proper class loading
	 */
	class Mirroring_Helper {

		/**
		 * Inits the mirroring actions
		 *
		 * @return void
		 *
		 * @since 4.4.3.2
		 */
		public static function init() {

			add_action( 'wsal_ext_db_header', array( __CLASS__, 'enqueue_styles' ) );
			add_action( 'wsal_ext_db_footer', array( __CLASS__, 'enqueue_scripts' ) );
		}

		/**
		 * Checks given string for valid tags and returns string (comma separated) with all valid tags
		 *
		 * @param string $tags - String which contains possible tags.
		 *
		 * @return string
		 *
		 * @since 4.4.2.1
		 */
		public static function clean_and_prepare_tags( string $tags ): string {

			$tags = PHP_Helper::string_to_array( $tags );

			if ( ! is_array( $tags ) || empty( $tags ) ) {
				return '';
			}

			foreach ( $tags as $key => &$tag ) {
				if ( ! Validator::validate_mirror_tag( $tag ) ) {
					unset( $tags[ $key ] );
				}
			}
			unset( $tag );

			if ( ! empty( $tags ) ) {
				return \implode( ',', $tags );
			}

			return '';
		}

		/**
		 * Enqueue tab scripts.
		 *
		 * @return void
		 *
		 * @since 4.4.3.2
		 */
		public static function enqueue_styles() {
			wp_enqueue_style( 'wp-jquery-ui-dialog' );

			wp_enqueue_style(
				'wsal-rep-select2-css',
				trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2.css',
				array(),
				WSAL_VERSION
			);

			wp_enqueue_style(
				'wsal-rep-select2-bootstrap-css',
				trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2-bootstrap.css',
				array(),
				WSAL_VERSION
			);

			wp_enqueue_style(
				'wsal-connections-css',
				WSAL_Ext_Common::get_extension_base_url() . 'css/wsal-ext-wizard.css',
				array(),
				WSAL_VERSION
			);
		}

		/**
		 * Enqueue tab scripts.
		 *
		 * @return void
		 *
		 * @since 4.4.3.2
		 */
		public static function enqueue_scripts() {
			wp_enqueue_script( 'jquery-ui-dialog' );

			wp_enqueue_script(
				'wsal-ext-select2-js',
				trailingslashit( WSAL_BASE_URL ) . 'js/select2/select2.min.js',
				array( 'jquery' ),
				WSAL_VERSION,
				true
			);

			// Connections script file.
			wp_register_script(
				'wsal-connections-js',
				WSAL_Ext_Common::get_extension_base_url() . 'js/wsal-ext-wizard.js',
				array( 'jquery', 'jquery-ui-dialog', 'wsal-ext-select2-js' ),
				WSAL_VERSION,
				true
			);

			// @codingStandardsIgnoreStart
			$mirror = isset( $_GET['mirror'] ) ? sanitize_text_field( wp_unslash( $_GET['mirror'] ) ) : false;
			// @codingStandardsIgnoreEnd

			$script_data = array(
				'wpNonce'               => wp_create_nonce( 'wsal-create-connections' ),
				'title'                 => __( 'Connections Wizard', 'wp-security-audit-log' ),
				'mirrorTitle'           => __( 'Mirroring Wizard', 'wp-security-audit-log' ),
				'connTest'              => __( 'Testing...', 'wp-security-audit-log' ),
				'deleting'              => __( 'Deleting...', 'wp-security-audit-log' ),
				'enabling'              => __( 'Enabling...', 'wp-security-audit-log' ),
				'disabling'             => __( 'Disabling...', 'wp-security-audit-log' ),
				'connFailed'            => __( 'Connection failed!', 'wp-security-audit-log' ),
				'connSuccess'           => __( 'Connected', 'wp-security-audit-log' ),
				'mirrorInProgress'      => __( 'Running...', 'wp-security-audit-log' ),
				'mirrorComplete'        => __( 'Mirror Complete!', 'wp-security-audit-log' ),
				'mirrorFailed'          => __( 'Failed!', 'wp-security-audit-log' ),
				'confirm'               => __( 'Are you sure that you want to delete this connection?', 'wp-security-audit-log' ),
				'confirmDelMirror'      => __( 'Are you sure that you want to delete this mirror?', 'wp-security-audit-log' ),
				'eventsPlaceholder'     => __( 'Select Event Code(s)', 'wp-security-audit-log' ),
				'severitiesPlaceholder' => __( 'Select Severity Levels', 'wp-security-audit-log' ),
				'testContinue'          => __( 'Configure and create connection', 'wp-security-audit-log' ),
				'buttonNext'            => __( 'Next', 'wp-security-audit-log' ),
				'ajaxURL'               => admin_url( 'admin-ajax.php' ),
				'connection'            => false,
				'mirror'                => $mirror,
				'urlBasePrefix'         => WSAL_Ext_Common::get_url_for_db(),
			);
			wp_localize_script( 'wsal-connections-js', 'scriptData', $script_data );
			wp_enqueue_script( 'wsal-connections-js' );
		}
	}
}
