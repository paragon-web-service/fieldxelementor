<?php
/**
 * User Last Name Widget
 *
 * User last name widget class file.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserLastNameWidget' ) ) :

	/**
	 * WSAL_AS_Filters_UserLastNameWidget.
	 *
	 * Class: User Last Name Widget.
	 */
	class WSAL_AS_Filters_UserLastNameWidget extends WSAL_AS_Filters_AbstractWidget {

		/**
		 * Method: Function to render field.
		 */
		protected function render_field() {
			?>
			<div class="wsal-widget-container">
				<input type="text"
					class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
					id="<?php echo esc_attr( $this->id ); ?>"
					data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
					placeholder="<?php esc_html_e( 'Enter users first name to filter', 'wp-security-audit-log' ); ?>"
				/>
				<button id="<?php echo esc_attr( "wsal-add-$this->prefix-filter" ); ?>" class="button wsal-button wsal-filter-add-button"><?php esc_html_e( 'Add this filter', 'wp-security-audit-log' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Method: Render JS in footer regarding this widget.
		 */
		public function static_footer() {
			?>
			<script type="text/javascript">
				jQuery( '<?php echo esc_attr( "#wsal-add-$this->prefix-filter" ); ?>' ).click( function( event ) {
					event.preventDefault();
					var lastname_input = jQuery( 'input.<?php echo esc_attr( $this->get_safe_name() ); ?>' );
					var lastname = lastname_input.val();
					if ( lastname.length == 0 ) return;
					var lastname_filter_value = lastname_input.attr( 'data-prefix' ) + ':' + lastname;
					window.WsalAs.AddFilter( lastname_filter_value );
				} );
			</script>
			<?php
		}

	}

endif;
