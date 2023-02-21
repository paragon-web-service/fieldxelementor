<?php
/**
 * Post ID Widget
 *
 * Post ID widget class file.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostIDWidget' ) ) :

	/**
	 * WSAL_AS_Filters_PostIDWidget.
	 *
	 * Class: Post ID Widget.
	 */
	class WSAL_AS_Filters_PostIDWidget extends WSAL_AS_Filters_AbstractWidget {

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
					placeholder="<?php esc_html_e( 'Enter post ID to filter', 'wp-security-audit-log' ); ?>"
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
					var post_id_input = jQuery( 'input.<?php echo esc_attr( $this->get_safe_name() ); ?>' );
					var post_id = post_id_input.val();
					if ( post_id.length == 0 ) return;
					var post_id_filter_value = post_id_input.attr( 'data-prefix' ) + ':' + post_id;
					window.WsalAs.AddFilter( post_id_filter_value );
				} );
			</script>
			<?php
		}

	}

endif;
