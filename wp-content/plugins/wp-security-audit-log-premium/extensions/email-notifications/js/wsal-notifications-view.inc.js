//@ requires jQuery
//@ requires jQuery.WSAL_EDIT_VIEW
//@ requires Wsal_FormValidator

var formValidator = new Wsal_FormValidator( $, 'wsal_trigger',  'wsal-error-container', 'wsal-notif-title', 'wsal-notif-email', 'wsal-trigger-input', 'invalid' );

// TITLE
var tplTitle = $( '#scriptTitle' ).text().trim();
$( '#wsal-section-title' ).append( Mark.up( tplTitle, wsalModel ) );

// EMAIL
var tplEmail = $( '#scriptEmail' ).text().trim();
$( '#wsal-section-email' ).append( Mark.up( tplEmail, wsalModel ) );

// TRIGGERS
var jsContentWrapper = $( '#wsal_content_js' ),
	tplTrigger       = $( '#scriptTrigger' ).text().trim();

// GLOBALS
Mark.globals.numTriggers = 0;  // Holds the number of triggers added to the view.
Mark.globals.lastId = 0;       // Counter for each trigger added.
Mark.globals.maxTriggers = 20; // Max number of triggers allowed to the view.

// Multisite Check.
var is_multisite = jQuery.WSAL_MULTISITE;

// Removes the first dropdown from the first trigger from the view
// it also takes care that the correct css class is applied to this trigger
var updateView = function() {
	if ( Mark.globals.numTriggers ) {

		// Find the first trigger and remove the first dropdown
		jsContentWrapper.find( '.wsal_trigger' )
			.first()
			.removeClass( 'wsal-section-light-full' )
			.addClass( 'wsal-section-light-first' )
			.find( '.js_s1' ).remove();
	}
};

var handleS3Options = function( selectedValue, s3Control ) {
	if ( 'EVENT ID' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'DATE' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS AFTER' ).text( 'IS AFTER' ) );
	} else if ( 'TIME' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS BEFORE' ).text( 'IS BEFORE' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS AFTER' ).text( 'IS AFTER' ) );
	} else if ( 'USERNAME' == selectedValue || 'USER ROLE' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'SOURCE IP' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'CONTAINS' ).text( 'CONTAINS' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'POST ID' == selectedValue || 'PAGE ID' == selectedValue || 'CUSTOM POST ID' == selectedValue || 'SITE DOMAIN' == selectedValue || 'CUSTOM USER FIELD' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'POST TYPE' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'POST STATUS' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'OBJECT' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	} else if ( 'TYPE' == selectedValue ) {
		s3Control.empty();
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS EQUAL' ).text( 'IS EQUAL' ) );
		s3Control.append( $( '<option></option>' ).attr( 'value', 'IS NOT' ).text( 'IS NOT' ) );
	}

	// invalid option.
	else {
		s3Control.empty();
	}
};

// Hook up listeners to the controls inside a trigger
var bindListeners = function( trigger, lastId ) {
	var selectedValue = '',
		s1 = $( '#select_1_' + lastId ),
		s2 = $( '#select_2_' + lastId ),
		s3 = $( '#select_3_' + lastId ),
		s4 = $( '.custom-dropdown__status #select_4_' + lastId ), // Post Status.
		s5 = $( '.custom-dropdown__post_type #select_5_' + lastId ), // Post Type.
		s6 = $( '.custom-dropdown__user_role #select_6_' + lastId ), // User Role.
		s7 = $( '.custom-dropdown__objects #select_7_' + lastId ), // Objects.
		s8 = $( '.custom-dropdown__type #select_8_' + lastId ), // Type.
		i1 = $( '#input_1_' + lastId );

	if ( 0 < s1.length ) {
		s1.on( 'change', function() {
			var selected = trigger.select1.data.indexOf( $( this ).val() );
			trigger.select1.selected = selected;
			$( '#select_1_' + lastId + '_hidden' ).val( selected );
		});

		// Set selected item
		selectedValue = trigger.select1.data[trigger.select1.selected];
		s1.val( selectedValue );
		$( '#select_1_' + lastId + '_hidden' ).val( trigger.select1.selected );
	}
	s2.on( 'change', function() {
		var selectedValue = $( this ).val();
		var selected = trigger.select2.data.indexOf( selectedValue );

		trigger.select2.selected = selected;
		$( '#select_2_' + lastId + '_hidden' ).val( selected );

		s4.parent().parent().addClass( 'custom-dropdown__hide' );
		s5.parent().parent().addClass( 'custom-dropdown__hide' );
		s6.parent().parent().addClass( 'custom-dropdown__hide' );
		s7.parent().parent().addClass( 'custom-dropdown__hide' );
		s8.parent().parent().addClass( 'custom-dropdown__hide' );

		// Update the input1 as needed based on user selection
		if ( 'DATE' == selectedValue ) {
			Wsal_CreateDatePicker( $, i1, null );
		} else if ( 'TIME' == selectedValue ) {
			Wsal_CreateTimePicker( i1, null );
		} else if ( 'POST STATUS' == selectedValue ) {
			i1.addClass( 'custom-dropdown__hide' );
			i1.val( trigger.select4.selected );
			s4.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'POST TYPE' == selectedValue && ! is_multisite ) {
			i1.addClass( 'custom-dropdown__hide' );
			i1.val( trigger.select5.selected );
			s5.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'USER ROLE' == selectedValue ) {
			i1.addClass( 'custom-dropdown__hide' );
			i1.val( trigger.select6.selected );
			s6.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'OBJECT' == selectedValue ) {
			i1.addClass( 'custom-dropdown__hide' );
			i1.val( trigger.select7.selected );
			s7.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'TYPE' == selectedValue ) {
			i1.addClass( 'custom-dropdown__hide' );
			i1.val( trigger.select8.selected );
			s8.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else {
			// remove both
			Wsal_RemovePickers( i1 );
			i1.removeClass( 'custom-dropdown__hide' );
			// Cant pass a string like we would normally due to how this JS is injected into the page.
			// So, lets grab the placeholder text originally used.
			var defaultPlaceholder = jQuery('#input_1_1').attr( 'placeholder' );
			// Re-apply the correct placeholder as the input is just text.
			i1.attr( 'placeholder', defaultPlaceholder );
		}

		// Disable invalid options from select3
		handleS3Options( selectedValue, s3 );
	});

	// Set selected item
	selectedValue = trigger.select2.data[trigger.select2.selected];
	s2.val( selectedValue );
	$( '#select_2_' + lastId + '_hidden' ).val( trigger.select2.selected );

	// Create plugins if needed
	if ( jQuery.WSAL_EDIT_VIEW ) {
		var what = trigger.select2.data[trigger.select2.selected];
		if ( 'DATE' == what ) {
			Wsal_CreateDatePicker( $, i1, trigger.input1 );
		} else if ( 'TIME' == what ) {
			Wsal_CreateTimePicker( i1, trigger.input1 );
		} else if ( 'POST STATUS' == what ) {
			i1.addClass( 'custom-dropdown__hide' );
			s4.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'POST TYPE' == what && ! is_multisite ) {
			i1.addClass( 'custom-dropdown__hide' );
			s5.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'USER ROLE' == what ) {
			i1.addClass( 'custom-dropdown__hide' );
			s6.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'OBJECT' == what ) {
			i1.addClass( 'custom-dropdown__hide' );
			s7.parent().parent().removeClass( 'custom-dropdown__hide' );
		} else if ( 'TYPE' == what ) {
			i1.addClass( 'custom-dropdown__hide' );
			s8.parent().parent().removeClass( 'custom-dropdown__hide' );
		}
		handleS3Options( what, s3 );
	}

	/**
     * Select 3 on change event.
     */
	s3.on( 'change', function() {
		var selected = trigger.select3.data.indexOf( $( this ).val() );
		trigger.select3.selected = selected;
		$( '#select_3_' + lastId + '_hidden' ).val( selected );
	});

	// Set selected item
	selectedValue = trigger.select3.data[trigger.select3.selected];
	s3.val( selectedValue );
	$( '#select_3_' + lastId + '_hidden' ).val( trigger.select3.selected );

	/**
     * Select 4 on change event.
     */
	s4.on( 'change', function() {
		var selected = trigger.select4.data.indexOf( $( this ).val() );
		trigger.select4.selected = selected;
		$( '#select_4_' + lastId + '_hidden' ).val( selected );
		i1.val( selected );
	});

	// Set selected item
	selectedValue = trigger.select4.data[trigger.select4.selected];
	s4.val( selectedValue );
	$( '#select_4_' + lastId + '_hidden' ).val( trigger.select4.selected );

	/**
     * Select 5 on change event.
     */
	s5.on( 'change', function() {
		var selected = trigger.select5.data.indexOf( $( this ).val() );
		trigger.select5.selected = selected;
		$( '#select_5_' + lastId + '_hidden' ).val( selected );
		i1.val( selected );
	});

	// Set selected item
	selectedValue = trigger.select5.data[trigger.select5.selected];
	s5.val( selectedValue );
	$( '#select_5_' + lastId + '_hidden' ).val( trigger.select5.selected );

	/**
     * Select 6 on change event.
     */
	s6.on( 'change', function() {
		var selected = trigger.select6.data.indexOf( $( this ).val() );
		trigger.select6.selected = selected;
		$( '#select_6_' + lastId + '_hidden' ).val( selected );
		i1.val( selected );
	});

	// Set selected item
	selectedValue = trigger.select6.data[trigger.select6.selected];
	s6.val( selectedValue );
	$( '#select_6_' + lastId + '_hidden' ).val( trigger.select6.selected );

	/**
     * Select 7 on change event.
     */
	s7.on( 'change', function() {
		var selected = trigger.select7.data.indexOf( $( this ).val() );
		trigger.select7.selected = selected;
		$( '#select_7_' + lastId + '_hidden' ).val( selected );
		i1.val( selected );
	});

	// Set selected item
	selectedValue = trigger.select7.data[trigger.select7.selected];
	s7.val( selectedValue );
	$( '#select_7_' + lastId + '_hidden' ).val( trigger.select7.selected );

	/**
     * Select 8 on change event.
     */
	s8.on( 'change', function() {
		var selected = trigger.select8.data.indexOf( $( this ).val() );
		trigger.select8.selected = selected;
		$( '#select_8_' + lastId + '_hidden' ).val( selected );
		i1.val( selected );
	});

	// Set selected item
	selectedValue = trigger.select8.data[trigger.select8.selected];
	s8.val( selectedValue );
	$( '#select_8_' + lastId + '_hidden' ).val( trigger.select8.selected );

	i1.val( trigger.input1 );

	$( '#deleteButton_' + lastId ).on( 'click', function() {
		if ( 1 > Mark.globals.numTriggers ) {
			return false;
		}

		// remove trigger from DOM
		var eId = $( this ).data( 'removeid' );
		$( '#' + eId ).remove();

		Mark.globals.numTriggers--;

		// Update view
		updateView();
		return true;
	});

	// Attach event listeners for grouping
	$( '#buttonAddToGroup_' + lastId ).on( 'click', function() {
		var parentID = $( this ).data( 'parentid' );
		var parent = $( '#' + parentID );
		GroupManager.AddToGroup( parent, $ );
	});

	var dd = $( '#wsal_options_' + lastId ),
		$trigger = dd.parents( '#trigger_id_' + lastId );
	dd.on( 'change', function() {
		var option = $( this ).val();
		switch ( option ) {
		case 'groupAbove': { GroupManager.GroupAbove( $trigger ); break; }
		case 'groupBelow': { GroupManager.GroupBelow( $trigger ); break; }
		case 'ungroup': { GroupManager.Ungroup( $trigger ); break; }
		case 'moveUp': { GroupManager.MoveUp( $trigger ); break; }
		case 'moveDown': { GroupManager.MoveDown( $trigger ); break; }
		}
		handleOptionsDropDown( jQuery, jQuery( this ) );
	});
};

// prepares the model object to be sent server-side for processing
var preparePostData = function() {
	wsalModel.info.title = $( '#wsal-notif-title' ).val().trim();
	wsalModel.errors.titleMissing = '';
	wsalModel.errors.titleInvalid = '';
	wsalModel.info.email = $( '#wsal-notif-email' ).val().trim();
	wsalModel.errors.emailMissing = '';
	wsalModel.errors.emailInvalid = '';
	wsalModel.info.phone = $( '#wsal-notif-phone' ).val().trim();
	wsalModel.errors.phoneMissing = '';
	wsalModel.errors.phoneInvalid = '';

	var _triggers = $( '.wsal_trigger' );
	wsalModel.triggers = []; // reset first

	// set the view state
	wsalModel.viewState = getViewState();

	// update triggers
	$.each( wsalModel.viewState, function( i, entry ) {
		if ( $.isArray( entry ) ) {
			$.each( entry, function( k, id ) {
				var trigger = $( '#' + id ),
					s1Selected = ~~$( '.wsal-s1 input[type="hidden"]', trigger ).val(),
					s2Selected = ~~$( '.wsal-s2 input[type="hidden"]', trigger ).val(),
					s3Selected = ~~$( '.wsal-s3 input[type="hidden"]', trigger ).val(),
					s4Selected = ~~$( '.wsal-s4 input[type="hidden"]', trigger ).val(),
					s5Selected = ~~$( '.wsal-s5 input[type="hidden"]', trigger ).val(),
					s6Selected = ~~$( '.wsal-s6 input[type="hidden"]', trigger ).val(),
					s7Selected = ~~$( '.wsal-s7 input[type="hidden"]', trigger ).val(),
					s8Selected = ~~$( '.wsal-s8 input[type="hidden"]', trigger ).val(),
					i1 = $( '.wsal-fly .wsal-trigger-input', trigger ).val().trim();
				var obj = {
					'select1': {
						'data': wsalModel.default.select1.data,
						'selected': s1Selected
					},
					'select2': {
						'data': wsalModel.default.select2.data,
						'selected': s2Selected
					},
					'select3': {
						'data': wsalModel.default.select3.data,
						'selected': s3Selected
					},
					'select4': {
						'data': wsalModel.default.select4.data,
						'selected': s4Selected
					},
					'select5': {
						'data': wsalModel.default.select5.data,
						'selected': s5Selected
					},
					'select6': {
						'data': wsalModel.default.select6.data,
						'selected': s6Selected
					},
					'select7': {
						'data': wsalModel.default.select7.data,
						'selected': s7Selected
					},
					'select8': {
						'data': wsalModel.default.select8.data,
						'selected': s8Selected
					},
					'input1': i1,
					'deleteButton': WsalTranslator.deleteButtonText
				};
				wsalModel.triggers.push( obj );
			});
		} else {
			var trigger = $( '#' + entry ),
				s1Selected = ~~$( '.wsal-s1 input[type="hidden"]', trigger ).val(),
				s2Selected = ~~$( '.wsal-s2 input[type="hidden"]', trigger ).val(),
				s3Selected = ~~$( '.wsal-s3 input[type="hidden"]', trigger ).val(),
				s4Selected = ~~$( '.wsal-s4 input[type="hidden"]', trigger ).val(),
				s5Selected = ~~$( '.wsal-s5 input[type="hidden"]', trigger ).val(),
				s6Selected = ~~$( '.wsal-s6 input[type="hidden"]', trigger ).val(),
				s7Selected = ~~$( '.wsal-s7 input[type="hidden"]', trigger ).val(),
				s8Selected = ~~$( '.wsal-s8 input[type="hidden"]', trigger ).val(),
				i1 = $( '.wsal-fly .wsal-trigger-input', trigger ).val().trim();
			var obj = {
				'select1': {
					'data': wsalModel.default.select1.data,
					'selected': s1Selected
				},
				'select2': {
					'data': wsalModel.default.select2.data,
					'selected': s2Selected
				},
				'select3': {
					'data': wsalModel.default.select3.data,
					'selected': s3Selected
				},
				'select4': {
					'data': wsalModel.default.select4.data,
					'selected': s4Selected
				},
				'select5': {
					'data': wsalModel.default.select5.data,
					'selected': s5Selected
				},
				'select6': {
					'data': wsalModel.default.select6.data,
					'selected': s6Selected
				},
				'select7': {
					'data': wsalModel.default.select7.data,
					'selected': s7Selected
				},
				'select8': {
					'data': wsalModel.default.select8.data,
					'selected': s8Selected
				},
				'input1': i1,
				'deleteButton': WsalTranslator.deleteButtonText
			};
			console.log( s2Selected );
			wsalModel.triggers.push( obj );
		}
	});

	// Set input data
	$( '#wsal-form-data' ).val( JSON.stringify( wsalModel ) );
};

/**
 * Restore the groups in the notification view
 */
var restoreViewState = function() {
	var data = wsalModel.viewState;
	if ( data.length ) {
		$.each( data, function( i, entry ) {

			// restore groups
			if ( $.isArray( entry ) ) {
				var target = $( '#' + entry[0]);
				var elements = [];
				$.each( entry, function( j, id ) {
					if ( 0 < j ) {
						elements.push( $( '#' + id ) );
					}
				});
				GroupManager.MakeGroup( target, elements );
			}
		});
	}
};

// Add a trigger to the view
var addTrigger = function( tplTrigger, model ) {
	Mark.globals.lastId++;
	Mark.globals.numTriggers++;

	// Clear first
	if ( ! jQuery.WSAL_EDIT_VIEW ) {
		model.select1.selected = 0;
		model.select2.selected = 0;
		model.select3.selected = 0;
		model.select4.selected = 0;
		model.select5.selected = 0;
		model.select6.selected = 0;
		model.select7.selected = 0;
		model.select8.selected = 0;
		model.input1 = '';
	}

	// console.log( model );
	jsContentWrapper.append( Mark.up( tplTrigger, model ) );
	handleS3Options( model.select2.data[model.select2.selected], $( '#select_3_' + Mark.globals.lastId ) );
};

//region >>> ON_LOAD

// if there are triggers to display
var _triggers = wsalModel.triggers;
var _tl = _triggers.length;
if ( _tl ) {

	// Remove extra triggers if any
	if ( _tl >= Mark.globals.maxTriggers ) {
		var j = Mark.globals.maxTriggers;
		for ( j; j < _tl; j++ ) {
			_triggers.pop();
		}
		_tl = Mark.globals.maxTriggers;
	}

	// Append triggers
	$.each( _triggers, function( i, triggerData ) {
		addTrigger( tplTrigger, triggerData );
		bindListeners( triggerData, Mark.globals.lastId );
	});

	// Restore groups
	restoreViewState();
	GroupManager.__updateOptions();
} else {
	wsalModel.viewState = [];
}


// Display errors if any
if ( ! jQuery.isEmptyObject( wsalModel.errors.triggers ) ) {
	formValidator.clearErrors();
	$.each( wsalModel.errors.triggers, function( k, error ) {
		formValidator.addError( error );
		$( '#input_1_' + k ).addClass( 'invalid' );
	});
	formValidator.addTitleForErrors( '<span style="margin-bottom: 5px; display: block;"><strong style="font-size: 13px; padding-bottom:5px;">' + WsalTranslator.errorsTitle + '</strong></span>' );
	formValidator.showErrors();
}

//endregion >>> ON_LOAD

$( '#wsal-button-add-trigger' ).on( 'click', function() {
	if ( Mark.globals.numTriggers < Mark.globals.maxTriggers ) {
		if ( jQuery.WSAL_EDIT_VIEW ) {

			// Clear first
			wsalModel.default.select1.selected = 0;
			wsalModel.default.select2.selected = 0;
			wsalModel.default.select3.selected = 0;
			wsalModel.default.select4.selected = 0;
			wsalModel.default.select5.selected = 0;
			wsalModel.default.select6.selected = 0;
			wsalModel.default.select7.selected = 0;
			wsalModel.default.select8.selected = 0;
			wsalModel.default.input1 = '';
		}
		addTrigger( tplTrigger, wsalModel.default );
		bindListeners( wsalModel.default, Mark.globals.lastId );
		GroupManager.__updateOptions();
	}
});

$( '#wsal-submit' ).click( function() {
	if ( formValidator.validate() ) {
		preparePostData();
		return true;
	}
	return false;
});

$( document ).ready( function() {

	function wsalCheckTemplate() {
		var selected = $( 'input[type=\'radio\'][name=\'template\']:checked' ).val();
		if ( 'default' == selected ) {
			$( '#wsal-section-template' ).addClass( 'hidden' );
		} else {
			$( '#wsal-section-template' ).removeClass( 'hidden' );
		}
	}

	wsalCheckTemplate();

	$( 'input[type=\'radio\'][name=\'template\']' ).change( function() {
		if ( 'default' == this.value ) {
			$( '#wsal-section-template' ).addClass( 'hidden' );
		} else {
			$( '#wsal-section-template' ).removeClass( 'hidden' );
		}
	});

	// Backward compatibility for Page ID and Custom Post ID.
	var s2 = $( '.js_s2' );
	var select2value = s2.val();
	if ( 'PAGE ID' == select2value || 'CUSTOM POST ID' == select2value ) {

		// Change value to POST ID.
		s2.val( 'POST ID' );
	}
});
