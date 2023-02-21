<?php
/**
 * IP Widget
 *
 * IP widget class file.
 *
 * @package wsal
 * @subpackage search
 * @since 3.2.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_IpWidget' ) ) {

	/**
	 * WSAL_AS_Filters_IpWidget.
	 *
	 * Class: IP Widget.
	 */
	class WSAL_AS_Filters_IpWidget extends WSAL_AS_Filters_AutoCompleteWidget {

		/**
		 * Render widget field.
		 */
		protected function render_field() {
			?>
			<div class="wsal-widget-container">
				<input type="text" autocomplete="off"
					class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
					id="<?php echo esc_attr( $this->id ); ?>"
					name="<?php echo esc_attr( $this->id ); ?>"
					data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
					data-filter="<?php echo esc_attr( $this->filter->get_safe_name() ); ?>"
					placeholder="<?php esc_html_e( '192.168.128.255', 'wp-security-audit-log' ); ?>"
				/>
				<button id="wsal-add-ip-filter" class="button wsal-button wsal-filter-add-button"><?php esc_html_e( 'Add this filter', 'wp-security-audit-log' ); ?></button>
			</div>
			<?php
		}
	}
}
