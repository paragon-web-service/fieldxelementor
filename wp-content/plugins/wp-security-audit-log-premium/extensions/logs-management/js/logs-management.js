
 jQuery( document ).ready( function() {

    var severitiesUrl = ajaxurl + '?action=wsal_ajax_get_all_severities&wsal_nonce=' + wsal_data.wp_nonce;
	jQuery( '#SeveritiesQueryBox' ).autocomplete({
	    source: severitiesUrl,
	    minLength: 1
	});

	var eventTypesUrl = ajaxurl + '?action=wsal_ajax_get_all_event_types&wsal_nonce=' + wsal_data.wp_nonce;
	jQuery( '#EventTypeQueryBox' ).autocomplete({
	    source: eventTypesUrl,
	    minLength: 1
	});

	var objectTypesUrl = ajaxurl + '?action=wsal_ajax_get_all_object_types&wsal_nonce=' + wsal_data.wp_nonce;
	jQuery( '#ObjectTypeQueryBox' ).autocomplete({
	    source: objectTypesUrl,
	    minLength: 1
	});

	var eventIDTypesUrl = ajaxurl + '?action=wsal_ajax_get_all_event_ids&wsal_nonce=' + wsal_data.wp_nonce;
	jQuery( '#EventIDQueryBox' ).autocomplete({
	    source: eventIDTypesUrl,
	    minLength: 1
	});

	// Handle deletion of individual log data.
	jQuery(function() {
		jQuery( 'body' ).on( 'click', '[data-delete-log-data-input]', function ( e ) {
			e.preventDefault();
			var ourButton  = jQuery( this );
			var inputToUse = ourButton.attr( 'data-delete-log-data-input' );
			var type       = ourButton.attr( 'data-delete-log-data-type' );
			var nonce      = ourButton.attr( 'data-nonce' );
			var toClear    = jQuery( inputToUse ).val();
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				async: true,
				data: {
					action: 'wsal_delete_data_from_logs',
					nonce: nonce,
					type: type,
					item_to_remove: toClear
				},
				success: function ( result ) {
					if ( result.success ) {
						jQuery( ourButton ).parent().append( '<span class="notice notice-success" style="display:none">' + result.data + '</span>' );
					} else {
						jQuery( ourButton ).parent().append( '<span class="notice notice-info" style="display:none">' + result.data + '</span>' );
					}
					jQuery( '.logs-management-settings .notice').slideDown();
					setTimeout( function() {
						jQuery( 'logs-management-settings .notice' ).slideUp();
					}, 3000);
				}
			});
		});
	});
 });