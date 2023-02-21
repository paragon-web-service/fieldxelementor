/**
 * Script for the reporting UI.
 *
 *  @param {jQuery} $        jQuery object.
 *  @param {Object} window   The window object.
 */
( function ( $, window) {
	
	window.wsal_reporting = window.wsal_reporting || {
		
		select_closest_selector: function( e ) {
			$( e.target ).closest( 'tr' ).find( 'input[type="radio"]' ).prop( 'checked', true );
		},
		
		select_closest_selector_if_value_not_empty: function( e ) {
			var targetElm = $( this );
			var v = targetElm.val();
			if ( v.length ) {
				window.wsal_reporting.select_closest_selector( e );
			}
		},
		
		select_all_selector: function( e ) {
			$( e.target ).closest( 'table' ).find( 'input[type="radio"]' ).first().prop( 'checked', true );
		},
		
		select_all_selector_if_value_empty: function( e ) {
			var targetElm = $( this );
			var v = targetElm.val();
			if ( ! v.length ) {
				window.wsal_reporting.select_all_selector( e );
			}
		},
		
		append_select2_events: function( select2obj ) {
			select2obj.on( 'select2:open', window.wsal_reporting.select_closest_selector )
				.on( 'select2:unselect', window.wsal_reporting.select_all_selector_if_value_empty )
				.on( 'select2:close', window.wsal_reporting.select_all_selector_if_value_empty )
				.on( 'change', window.wsal_reporting.select_closest_selector_if_value_not_empty );
		}
	};
	
	// Validation date format
	$( '.date-range' ).on( 'change', function () {
		if ( wsal_CheckDate( $( this ).val() ) ) {
			jQuery( this ).css( 'border-color', '#aaa' );
		} else {
			jQuery( this ).css( 'border-color', '#dd3d36' );
		}
	})

}( jQuery, window ) );