( function( $ ) {

	$( document ).ready( function() {

		scriptData.validators = {};

		// Current wizard step.
		var currentStep = 1;
		var wizardSave = jQuery( '#wizard-save' );
		var wizardNext = jQuery( '#wizard-next' );
		var wizardCancel = jQuery( '#wizard-cancel' );
		var configSaveBtn = '';

		if ( '' !== scriptData.mirror ) {
			configSaveBtn = jQuery( '#submit' );
		}

		// initialise the dialog
		function initializeWizard( wizardId, wizardTitle ) {
			$( wizardId ).dialog({
				title: wizardTitle,
				dialogClass: 'wp-dialog',
				autoOpen: false,
				draggable: false,
				width: 750,
				modal: true,
				resizable: false,
				closeOnEscape: true,
				position: {
					my: 'center',
					at: 'center',
					of: window
				},
				open: function() {

					// close dialog by clicking the overlay behind it
					$( '.ui-widget-overlay' ).bind( 'click', function() {
						$( wizardId ).dialog( 'close' );
					});

					wizardCancel.bind( 'click', function() {
						$( wizardId ).dialog( 'close' );
					});
				},
				create: function() {
					// style fix for WordPress admin
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
				}
			});
		}

		function bindWizardBtn( btnId, wizardId ) {
			$( btnId ).click( function( e ) {
				e.preventDefault();
				$( wizardId ).dialog( 'open' );
				resetConnectionWizard();
				loadNextWizardStep( currentStep );
			});
		}

		initializeWizard( '#wsal-mirroring-wizard', scriptData.mirrorTitle ); // Mirroring Wizard.
		bindWizardBtn( '#wsal-create-mirror', '#wsal-mirroring-wizard' );

		// Edit Mirror screen
		if ( scriptData.mirror ) {
			$( '#wsal-mirroring-wizard' ).dialog( 'open' );
			resetConnectionWizard();
			loadNextWizardStep( currentStep );
		}

		/**
		 * Reset Connection Wizard Contents.
		 */
		function resetConnectionWizard() {
			currentStep = 1;
			jQuery( '#content-step-1' ).addClass( 'hide' );
			jQuery( '#content-step-2' ).addClass( 'hide' );
			jQuery( '#content-step-3' ).addClass( 'hide' );
			jQuery( '#wizard-save' ).addClass( 'hide' );
			jQuery( '#wizard-next' ).addClass( 'hide' );
			jQuery( '#wizard-cancel' ).addClass( 'hide' );
			jQuery( '.steps > li' ).removeClass( 'is-active' );

			if ( '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			$( 'span.error' ).hide();
		}

		/**
		 * Load Next Mirroring Wizard Step.
		 *
		 * @param {integer} currentStep – Current step number.
		 */
		function loadNextWizardStep( currentStep ) {
			var previousStep = currentStep - 1;

			jQuery( '#step-' + previousStep ).removeClass( 'is-active' );
			jQuery( '#content-step-' + previousStep ).addClass( 'hide' );
			jQuery( '#step-' + currentStep ).addClass( 'is-active' );
			jQuery( '#content-step-' + currentStep ).removeClass( 'hide' );

			if ( 1 === currentStep ) {
				wizardNext.removeClass( 'hide' );
				wizardCancel.removeClass( 'hide' );
			} else if ( 3 === currentStep ) {
				wizardNext.addClass( 'hide' );
				wizardSave.removeClass( 'hide' );
			}

		}

		wizardNext.click( function( e ) {
			var detailInputs   = '';
			e.preventDefault(); // Prevent default.

			if ( 1 === currentStep ) {
				detailInputs = jQuery( '#mirror-name' );
				var mirrorConnection = jQuery( '#mirror-connection' );

				if ( checkInputForError( detailInputs ) || checkInputForError( mirrorConnection ) ) {
					return;
				}

				currentStep += 1;
				loadNextWizardStep( currentStep );
			} else {
				currentStep += 1;
				loadNextWizardStep( currentStep );
			}
		});

		/**
		 * Check Input for Error.
		 *
		 * @param {element} detailInput - Wizard HTML Element.
		 */
		function checkInputForError( detailInput ) {
			if ( '' === detailInput.val() || null === detailInput.val() ) {
				detailInput.addClass( 'error' );
				detailInput.change( function() {
					if ( '' !== jQuery( this ).val() && jQuery( this ).hasClass( 'error' ) ) {
						jQuery( this ).removeClass( 'error' );
					}
				});
				return true;
			}
			return false;
		}

		/**
		 * Mirror Wizard Events.
		 */
		function initializeEventSelect2( selectId, optionId, placeHolderText ) {
			if ( 'undefined' !== typeof Select2 && 'object' === typeof Select2 ) {
				$( selectId ).select2({
					placeholder: placeHolderText,
					allowClear: true,
					width: '600px'
				})
					.on( 'select2-open', function( e ) {
						var v = $( e ).val;
						if ( v.length ) {
							$( optionId ).prop( 'checked', true );
						}
					}).on( 'select2-selecting', function( e ) {
						var v = $( e ).val;
						if ( v.length ) {
							$( optionId ).prop( 'checked', true );
						}
					}).on( 'select2-removed', function( e ) {
						var v = $( this ).val();
						if ( ! v.length ) {
							$( optionId ).prop( 'checked', false );
						}
					});
			}
		}
		initializeEventSelect2( '#mirror-select-event-codes', '#mirror-filter-event-codes', scriptData.eventsPlaceholder );
		initializeEventSelect2( '#mirror-select-except-codes', '#mirror-filter-except-codes', scriptData.eventsPlaceholder );
		initializeEventSelect2( '#mirror-select-severities', '', scriptData.severitiesPlaceholder );

		/**
		 * Toggle mirror state.
		 */
		jQuery( '.wsal-mirror-toggle' ).click( function( event ) {
			var mirrorName = '';
			var toggleNonce = '';
			var toggleBtn = '';
			var mirrorState = '';
			event.preventDefault();

			toggleBtn = jQuery( this );
			mirrorName = toggleBtn.data( 'mirror' );
			toggleNonce = toggleBtn.data( 'nonce' );
			mirrorState = toggleBtn.data( 'state' );

			if ( 'Enable' === toggleBtn.text() ) {
				toggleBtn.text( scriptData.enabling );
			} else {
				toggleBtn.text( scriptData.disabling );
			}

			// Ajax request to test connection.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_toggle_mirror_state',
					nonce: toggleNonce,
					mirror: mirrorName,
					state: mirrorState
				},
				success: function( data ) {
					if ( data.success ) {
						toggleBtn.text( data.button );
						toggleBtn.data( 'state', data.state );
					} else {
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					toggleBtn.val( 'Failed!' );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		/**
		 * Delete Mirror.
		 */
		jQuery( '.wsal-mirror-delete' ).click( function( event ) {
			var mirrorName = '';
			var deleteMirrorNonce = '';
			var deleteMirrorBtn = '';
			event.preventDefault();

			// Delete confirmation.
			if ( ! confirm( scriptData.confirmDelMirror ) ) {
				return;
			}

			deleteMirrorBtn = jQuery( this );
			deleteMirrorBtn.text( scriptData.deleting );
			mirrorName = deleteMirrorBtn.data( 'mirror' );
			deleteMirrorNonce = deleteMirrorBtn.data( 'nonce' );

			// Ajax request to delete mirror.
			jQuery.ajax({
				type: 'POST',
				url: scriptData.ajaxURL,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_delete_mirror',
					nonce: deleteMirrorNonce,
					mirror: mirrorName
				},
				success: function( data ) {
					if ( data.success ) {
						location.reload();
					} else {
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					deleteMirrorBtn.val( 'Failed!' );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		/**
		 * Run Mirror Manually.
		 */
		jQuery( '.wsal-mirror-run-now' ).click( function( event ) {
			var mirrorRunBtn = jQuery( this );
			mirrorRunBtn.html( scriptData.mirrorInProgress );
			mirrorRunBtn.attr( 'disabled', true );
			event.preventDefault();

			// Ajax request to run mirror manually.
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				async: true,
				dataType: 'json',
				data: {
					action: 'wsal_run_mirror',
					wpnonce: mirrorRunBtn.data( 'nonce' ),
					mirror: mirrorRunBtn.data( 'mirror' )
				},
				success: function( data ) {
					if ( data.success ) {
						mirrorRunBtn.html( scriptData.mirrorComplete );
					} else {
						mirrorRunBtn.html( scriptData.mirrorFailed );
						console.log( data.message );
					}
				},
				error: function( xhr, textStatus, error ) {
					mirrorRunBtn.html( scriptData.mirrorFailed );
					console.log( xhr.statusText );
					console.log( textStatus );
					console.log( error );
				}
			});
		});

		$('input[data-required="yes"]').on('change keyup paste', function () {
			if (this.value.length === 0 ) {
				showErrors(this);
			} else {
			  hideErrors(this);
      }
		});

		// Mirror name pattern detection.
		$( '#mirror-name' ).on( 'change keyup paste', function() {
			matchNamePattern( this, 'mirror' );
		});

		function hideErrors(inputStr) {
			var inputError = $( inputStr ).parent().find( 'span.error' );

			// Hide error.
			inputError.hide();

			if ( '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' ); // Remove configure save btn disabled attribute.
			} else {
				wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}
		}

		function showErrors( inputStr ) {
			var inputError = $( inputStr ).parent().find( 'span.error' );

			inputError.show();
			if ( '' !== scriptData.connection || '' !== scriptData.mirror ) {
				configSaveBtn.attr( 'disabled', 'disabled' );
			} else {
				wizardNext.attr( 'disabled', 'disabled' );
			}
		}

		scriptData.validators.validateRegExp = function(inputStr, strPattern, partialMatchAllowed = false ) {

			hideErrors(inputStr);

			var searchName = $( inputStr ).val();
			var matches = searchName.match(strPattern);
			if ( matches !== null ) {
				if (partialMatchAllowed) {
					return;
				}

				if (matches[0] === searchName) {
					return;
				}
			}

			showErrors(inputStr);
		}

		/**
		 * Wizard Name Pattern Match.
		 *
		 * @param {element} wizardNameInput - Wizard Name Element.
		 * @param {string}  nameType        - Wizard Type.
		 */
		function matchNamePattern( wizardNameInput, nameType ) {
			var searchName = $( wizardNameInput ).val();
			var wizardNameError = $( wizardNameInput ).parent().find( 'span.error' );
			var nameLength = searchName.length;
			var namePattern = /^[a-z\d\_]+$/i; // Upper and lower case alphabets, numbers, and underscore allowed.

			// Hide wizard name error.
			wizardNameError.hide();

			// Configure connection/mirror view.
			if ( '' !== scriptData.mirror ) {
				configSaveBtn.removeAttr( 'disabled' );
			} else {
					wizardNext.removeAttr( 'disabled' ); // Remove wizard next btn disabled attribute.
			}

			if (typeof excludeNames !== 'undefined' && excludeNames.length) {
				if (excludeNames.includes(searchName)) {
					if ( '' !== scriptData.mirror ) {
						configSaveBtn.attr( 'disabled', 'disabled' );
					} else {
						wizardNext.attr( 'disabled', 'disabled' );
					}
					wizardNameError.show();
				}
			}

			if ( ( nameLength && ! namePattern.test( searchName ) ) || 25 < nameLength ) {
				wizardNameError.show();

				// Configure mirror view.
				if ( '' !== scriptData.mirror ) {
					configSaveBtn.attr( 'disabled', 'disabled' );
				} else {
					// Wizard mirror view.
					if ( 'mirror' === nameType ) {
						wizardNext.attr( 'disabled', 'disabled' );
					}
				}
			}
			if (!nameLength) {
				configSaveBtn.attr( 'disabled', 'disabled' );
				wizardNameError.show();
			}
		}
	});
}( jQuery ) );
