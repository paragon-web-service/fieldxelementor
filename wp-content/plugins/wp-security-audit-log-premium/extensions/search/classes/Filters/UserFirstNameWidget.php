<?php
/**
 * User First Name Widget
 *
 * User first name widget class file.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserFirstNameWidget' ) ) :

	/**
	 * WSAL_AS_Filters_UserFirstNameWidget.
	 *
	 * Class: User First Name Widget.
	 */
	class WSAL_AS_Filters_UserFirstNameWidget extends WSAL_AS_Filters_AbstractWidget {

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
					var firstname_input = jQuery( 'input.<?php echo esc_attr( $this->get_safe_name() ); ?>' );
					var firstname = firstname_input.val();
					if ( firstname.length == 0 ) return;
					var firstname_filter_value = firstname_input.attr( 'data-prefix' ) + ':' + firstname;
					window.WsalAs.AddFilter( firstname_filter_value );
				} );
			</script>
			<?php
		}

	}

endif;
