/**
 * Users Sessions Management Script.
 *
 */
jQuery(document).ready(function () {
	jQuery("h2:first").after('<div id="msg-busy-page"></div>');

	// Tab handling code.
	jQuery('#wsal-tabs>a').click(function () {
		jQuery('#wsal-tabs>a').removeClass('nav-tab-active');
		jQuery('div.wsal-tab').hide();
		jQuery(jQuery(this).addClass('nav-tab-active').attr('href')).show();
	});

	// Show relevant tab.
	var hashlink = jQuery('#wsal-tabs>a[href="' + location.hash + '"]');
	if (hashlink.length) {
		hashlink.click();
	} else {
		jQuery('#wsal-tabs>a:first').click();
	}

	jQuery('form input[type=checkbox]').not( '.bulk-checkbox' ).unbind('change').change(function () {
		var current = this.name + 'Emails';
		if (jQuery(this).is(':checked')) {
			jQuery('#' + current).prop('required', true);
		} else {
			jQuery('#' + current).removeProp('required');
		}
	});

	/**
	 * Destroy Session.
	 *
	 * @since 3.1
	 */
	jQuery('.wsal_destroy_session').click(function (event) {
		event.preventDefault();

		if ( window.confirm( script_data.sessionWarning ) == false ) {
			return;
		}

		jQuery(this).text( script_data.loggingOut );
		jQuery(this).attr('disabled', 'disabled');
		var session_data = {
			action: jQuery(this).data('action'),
			user_id: jQuery(this).data('user-id'),
			token: jQuery(this).data('token'),
			wpnonce: jQuery(this).data('wpnonce'),
		}

		WsalDestroySession(jQuery(this), session_data);
	});

	// Select option on focus.
	jQuery('#multi-sessions-limit').focus(function (event) {
		jQuery('#allow-limited').attr('checked', 'checked');
	});
	jQuery('#session_override_pass').focus(function (event) {
		jQuery('#with_warning').attr('checked', 'checked');
	});

	/**
	 * Sessions Blocked Option.
	 *
	 * @since 3.1.4
	 */
	var session_blocked = jQuery('#wsal_blocked_session_override fieldset');
	jQuery('input[name=MultiSessions]').change(function (event) {
		var checked = jQuery(this).val();
		if (checked === '1') {
			session_blocked.removeAttr('disabled');
		} else {
			session_blocked.attr('disabled', 'disabled');
		}
	});

	jQuery( '.wsal_fetch_users_event_data' ).bind( 'click', function() {
		wsal_get_users_session_event_data( this );
	} );


	// watch for main checkbox changes to determine enabled/disabled states.
	jQuery( '#policies_enabled ,#policies_inherited, #exclude_role' )
		.change(
			function() {

				var settingsContainer = jQuery( '#sessions-policy-settings' );
				var disabled          = wsal_should_sessions_inputs_be_disabled();

				// add/remove the wrapper class.
				if ( true === disabled ) {
					jQuery( settingsContainer ).addClass( 'disabled' );
				} else {
					jQuery( settingsContainer ).removeClass( 'disabled' );
				}

				// put the inputs intop either enabled/disabled state.
				jQuery( settingsContainer )
					.find( 'input, select, textarea' )
					.each(
						function() {
							jQuery( this ).prop( 'disabled', disabled );
						}
					);

			}
		);
	// trigger a chance on these items on dom Ready so inputs are put to correct
	// state on load.
	jQuery( '#policies_enabled, #policies_inherited, #exclude_role' ).change();
	
	// Terminate all sessions found within a search query.
	jQuery( document ).on( 'click', '.terminate-session-for-query', function ( event ) {
		event.preventDefault();

		if ( window.confirm( script_data.sessionWarning ) == false ) {
			return;
		}

		var userData = jQuery( this ).data( 'users-to-terminate' );
		var progressSpan = '<strong class="terminate-count" data-termination-current-count="' + userData.length + '">' + userData.length + '</strong>';
		jQuery( '.terminate-query-progress' ).html( script_data.remainingSessions + ' ' + progressSpan );		

		jQuery.each( userData, function( key, value ) {
			var session_data = {
				action: 'destroy_session',
				user_id: value[0],
				token:   value[1],
				wpnonce: value[2],
			}
			WsalDestroySession(jQuery(this), session_data);
		}); 
		
	} );

	jQuery( '#wsal_show_multiple_sessions_only' ).on( 'change', function() {
		var nonce = jQuery( this ).data( nonce );
		var enabled = jQuery(this)[0].checked;
		wsal_ajax_show_multiple_sessions_only( nonce, enabled );
	});

	// Bulk actions.
	jQuery( document ).on( 'click', '#do-session-bulk-action', function ( event ) {
		event.preventDefault();

		var Action  = jQuery( '#bulk-action-selector-top' ).val();
		var UserIDs = [];
		jQuery( '.bulk-checkbox:checked').each(function( index ) {
			UserIDs.push( jQuery( this ).val() );
		});

		if ( UserIDs.length > 0 && Action == 'terminate-sessions' ) {

			if ( window.confirm( script_data.sessionWarning ) == false ) {
				return;
			}

			jQuery( UserIDs ).each(function( index ) {
				var session_data = {
					action: 'destroy_session',
					user_id: UserIDs[index],
					token: jQuery( '.bulk-checkbox[value="'+UserIDs[index]+'"]' ).closest( 'tr' ).attr( 'id' ),
					wpnonce: jQuery( '.bulk-checkbox[value="'+UserIDs[index]+'"]' ).data( 'bulk-destroy-nonce' ),
				}
			    WsalDestroySession( jQuery(this), session_data );
			});

		} else if ( UserIDs.length > 0 && Action == 'retrieve-events' ) {
			var session_ids = [];
			var nonce  = jQuery( 'option[value="'+ Action +'"]' ).data( 'nonce' );

			jQuery( UserIDs ).each(function( index ) {
				session_ids.push({
					session: jQuery( '.bulk-checkbox[value="'+UserIDs[index]+'"]' ).closest( 'tr' ).attr( 'id' ), 
					user: jQuery.trim( jQuery( '.bulk-checkbox[value="'+UserIDs[index]+'"]' ).closest( 'tr' ).find( '.wsal_session_user' ).data( 'login' ) )
				});
			});
			wsal_ajax_fetch_session_event_data( nonce, session_ids, 10, 0 );
		}

		// Small timeout as if feel way to abrupt otherwise.
		setTimeout(function(){
			jQuery( '.bulk-checkbox:checked').each(function( index ) {
				jQuery(this).prop("checked", false); 
			});
			jQuery( '.session-bulk-check' ).prop("checked", false);
			jQuery('#bulk-action-selector-top').prop('selectedIndex',0);
		}, 200);
	});

    jQuery( '.session-bulk-check' ).change(function() {
		var Checked = this.checked;
        if(this.checked) {
            jQuery(this).prop("checked", true); 
		}
		jQuery( '.bulk-checkbox').each(function( index ) {
			jQuery(this).prop( "checked", Checked ); 
		});
        jQuery( '.session-bulk-check' ).val(this.checked);        
    });
});

function Refresh() {
	location.reload();
}

function WsalSsasChange(value) {
	jQuery('#wsal-cbid').val(value);
	jQuery('#sessionsForm').submit();
}

var validateEmail = function (value) {
	return /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(value);
};

jQuery('form').submit(function () {
	var res = true;

	jQuery(".emailsAlert").each(function () {
		var emailStr = jQuery(this).val().trim();
		if (emailStr != "") {
			var emails = emailStr.split(/[;,]+/);
			for (var i in emails) {
				var email = jQuery.trim(emails[i]);
				if (!validateEmail(email)) {
					jQuery(this).addClass("error");
					res = false;
				} else {
					jQuery(this).removeClass("error");
				}
			}
		}
	})
	return res;
});

/**
 * Auto Refresh Sessions Page.
 *
 * @param {string} dataSessions - JSON information string about sessions.
 */
function SessionAutoRefresh( dataSessions ) {
	var data = jQuery.parseJSON( dataSessions );

	var current_token = data.token;
	var blog_id       = data.blog_id;
	var nonce         = data.session_nonce;

	var SessionsChk = function () {
		var is_page_busy = false;

		// Try to detect user activity on the page via mouse movement.
		jQuery( 'body' ).mousemove(
			function (event) {
				is_page_busy = true;
			}
		);

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			data: {
				action: 'wsal_usersession_auto_refresh',
				sessions_count: current_token,
				blog_id: blog_id,
				session_nonce: nonce
			},
			success: function (result) {
				// if we have a result with a refresh key...
				if (result && result.data.refresh) {
					current_token = result;
					// if the page is not busy (indicated by mouse movement) then refresh.
					if ( ! is_page_busy) {
						location.reload();
					} else {
						// Page is busy show a notice that sessions changed.
						// TODO: this string needs localized.
						var msg = '<p>New session. Please press <a href="javascript:Refresh();">Refresh</a></p>';
						jQuery( '#msg-busy-page' ).html( msg ).addClass( 'updated' );
					}
				}
			}
		});
	};
	SessionsChk(); // Refresh once the page loads.
	setInterval( SessionsChk, 60000 ); // Then refresh sessions every 60 seconds.
}

/**
 * Destroy Individual Session.
 *
 * @since 3.1
 */
function WsalDestroySession(btn, session_data) {
	if (!session_data) {
		return false;
	}

	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		async: true,
		dataType: 'json',
		data: {
			action: session_data.action,
			user_id: session_data.user_id,
			token: session_data.token,
			nonce: session_data.wpnonce,
		},
		success: function (response) {
			if (!response.success) {
				console.log(response.message);
			} else {
				if ( jQuery( '.terminate-count' ).length ) {
					// Track count if terminating several at once.
					var countElement = jQuery( '.terminate-count' );
					var currentCount = countElement.data( 'termination-current-count' );
					var currentCount = currentCount - 1;

					countElement.data( 'termination-current-count', currentCount );
					countElement.text( currentCount );

					if ( currentCount == 0 ) {
						window.location.search += "&sessions-terminated=true";
					}

				} else {
					btn.text( script_data.refreshing );
					Refresh();
				}
			}
		},
		error: function (xhr, textStatus, error) {
			console.log(xhr.statusText);
			console.log(textStatus);
			console.log(error);
		}
	});
}

/**
 * Terminate all sessions.
 *
 * @param {integer} security - Security nonce.
 */
function terminate_all_sessions( security, sessions_deleted = 0 ) {
	var progress = jQuery( '#wsal-termination-progress' );

	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		async: true,
		dataType: 'json',
		data: {
			action: 'wsal_terminate_all_sessions',
			nonce: security,
			sessions_deleted: sessions_deleted
		},
		success: function( response ) {
			if ( response.success ) {
				var sessions_deleted_total = response.sessions_deleted;
				progress.append('<p>' + sessions_deleted_total + ' ' + script_data.sessionsTerminated + '</p>')

				var process_completed = ( response.completed && 'yes' === response.completed );
				if ( !process_completed ) {
					terminate_all_sessions(security, sessions_deleted_total)
				} else {
					progress.append('<p>' + script_data.refreshing + '</p>')
					jQuery(window).off('beforeunload')

					setTimeout(function () {
						var url = window.location.href
						url = url.replace(/[\?&]terminate=[^&]+/, '').replace(/^&/, '?')
						url = url.replace(/[\?&]terminate_security=[^&]+/, '').replace(/^&/, '?')
						window.location.href = url
					}, 1000)
				}
			} else {
				console.log( response.message );
			}
		},
		error: function( xhr, textStatus, error ) {
			console.log(xhr.statusText);
			console.log(textStatus);
			console.log(error);
		}
	});
}

/**
 * Terminate all sessions but the admin.
 *
 * @param {integer} security - Security nonce.
 */
function terminate_all_sessions_but_mine( security, sessions_deleted = 0 ) {
	var progress = jQuery( '#wsal-termination-progress' );

	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		async: true,
		dataType: 'json',
		data: {
			action: 'wsal_terminate_all_sessions_but_mine',
			nonce: security,
			sessions_deleted: sessions_deleted
		},
		success: function( response ) {
			if ( response.success ) {
				var sessions_deleted_total = response.sessions_deleted;
				progress.append('<p>' + sessions_deleted_total + ' ' + script_data.sessionsTerminatedNoMine + '</p>')

				var process_completed = ( response.completed && 'yes' === response.completed );
				if ( !process_completed ) {
					terminate_all_sessions_but_mine(security, sessions_deleted_total)
				} else {
					progress.append('<p>' + script_data.refreshing + '</p>')
					jQuery(window).off('beforeunload')

					setTimeout(function () {
						var url = window.location.href
						url = url.replace(/[\?&]terminate=[^&]+/, '').replace(/^&/, '?')
						url = url.replace(/[\?&]terminate_security=[^&]+/, '').replace(/^&/, '?')
						window.location.href = url
					}, 1000)
				}
			} else {
				console.log( response.message );
			}
		},
		error: function( xhr, textStatus, error ) {
			console.log(xhr.statusText);
			console.log(textStatus);
			console.log(error);
		}
	});
}

/**
 * Click handler that initiates event data fetching.
 *
 * Gets 10 at a time and loops till all data is fetched.
 */
function wsal_get_users_session_event_data( button ) {
	var nonce       = jQuery( button ).data( 'nonce' );
	var session_ids = jQuery( '.username.column-username' )
		.map(
			function() {
				return {
					'user' : jQuery.trim( jQuery( this ).find( '.wsal_session_user' ).data( 'login' ) ),
					'session' : jQuery.trim( jQuery( this ).find( '.user_session_id' ).text() )
				};
			}
		).get();
	// update the data on the page.
	jQuery( button ).text( script_data.fetchEventStrings.buttonRunning );
	jQuery( button ).attr( 'disabled', true );
	jQuery( '.fetch-progress-spinner' ).addClass( 'is-active' );
	var limit = 10;
	var step  = 0;
	wsal_ajax_fetch_session_event_data( nonce, session_ids, limit, step );

}

/**
 * Performs the ajax action which fetches data then on succes pushes it to the page.
 */
 function wsal_ajax_fetch_session_event_data( nonce, session_ids, limit, step ) {
	jQuery.ajax(
		{
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: {
				action: 'wsal_fetch_session_event_data_chunk',
				nonce: nonce,
				sessions: session_ids,
				limit: limit,
				step: step,
			},
			success: function( response ) {
				if ( response.success ) {
					// update page data.
					if ( response.data.data ) {
						wsal_update_session_event_data_in_page( response.data.data );
					}
					// make the next request or update button as finished.
					if ( response.data.step ) {
						wsal_ajax_fetch_session_event_data( nonce, session_ids, limit, response.data.step );
					} else {
						jQuery( '.wsal_fetch_users_event_data' ).text( script_data.fetchEventStrings.buttonFinished );
						jQuery( '.fetch-progress-spinner' ).removeClass( 'is-active' );
					}
				} else {
					console.log( response );
				}
			},
			error: function( xhr, textStatus, error ) {
				console.log(xhr.statusText);
				console.log(textStatus);
				console.log(error);
			}
		}
	);
}

function wsal_ajax_show_multiple_sessions_only( nonce, enabled ) {
	jQuery( '.show_multiple_sessions_label' ).text( script_data.refreshingScreenString );
	jQuery.ajax(
		{
			type: 'POST',
			url: ajaxurl,
			async: true,
			dataType: 'json',
			data: {
				action: 'wsal_show_multiple_sessions_only',
				nonce: nonce,
				enabled: enabled
			},
			success: function( response ) {
				console.log( response );
				if ( response.success ) {
					location.reload();
				} else {
					console.log( response );
				}
			},
			error: function( xhr, textStatus, error ) {
				console.log(xhr.statusText);
				console.log(textStatus);
				console.log(error);
			}
		}
	);
}

function wsal_update_session_event_data_in_page( data ) {
	for (var key in data) {
		// skip loop if the property is from prototype.
		if ( ! data.hasOwnProperty( key ) ) {
			continue;
		}
		jQuery( '#' + key ).find( 'td.ip.column-ip .fetch_placeholder' ).text( data[key].client_ip );
		var details = script_data.fetchEventStrings.idString + data[key].event_id + '<br>'
			+ script_data.fetchEventStrings.objectString + data[key].object.charAt( 0 ).toUpperCase() + data[key].object.slice(1)
			+ '<br>' + script_data.fetchEventStrings.eventTypeString + data[key].event_type.charAt( 0 ).toUpperCase() + data[key].event_type.slice(1) + '<br>';
		jQuery( '#' + key ).find( 'td.alert.column-alert .fetch_placeholder' ).after( details );
		jQuery( '#' + key ).find( 'td.alert.column-alert .fetch_placeholder' ).remove();
	}
}

/**
 * Determine if the inputs on the sessions settings page should be disabled or
 * not based on state of the switches/checkboxes.
 *
 * @method wsal_should_sessions_inputs_be_disabled
 * @since  4.1.0
 * @return bool
 */
function wsal_should_sessions_inputs_be_disabled() {
	var disabled = false;
	// determine if one of the checkboxes is in the state to disable
	// the other inputs.
	var masterSwitch  = jQuery( '#policies_enabled' ).prop( 'checked' ); // false = disable inputs.
	var inheritSwitch = jQuery( '#policies_inherited' ).prop( 'checked' ); // false = disable inputs.
	var excludeSwtich = jQuery( '#exclude_role' ).prop( 'checked' ); // true = disable inputs.
	if ( false === masterSwitch || true === inheritSwitch || true === excludeSwtich ) {
		// one of the checboxes disables inputs.
		disabled = true;
	}
	return disabled;
}

