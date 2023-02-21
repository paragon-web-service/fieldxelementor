/**
 * Format a string
 * Example: 'Hello {0}!'.wsalFormat('Your Name');
 * @see: http://stackoverflow.com/a/2648463
 * @returns {string}
 */
String.wsalFormat = function() {
	var s = arguments[0];
	for (var i=0; i<arguments.length-1; i++) {
		var reg = new RegExp("\\{"+i+"\\}", "gm");
		s = s.replace(reg, arguments[i+1]);
	}
	return s;
};
/**
 * Cleanup html entities from the given string
 * @param string The string to cleanup
 * @returns {string}
 */
var wsalRemoveHtml = function(string) {
	var entityMap = { "&": "","<": "",">": "",'"': '',"'": '',"/": '',"?" : '',"!" : '',"#" : '' };
	return String(string).replace(/[&<>"'\/]/g, function (s) { return entityMap[s]; });
};
/**
 * Sanitize the provided input
 * @param input string. The string to sanitize
 * @param forSearch boolean Whether or not this function should be used in the search form
 * @returns {string}
 */
var wsalSanitize = function(input, forSearch) {
	var output = input.replace(/<script[^>]*?>.*?<\/script>/gi, '').
		replace(/<[\/\!]*?[^<>]*?>/gi, '').
		replace(/<style[^>]*?>.*?<\/style>/gi, '').
		replace(/<![\s\S]*?--[ \t\n\r]*>/gi, '');
	if(forSearch){
		output = wsalRemoveHtml(output);
	}
	return output.replace(/[^A-Z0-9_-]/gi, '');
};
/**
 * Sanitize the input from triggers
 * @param input string The text to sanitize
 * @returns {string}
 */
var wsalSanitizeCondition = function(input){
	var output = input.replace(/<script[^>]*?>.*?<\/script>/gi, '').
		replace(/<[\/\!]*?[^<>]*?>/gi, '').
		replace(/<style[^>]*?>.*?<\/style>/gi, '').
		replace(/<![\s\S]*?--[ \t\n\r]*>/gi, '');
	return output.replace(/[^a-z0-9.':\-]/gi, '');
};

// date should be set only when available
function Wsal_CreateDatePicker($, $input, date){
	$input.timeEntry('destroy');
	$input.val(''); // clear
	wsal_CreateDatePicker($, $input, date);
  	// Turn autocomplete off for this input, as it can cause picker to be difficult to use.
  	$input.attr( 'autocomplete', 'off' );
}
// time should be set only when available
function Wsal_CreateTimePicker($input, time){
	time = time || '12:00';
	$input.datepicker('destroy');
	$input.val(''); // clear
	$input.timeEntry({
		spinnerImage: '',
		show24Hours: show24Hours
	}).timeEntry('setTime', time);
  // Turn autocomplete off for this input, as it can cause picker to be difficult to use.
  $input.attr( 'autocomplete', 'off' );
}

function Wsal_RemovePickers($input){
	$input.datepicker('destroy');
	$input.timeEntry('destroy');
	$input.val(''); // clear
}
//
var handleOptionsDropDown = function($, $dd){
	$dd.empty()
		.append($("<option></option>").attr("value", '-1').text(WsalTranslator.groupOptions))
		.append($("<option></option>").attr("value", 'groupAbove').text(WsalTranslator.groupAbove))
		.append($("<option></option>").attr("value", 'groupBelow').text(WsalTranslator.groupBelow))
		.append($("<option></option>").attr("value", 'ungroup').text(WsalTranslator.ungroup))
		.append($("<option></option>").attr("value", 'moveUp').text(WsalTranslator.moveUp))
		.append($("<option></option>").attr("value", 'moveDown').text(WsalTranslator.moveDown))

	var $parent = $dd.parents('.wsal_trigger')
		,$group = $dd.parents('.'+GroupManager.groupDefaultCssClass)
		,inGroup = ($group.length > 0);

	var prevElement = $parent.prev()
		,prevIsFirstChild = true
		,prevIsTrigger = prevElement.length>0 ? GroupManager.IsTrigger(prevElement): false
		,prevIsGroup = prevElement.length>0 ? GroupManager.IsGroup(prevElement): false
		,nextElement = $parent.next()
		,nextIsTrigger = nextElement.length>0 ? GroupManager.IsTrigger(nextElement) : false
		,nextIsGroup = nextElement.length>0 ? GroupManager.IsGroup(nextElement) : false
		,s1 = prevElement.find('.wsal-s1 select');

	if(prevElement.prev().length > 0){
		prevIsFirstChild = false;
	}

	if(inGroup) {
		$dd.find("option[value='ungroup']").prop("disabled", false);
		$dd.find("option[value='groupAbove']").prop("disabled", true);
		$dd.find("option[value='groupBelow']").prop("disabled", true);
		if(s1.length<1){
			$dd.find("option[value='moveUp']").prop("disabled", true);
		}
		if(nextIsTrigger){
			$dd.find("option[value='moveDown']").prop("disabled", false);
		}
		else { $dd.find("option[value='moveDown']").prop("disabled", true); }
	}
	else {
		$dd.find("option[value='ungroup']").prop("disabled", true);

		if(s1.length>0){
			$dd.find("option[value='moveUp']").prop("disabled", false);
		}
		if(nextIsTrigger || nextIsGroup){
			$dd.find("option[value='moveDown']").prop("disabled", false);
		}
		else {
			$dd.find("option[value='groupBelow']").prop("disabled", true);
			$dd.find("option[value='moveDown']").prop("disabled", true);
		}
		if(prevIsFirstChild){
			$dd.find("option[value='moveUp']").prop("disabled", true);
		}
		else { $dd.find("option[value='moveUp']").prop("disabled", false); }
	}
};

/**
 * Retrieve the view state of the notification
 * @returns {Array}
 */
var getViewState = function(){
	$ = jQuery;
	var result = [], children = $('#wsal_content_js').children();
	if(children.length){
		$.each(children, function(i,v){
			var group = [];
			var element = $(this);
			//id = element.attr('id')
			if(GroupManager.IsTrigger(element)){
				result.push(element.attr('id'));
			}
			else if(GroupManager.IsGroup(element)){
				var elements = element.children();
				$.each(elements, function(k,j){
					group.push($(this).attr('id'));
				});
				result.push(group);
			}
		});
	}
	return result;
};

/**
 * Initialize Popup.
 *
 * @since 3.4
 *
 * @param {string} popupId - Popup ID.
 * @param {string} popupTitle - Popup Title.
 * @param {string} popupClose - Popup Close Buttons.
 */
function initializeTestPopup( popupId, popupTitle, popupClose ) {
	jQuery( popupId ).dialog({
		title: popupTitle,
		dialogClass: 'wp-dialog',
		autoOpen: false,
		draggable: false,
		width: 600,
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
			jQuery( '.ui-widget-overlay' + popupClose ).bind( 'click', function() {
				jQuery( popupId ).dialog( 'close' );
			});
		},
		create: function() {

			// style fix for WordPress admin
			jQuery( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
		}
	});
}

/**
 * Bind Popup Trigger Button with Popup ID.
 *
 * @since 3.4
 *
 * @param {string} btnId - Test Popup Opener Button ID.
 * @param {string} popupId - Associated Popup ID.
 */
function bindTestPopupBtn( btnId, popupId ) {
	jQuery( btnId ).click( function() {
		resetTestPopup();
		jQuery( popupId ).dialog( 'open' );
	});
}

/**
 * Reset Test Notifications PopUp.
 *
 * @since 3.4
 */
function resetTestPopup() {
	jQuery( '#wsal-test-email' ).val( '' );
	jQuery( '#wsal-test-number' ).val( '' );
	jQuery( '#step-2 p.response' ).removeClass( 'error' );
	jQuery( '#step-2' ).hide();
	jQuery( '#step-1' ).show();
	jQuery( '#step-1' ).find( 'fieldset' ).removeAttr( 'disabled' );
}

/**
 * Send Test Notifications.
 *
 * @since 3.4
 */
function sendTestNotification() {
	var step1 = jQuery( '#step-1' );
	var step2 = jQuery( '#step-2' );
	var email = jQuery( '#wsal-test-email' ).val();
	var phone = jQuery( '#wsal-test-number' ).val();
	var loader = step1.find( '.loader' );

	if ( ! email && ! phone ) {
		alert( scriptData.emptyFieldsError );
		return;
	}

	step1.find( 'fieldset' ).attr( 'disabled', true );
	loader.show( 'fast' );

	jQuery.ajax({
		type: 'POST',
		url: scriptData.ajaxURL,
		async: true,
		dataType: 'json',
		data: {
			action: 'wsal_test_notifications',
			wsalSecurity: scriptData.scriptNonce,
			email: email,
			phone: phone
		},
		success: function( data ) {
			var response = step2.find( 'p.response' );
			loader.hide( 'fast' );
			response.html( data.message )
			step1.hide( 'fast' );
			step2.show( 'fast' );
			if ( false === data.success ) {
				response.addClass( 'error' );
			} else {
				// currentStep += 1;
				// loadNextWizardStep( currentStep );
			}
		},
		error: function( xhr, textStatus, error ) {
			console.log( xhr.statusText );
			console.log( textStatus );
			console.log( error );
		}
	});
}

/**
 * Send Trigger Builder Test Notifications.
 *
 * @since 3.4
 *
 * @param {string} notificationId - Notification ID.
 * @param {string} notificationType - Notification Type.
 */
function sendTriggerTestNotif( notificationId, notificationType ) {
	var loader = jQuery( scriptData.triggerTestPopupID + ' .loader' );
	var response = jQuery( scriptData.triggerTestPopupID + ' .response p' );
	response.parent().hide();
	response.empty();
	loader.show();
	jQuery( scriptData.triggerTestPopupID ).dialog( 'open' );

	// Send the AJAX request.
	jQuery.ajax({
		type: 'POST',
		url: scriptData.ajaxURL,
		data: {
			action: 'wsal_trigger_test_notification',
			wsalSecurity: scriptData.scriptNonce,
			notificationId: notificationId,
			notificationType: notificationType
		},
		success: function( htmlResponse ) {
			loader.hide();
			response.html( htmlResponse );
			response.parent().show();
		},
		error: function( xhr, textStatus, error ) {
			console.log( xhr.statusText );
			console.log( textStatus );
			console.log( error );
		}
	});
}

jQuery( document ).ready( function () {
	if ( 'undefined' !== typeof scriptData ) {
		initializeTestPopup( '#wsal-test-notif-dialog', scriptData.testPopupTitle, ',#close-test-notif,#cancel-test-notif' );
		bindTestPopupBtn( '#wsal-test-notifications', '#wsal-test-notif-dialog' );
		jQuery( '#send-test-notif' ).click( function() {
			sendTestNotification();
		});
		initializeTestPopup( scriptData.triggerTestPopupID, scriptData.triggerTestTitle, ',' + scriptData.triggerTestPopupID + ' .close' );
	}

	jQuery( '#is_url_shortner' ).change( function() {
		var accessToken = jQuery( this ).parents( 'fieldset' ).find( 'input[name="url_shortner_access_token"]' );
		if ( jQuery( this ).is( ':checked' ) ) {
			accessToken.removeAttr( 'disabled' );
		} else {
			accessToken.attr( 'disabled', true );
		}
	});
});
