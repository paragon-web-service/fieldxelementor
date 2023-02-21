<?php
/**
 * Class: Date Widget
 *
 * Date widget for search extension.
 *
 * @since 1.0.0
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_AS_Filters_DateWidget
 *
 * @package wsal
 * @subpackage search
 */
class WSAL_AS_Filters_DateWidget extends WSAL_AS_Filters_AbstractWidget {

	/**
	 * Method: Function to render field.
	 */
	protected function render_field() {
		$date_format = WSAL_Helpers_Assets::DATEPICKER_DATE_FORMAT;
		?>
		<div class="wsal-widget-container dashicons-left-input">
			<span class="dashicons dashicons-calendar-alt"></span>
			<input type="text"
				class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
				id="<?php echo esc_attr( $this->id ); ?>"
				placeholder="<?php echo esc_attr( $date_format ); ?>"
				data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
			/>
			<button type="button" id="<?php echo esc_attr( "wsal-add-$this->prefix-filter" ); ?>" class="button wsal-button wsal-filter-add-button"><?php esc_html_e( 'Add this filter', 'wp-security-audit-log' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Method: Render JS in footer regarding this widget.
	 */
	public function static_footer() {
		?>
		<script type="text/javascript">
			// manually defining these as this does not loop 3 times like reder does.
			jQuery( '#wsal-add-from-filter' ).click( function( event ) {
				event.preventDefault();
				var event_id_input = jQuery( '#wsal_as_widget_from' );
				var event_id = event_id_input.val();
				if ( event_id.length == 0 ) return;
				var event_id_filter_value = event_id_input.attr( 'data-prefix' ) + ':' + event_id;
				window.WsalAs.AddFilter( event_id_filter_value );
				event_id_input.val( '' );
			} );
			jQuery( '#wsal-add-to-filter' ).click( function( event ) {
				event.preventDefault();
				var event_id_input = jQuery( '#wsal_as_widget_to' );
				var event_id = event_id_input.val();
				if ( event_id.length == 0 ) return;
				var event_id_filter_value = event_id_input.attr( 'data-prefix' ) + ':' + event_id;
				window.WsalAs.AddFilter( event_id_filter_value );
				event_id_input.val( '' );
			} );
			jQuery( '#wsal-add-on-filter' ).click( function( event ) {
				event.preventDefault();
				var event_id_input = jQuery( '#wsal_as_widget_on' );
				var event_id = event_id_input.val();
				if ( event_id.length == 0 ) return;
				var event_id_filter_value = event_id_input.attr( 'data-prefix' ) + ':' + event_id;
				window.WsalAs.AddFilter( event_id_filter_value );
				event_id_input.val( '' );
			} );
		</script>
		<?php
	}
}
