jQuery( document ).ready( function() {
	jQuery(document).on('confirmation', '.remodal', function (e) {
		var remodalElm = jQuery(this);
		var remodal_id = remodalElm.data('remodal-id');

		if ( 'yes' === remodalElm.attr('data-reload-on-confirm') ) {
			var spinner = remodalElm.find('.spinner');
			spinner.addClass('is-active');
			window.location.reload();
			return;
		}

		if ('wsal-external-db-connection-modal' === remodal_id) {
			var selected_connection = remodalElm.find('select[name="AdapterConnection"] option:selected').val();
			if ( 0 == selected_connection ) {
				showErrorMessageInRemodal(remodalElm, externalData.selectConnectionForExternalStorage );
				return;
			}

			startConnectionChange(true, {
				connection: selected_connection
			}, remodalElm);
		} else if ('wsal-external-db-source-data-choice-modal' === remodal_id) {
			// user decided to delete events in the source database before switching connection
			startConnectionChange('to_external' === remodalElm.attr('data-direction'), {
				connection: remodalElm.attr('data-connection-name'),
				decision: 'delete'
			}, remodalElm);
		} else if ('wsal-external-db-target-data-choice-modal' === remodal_id) {
			// user decided to keep events in the target database and merge it
			startConnectionChange('to_external' === remodalElm.attr('data-direction'), {
				connection: remodalElm.attr('data-connection-name'),
				decision_target: 'merge'
			}, remodalElm);
		} else if ('wsal-external-db-switch-to-local-modal' === remodal_id) {
			startConnectionChange(false, {}, remodalElm);
		}
	});

	jQuery(document).on('cancellation', '.remodal', function (e) {
		var remodalElm = jQuery(this);
		var remodal_id = remodalElm.data('remodal-id');
		if ('wsal-external-db-source-data-choice-modal' === remodal_id) {
			startConnectionChange( 'to_external' === remodalElm.attr('data-direction'), {
				connection: remodalElm.attr('data-connection-name'),
				decision: 'migrate'
			}, remodalElm);
		} else if ('wsal-external-db-target-data-choice-modal' === remodal_id) {
			// user decided to delete events in the target database
			startConnectionChange('to_external' === remodalElm.attr('data-direction'), {
				connection: remodalElm.attr('data-connection-name'),
				decision_target: 'delete'
			}, remodalElm);
		}
	});

	function showErrorMessageInRemodal( remodalElm, errorMessage) {
		var lastElm = remodalElm.find('form');
		if ( lastElm.length === 0) {
			lastElm = remodalElm.find('p')
		}
		lastElm.last().append('<div class="notice notice-error"><p>' + errorMessage + '</p></div>')
	}

	function handleExternalConnectionChangeError(remodalElm, confirmButton, errorMessage) {
		showErrorMessageInRemodal( remodalElm, errorMessage );
		confirmButton.html( externalData.switchConnection ).prop('disabled', '');
	}

	function startConnectionChange(toExternal, extraData, remodalElm) {
		var requestData = {
			action: toExternal ? 'wsal_MigrateOccurrence' : 'wsal_MigrateBackOccurrence',
			nonce: jQuery('input[name="wsal-external-storage-switch-nonce"]').val()
		};

		if (typeof extraData === 'object') {
			requestData = jQuery.extend(requestData, extraData);
		}

		var confirmButton = remodalElm.find('.remodal-confirm');
		confirmButton.html( externalData.workingProgress ).prop('disabled', 'disabled');
		var spinner = remodalElm.find('.spinner');
		spinner.addClass('is-active');
		remodalElm.find('.notice-error').remove();

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: requestData,
			success: function( response ) {
				if (response.success) {
					//	this happens only when the external storage connection is switched
					remodalElm.find('h3').html(response.data.title);
					remodalElm.find('form, p').remove();
					remodalElm.find('h3').after( response.data.content );
					remodalElm.find('.remodal-cancel').remove();
					remodalElm.attr('data-reload-on-confirm', 'yes');
					confirmButton.prop('disabled', '').html( externalData.continue );
					return;
				}

				if ( typeof response.data === 'object' && response.data.show_modal ) {
					//	object is sent only when there is some data in the source database
					var choiceModal = jQuery('[data-remodal-id="' + response.data.show_modal + '"]')
					if (typeof response.data.context_data === 'object' ) {
						jQuery.each(response.data.context_data, function(propertyName, valueOfProperty) {
							choiceModal.attr('data-' + propertyName, valueOfProperty);
						});
						choiceModal.remodal({
							hashTracking: false,
							closeOnConfirm: false,
							closeOnOutsideClick: false,
							closeOnEscape: false,
							closeOnCancel: false
						}).open();
					}
				} else {
					//	regular error message
					handleExternalConnectionChangeError(remodalElm, confirmButton, response.data)
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				handleExternalConnectionChangeError(remodalElm, confirmButton, errorThrown);
			},
			complete: function() {
				spinner.removeClass('is-active');
			}
		});
	}

	function cancelMigration( buttonElm ) {
		buttonElm.val( externalData.workingProgress ).prop('disabled', 'disabled');
		buttonElm.next( '.notice').remove();
		var spinner = buttonElm.next('.spinner');
		spinner.addClass('is-active');

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: {
				action: 'wsal_cancel_external_migration',
				nonce: buttonElm.attr('data-nonce')
			},
			success: function( response ) {
				if (response.success) {
					buttonElm.val( externalData.done );
					buttonElm.after( '<div class="notice notice-success">' + response.data + '</div>' );
				} else {
					//	regular error message
					handleMigrationCancellationError(buttonElm, response.data)
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				handleMigrationCancellationError(buttonElm, textStatus)
			},
			complete: function() {
				spinner.removeClass('is-active');
			}
		});
	}

	function handleMigrationCancellationError( buttonElm, errorMessage ) {
		buttonElm.val( externalData.cancelMigration ).prop('disabled', '');
		buttonElm.after( '<div class="notice notice-error">' + errorMessage + '</div>' );
	}

	jQuery( 'input[name="wsal-external-migration-cancel"]').on('click', function(){
		//	cancel migration button handler
		cancelMigration(jQuery(this));
	});

	jQuery( '#wsal-archiving' ).click( function() {
		var button = this;
		jQuery( button ).val( externalData.archivingProgress );
		jQuery( button ).attr( 'disabled', 'disabled' );
		ArchivingNow( button );
	});

	function ArchivingNow( button ) {
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			data: {
				action: 'wsal_archive_now'
			},
			success: function() {
				setTimeout( function() {
					jQuery( button ).val( externalData.archivingComplete );
				}, 1000 );
			}
		});
	}

	// Test connection button.
	jQuery( '#adapter-test, #mirror-test, #archive-test' ).click( function() {
		jQuery( this ).val( externalData.testingProgress );
		jQuery( this ).attr( 'disabled', true );
		var testType = jQuery( this ).data( 'connection' );
		var nonce = jQuery( '#' + testType + '-test-nonce' ).val();

		wsalTestConnection( this, testType, nonce );
	});

	/**
	 * Test connection with external DBs.
	 *
	 * @param {element} btn   – Button element.
	 * @param {string}  type  – Type of connection to test.
	 * @param {string}  nonce – Connection nonce.
	 */
	function wsalTestConnection( btn, type, nonce ) {

		// Make sure the arguments are not empty.
		if ( ! type.length || ! nonce.length ) {
			return;
		}

		// Ajax request to test connection.
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: {
				action: 'wsal_test_connection',
				nonce: nonce,
				connectionType: type
			},
			success: function( data ) {
				if ( data.success ) {
					if ( data.customMessage ) {
						jQuery( btn ).val( data.customMessage );
					} else {
						jQuery( btn ).val( externalData.connectionSuccess );
					}
				} else {
					jQuery( btn ).val( externalData.connectionFailed );
					console.log( data.message );
				}
			},
			error: function( xhr, textStatus, error ) {
				jQuery( btn ).val( externalData.connectionFailed );
				console.log( xhr.statusText );
				console.log( textStatus );
				console.log( error );
			}
		});
	}

	/**
	 * Reset Archiving Settings
	 *
	 * @since 3.3
	 */
	jQuery( '#wsal-reset-archiving' ).click( function( event ) {
		var resetBtn = jQuery( this );
		resetBtn.val( externalData.resetProgress );
		event.preventDefault();

		// Ajax request to reset archiving settings.
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: {
				action: 'wsal_reset_archiving',
				wpnonce: jQuery( '#wsal_archive_db' ).val()
			},
			success: function( data ) {
				if ( data.success ) {
					location.reload();
				} else {
					resetBtn.val( externalData.resetFailed );
					console.log( data.message );
				}
			},
			error: function( xhr, textStatus, error ) {
				resetBtn.val( externalData.resetFailed );
				console.log( xhr.statusText );
				console.log( textStatus );
				console.log( error );
			}
		});
	});
});
