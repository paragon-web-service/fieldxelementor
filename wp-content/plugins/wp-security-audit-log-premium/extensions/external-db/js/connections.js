(function( $) {
  $( document ).ready(function( ) {

    //  validation method for the hidden field indicating that connection requirements where checked and met
    $.validator.addMethod( 'requirements', function( value, element ) {
      return value === 'yes';
    }, 'Requirements are not met' );

    $.validator.addMethod( 'connectionTest', function( value, element ) {
      return value === 'yes';
    }, 'Connection test failed' );

    $.validator.addMethod( 'connection', function( value, element ) {
      return (25 >= value.length && /^[a-z\d\_]+$/i.test( value ));
    }, 'Invalid connection name' );

    $.validator.addMethod( 'cloudWatchGroupName', function( value, element ) {
      return this.optional( element ) || /^[\.\-_/#A-Za-z0-9]+$/i.test( value );
    }, 'Invalid CloudWatch group name' );

    $.validator.addMethod( 'port', function( value, element ) {
      return this.optional( element ) || /^([1-9]|[1-8][0-9]|9[0-9]|[1-8][0-9]{2}|9[0-8][0-9]|99[0-9]|[1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9]|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/i.test( value );
    }, 'Invalid port number' );

    $.validator.addMethod( 'ipAddress', function( value, element ) {
      return this.optional( element ) || /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d )){3}))|:)))(%.+)?\s*$/i.test( value );
    }, 'Invalid IP address' );

    $.validator.addMethod( 'papertrailLocation', function( value, element ) {
      return this.optional( element ) || /^[a-z\d]+(.papertrailapp.com:)[\d]+$/i.test( value );
    }, 'Invalid Papertrail location' );

    $.validator.addMethod( 'slackWebhook', function( value, element ) {
      return this.optional( element ) || /https:\/\/hooks.slack.com\/services\//i.test( value );
    }, 'Invalid Slack webhook' );

    function closeWizard(wizardId ) {
      $( wizardId ).dialog( 'close' );
    }

    function enableButton(buttonElm ) {
      $( buttonElm ).show();
      if ( 'INPUT' === buttonElm.prop( 'tagName' ) ) {
        $( buttonElm ).prop( 'disabled', false );
      } else {
        $( buttonElm ).removeClass( 'disabled' ).attr( 'aria-disabled', 'false' );
      }
    }

    function disableButton(buttonElm ) {
      $( buttonElm ).hide();
      if ( 'INPUT' === buttonElm.prop( 'tagName' ) ) {
        $( buttonElm ).prop( 'disabled', true );
      } else {
        $( buttonElm ).addClass( 'disabled' ).attr( 'aria-disabled', 'true' );
      }
    }

    function getVisibleSlide( stepsElm ) {
      return $( stepsElm ).find( '.body:visible' )
    }

    function getNextButton( slideElm ) {
      return $( slideElm ).closest( '.wizard' ).find( '.actions > ul > li:eq(1)' );
    }

    function processConditionallyRequiredFields( formElm ) {
      //  process all fields that are required only if some other field is checked
      $( formElm ).validate();
      $( formElm ).find( '[data-required-if]' ).each(function( ) {
        var inputElm = $( this );
        inputElm.rules( 'add', {
          required: '#' + inputElm.data( 'required-if' ) + ':checked'
        });
      });
    }

    function runRequirementsCheck( slideElm ) {
      slideElm.find( 'input' ).val( 'no' );
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.html( '<p><span class="spinner is-active"></span>' + wsalConnections.checking_requirements + '</p>' );

      $.ajax({
        type: 'POST',
        url: wsalConnections.ajaxURL,
        async: true,
        dataType: 'json',
        data: {
          action: 'wsal_check_requirements',
          nonce: slideElm.closest('form').find('input[name="_wpnonce"]').val(),
          type: slideElm.closest('form').find('select[name="connection[type]"]').val()
        },
        success: function( responseData ) {
          if ( responseData.success ) {
            requirementsCheckSuccessHandler( slideElm, responseData.data );
          } else {
            requirementsCheckFailureHandler( slideElm, responseData.data );
          }
        },
        error: function( xhr, textStatus, error ) {
          requirementsCheckFailureHandler( slideElm, wsalConnections.requirementsCheckFailed );
        }
      });
    }

    function runConnectionTest( slideElm ) {
      slideElm.find( 'input' ).val( 'no' );
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.html( '<p><span class="spinner is-active"></span>' + wsalConnections.sendingTestMessage + '</p>' );

      $.ajax({
        type: 'POST',
        url: wsalConnections.ajaxURL,
        async: true,
        dataType: 'json',
        data: {
          action: 'wsal_connection_test',
          nonce: slideElm.closest('form').find('input[name="_wpnonce"]').val(),
          config: slideElm.closest('form').serialize()
        },
        success: function( responseData ) {
          if ( responseData.success ) {
            var message = ( responseData.data ) ? responseData.data : wsalConnections.connSuccess;
            connectionTestSuccessHandler( slideElm, message );
          } else {
            connectionTestFailureHandler( slideElm, responseData.data );
          }
        },
        error: function( xhr, textStatus, error ) {
          connectionTestFailureHandler( slideElm, wsalConnections.connFailed );
        }
      });
    }

    function requirementsCheckSuccessHandler( slideElm, message ) {
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.find( '.spinner' ).remove( );
      progressPane.append( '<p>' + message + '</p>' );
      slideElm.find( 'input' ).val( 'yes' );
      enableButton( getNextButton( slideElm ) );
    }

    function requirementsCheckFailureHandler( slideElm, responseData ) {
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.find( '.spinner' ).remove( );
      slideElm.find( 'input' ).val( 'no' );
      progressPane.append( '<p>' + responseData.message + '</p>' );
      if ( responseData.errors ) {
        if ( responseData.errors.length === 1 && /<[a-z][\s\S]*>/i.test( responseData.errors[0] ) ) {
          progressPane.append( responseData.errors[0] );
        } else {
          progressPane.append('<ul>' + responseData.errors.map(function (error) {
            return '<li>' + error + '</li>';
          }) + '</ul>');
        }
      }
    }

    function connectionTestSuccessHandler( slideElm, message ) {
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.find( '.spinner' ).remove( );
      progressPane.append( '<p>' + message + '</p>' );
      slideElm.find( 'input' ).val( 'yes' );
      enableButton( getNextButton( slideElm ) );
    }

    function connectionTestFailureHandler( slideElm, message ) {
      var progressPane = slideElm.find( '.progress-pane' );
      progressPane.find( '.spinner' ).remove( );
      slideElm.find( 'input' ).val( 'no' );
      progressPane.append( '<p>' + message + '</p>' );
    }
    
    function validateFieldsInSlide( stepsElm, event, currentIndex ) {
      var validator = stepsElm.validate();
      validator.settings.ignore = ":disabled";

      //  check form fields on the current slide (unless they reside withing a "disabled" fieldset)
      var fields = getVisibleSlide( stepsElm ).find( 'input,select' ).filter(function() {
        var fieldset = $(this).closest('fieldset');
        return fieldset.length == 0 || fieldset.length > 0 && typeof fieldset.attr('disabled') === 'undefined';
      });

      var valid = true;
      if (fields.length > 0) {
        fields.each(function(){
          valid = validator.element(this ) && valid;
        });
      }

      return valid;
    }

    function autoAdjustSlideHeight() {
      $('.wizard .content').animate( {
        height: $('.body.current').outerHeight()
      }, "slow" );
    }

    // initialise the dialog
    function initializeWizard( wizardId, wizardTitle, initCallback ) {
      var stepsElm = $( wizardId ).find( 'form' );

      $( wizardId ).dialog({
        title: wizardTitle,
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: false,
        width: 750,
        modal: true,
        resizable: false,
        closeOnEscape: true,
        minHeight: 'auto',
        height: 'auto',
        position: {
          my: 'center',
          at: 'center',
          of: window
        },
        open: function() {

          // close dialog by clicking the overlay behind it
          $( '.ui-widget-overlay' ).bind( 'click', function() {
            closeWizard( wizardId );
          });

          //  reset wizard back to the first slide and clear all form fields
          if ( stepsElm.data( 'steps' ))  {
            resetWizardFields( wizardId );
            stepsElm.validate().resetForm();
            autoAdjustSlideHeight();
            return;
          }

          stepsElm.steps({
            headerTag: 'h3.step-title',
            bodyTag: 'div.step-content',
            enableCancelButton: true,
            transitionEffect: 'slideLeft',
            labels: {
              cancel: wsalConnections.cancelLabel,
              finish: wsalConnections.finishLabel,
              next: wsalConnections.nextLabel,
              previous: wsalConnections.previousLabel
            },
            onInit: function( event, currentIndex ) {
              if ( typeof initCallback === 'function' ) {
                initCallback.call( this, stepsElm );
              }

              processConditionallyRequiredFields( stepsElm );

              //  init live field validation
              $( stepsElm ).on( 'blur keyup change', 'input, select', function() {
                var visibleSlide = getVisibleSlide( stepsElm );
                var nextButton = getNextButton( visibleSlide );

                if ( stepsElm.validate().numberOfInvalids() === 0 ) {
                  if (jQuery('#connection-type').hasClass('invalid')) {
                    disableButton( nextButton );
                  } else {
                    enableButton( nextButton );
                  }
                } else {
                  validateFieldsInSlide( stepsElm, event, currentIndex );
                }
              });
            },
            onCanceled: function(event ) {
              closeWizard( wizardId );
            },
            onStepChanged: function( event, currentIndex, priorIndex ) {
              //  reset form validation to avoid issues when changing connection type
              stepsElm.validate().resetForm();

              var visibleSlide = getVisibleSlide( stepsElm );
              var nextButton = getNextButton( visibleSlide );
              if (priorIndex > currentIndex ) {
                //  just enable the next button when going to the previous slide
                enableButton(nextButton );
                return;
              }

              //  we need to disable the next button when proceeding to the next slide, unless there is a data attribute
              //  "data-next-enabled-by-default" that prevents this
              if ( getVisibleSlide( stepsElm ).data( 'next-enabled-by-default' ) !== 'yes' ) {
                disableButton(nextButton );
              }

              autoAdjustSlideHeight();

              //  custom work on some slides
              if ( currentIndex == 0 ) {
                //  make sure to trigger the connection type change to handle the hidden section in the settings slide
                $( '#connection-type' ).trigger( 'change' );
              } else if ( currentIndex == 1 ) {
                //  run requirements AJAX check
                runRequirementsCheck( visibleSlide );
              } else if ( currentIndex == 3 ) {
                //  run connection AJAX test
                runConnectionTest( visibleSlide );
              }
            },
            onStepChanging: function( event, currentIndex, newIndex )
            {
              if (newIndex < currentIndex ) {
                return true;
              }

              return validateFieldsInSlide( stepsElm, event, currentIndex );
            },
            onFinishing: function( event, currentIndex )
            {
              return validateFieldsInSlide( stepsElm, event, currentIndex );
            },
            onFinished: function( event, currentIndex )
            {
              stepsElm.validate().destroy();
              stepsElm.submit();
            }
          })
        },
        create: function() {
          // style fix for WordPress admin
          $( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
        },
        close: function (event, ui) {
          stepsElm.steps('reset');
        }
      });
    }

    function bindWizardBtn( btnId, wizardId ) {
      $( btnId ).click( function( e ) {
        e.preventDefault();
        $( wizardId ).dialog( 'open' );
      });
    }

    /**
     * Reset Wizard Fields.
     */
    function resetWizardFields(wizardId ) {
      $( wizardId ).find( 'form' ).trigger( 'reset' );
    }

    /**
     * Test Connection.
     */
    jQuery( '.wsal-conn-test' ).click( function( e ) {
      var connection = '';
      var nonce = '';
      e.preventDefault();

      var testBtn = jQuery( this );
      if ( testBtn.hasClass('disabled') ) {
        return false;
      }

      connection = testBtn.data( 'connection' );
      nonce = testBtn.data( 'nonce' );
      testBtn.text( wsalConnections.connTest );

      // Ajax request to test connection.
      jQuery.ajax({
        type: 'POST',
        url: wsalConnections.ajaxURL,
        async: true,
        dataType: 'json',
        data: {
          action: 'wsal_connection_test',
          nonce: nonce,
          connection: connection
        },
        success: function( responseData ) {
          if ( responseData.success ) {
            if ( responseData.data ) {
              testBtn.text( responseData.data );
            } else {
              testBtn.text( wsalConnections.connSuccess );
            }
          } else {
            testBtn.text( wsalConnections.connFailed );
          }
        },
        error: function( xhr, textStatus, error ) {
          jQuery( testBtn ).text( wsalConnections.connFailed );
        }
      });
    });

    /**
     * Delete Connection.
     */
    jQuery( '.wsal-conn-delete' ).click( function( e ) {
      var connection = '';
      var nonce = '';
      e.preventDefault();

      if ( ! confirm( wsalConnections.confirm ) ) {
        return;
      }

      connection = jQuery( this ).data( 'connection' );
      nonce = jQuery( this ).data( 'nonce' );
      jQuery( this ).text( wsalConnections.deleting );

      // Ajax request to delete connection.
      jQuery.ajax({
        type: 'POST',
        url: wsalConnections.ajaxURL,
        async: true,
        dataType: 'json',
        data: {
          action: 'wsal_delete_connection',
          nonce: nonce,
          connection: connection
        },
        success: function( data ) {
          if ( data.success ) {
            location.reload();
          }
        },
        error: function( xhr, textStatus, error ) {
          jQuery( btn ).val( 'Connection failed!' );
        }
      });
    });

    function initializeDbPrefixChangeHandler( formElm ) {
      // handle change of checkbox for using website URL as db prefix
      $( '#db-url-base-prefix' ).change( function() {
        var dbPrefixField = $( '#db-base-prefix' );
        if ( $( this ).is( ':checked' ) ) {
          dbPrefixField.attr( 'disabled', true );
          dbPrefixField.val( wsalConnections.urlBasePrefix );
        } else {
          dbPrefixField.attr( 'disabled', false );
          dbPrefixField.val( '' );
        }

        var validator = formElm.validate();
        validator.settings.ignore = "";
        validator.element( dbPrefixField[0] );
        validator.settings.ignore = ":disabled";

      } );
    }

    function initializeConnectionTypeChangeHandler( formElm ) {
      //  make sure only the correct part of configuration fields is displayed when connection type changes
      $( '#connection-type' ).change( function() {
        var allConfigSections = $( 'div[class^="details-"]' );
        allConfigSections.addClass( 'hide' );
        allConfigSections.find( 'fieldset' ).prop( 'disabled',  true );

        var activeSection = $( '.details-' + $( this ).val() );
        activeSection.removeClass( 'hide' );
        activeSection.find( 'fieldset' ).prop( 'disabled',  false );
      } );

      //  trigger initial connection type change with the pre-selected value
      $( '#connection-type' ).trigger( 'change' );
    }

    if ( '' !== wsalConnections.connection ) {
      //  we are on connection editing screen
      var formElm = $( '.js-wsal-connection-form' );
      initializeDbPrefixChangeHandler( formElm );
      processConditionallyRequiredFields( formElm );

      //  init live field validation
      formElm.on( 'blur keyup change', 'input, select', function() {
        var submitButton = formElm.find( 'input[type="submit"]' );
        var validator = formElm.validate();
        validator.form();
        if ( validator.numberOfInvalids() === 0 ) {
          enableButton( submitButton );
        } else {
          disableButton( submitButton );
        }
      });
    } else {
      //  we are connection list screen featuring new connection wizard
      initializeWizard( '#wsal-connection-wizard', wsalConnections.wizardTitle, function( formElm ) {
        initializeConnectionTypeChangeHandler( formElm );
        initializeDbPrefixChangeHandler( formElm );
      } );

      bindWizardBtn( '#wsal-create-connection', '#wsal-connection-wizard' );
    }

    // Ensure parent radio is checked if applicable.
    jQuery( 'body' ).on( 'click', '.subfield[type="radio"]', function ( e ) {
      var isChildOfRadio = jQuery( this ).parent().parent().find( 'input[type="radio"]' ).not( '.subfield' );
      if ( isChildOfRadio.length ) {
        isChildOfRadio.prop( 'checked', true );
      }
    });

    /** Show notification about missing external libraries based on current selection and their existence */
    jQuery('body').on( 'change','#connection-type', function (){
      var slideElm = getVisibleSlide( jQuery( '#wsal-connection-wizard' ).find( 'form' ));

      if (jQuery(this).find(':selected').data('notification') === 'show'){
        jQuery('.notice.notice-info.inline').show();
        
        disableButton( getNextButton( slideElm ) );
        jQuery(this).removeClass('valid');
        jQuery(this).addClass('invalid');

      } else {
        jQuery('.notice.notice-info.inline').hide();
        jQuery(this).removeClass('invalid');
        jQuery(this).addClass('valid');

        enableButton( getNextButton( slideElm ) );
      }
    });    
  });
}(jQuery ))