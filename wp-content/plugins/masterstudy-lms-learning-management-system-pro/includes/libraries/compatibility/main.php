<?php

new STM_LMS_PRO_Compatibility();

class STM_LMS_PRO_Compatibility {
	private static $free = '2.5.0';

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'init' ) );
	}

	public function check_version() {
		$has_pro = defined( 'MS_LMS_FILE' );

		if ( ! $has_pro ) {
			return false;
		}

		$plugin_data = get_plugin_data( MS_LMS_FILE );
		$version     = $plugin_data['Version'];

		return version_compare( self::$free, $version ) > 0;
	}

	public function init() {
		if ( $this->check_version() ) :
			wp_enqueue_style( 'stm-lms-pro-notice', STM_LMS_PRO_URL . 'assets/css/pro_notice.css', array(), STM_LMS_PRO_VERSION );
			?>

			<div class="notice notice-lms notice-lms-go-to-pages notice-lms-compatibility">

				<div class="notice-lms-go-to-pages-icon">
					<i class="fa fa-exclamation"></i>
				</div>

				<div class="notice-lms-go-to-pages-content">

					<p>
						<strong>
							<?php esc_html_e( 'Please update MasterStudy LMS!', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</strong>
					</p>

					<p>
						<?php esc_html_e( 'The current version of MasterStudy LMS Pro is not compatible with old versions of the MasterStudy LMS plugin, some functionality may not work correctly or may stop working completely.', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</p>

				</div>

			</div>

			<?php
		endif;
	}

}
