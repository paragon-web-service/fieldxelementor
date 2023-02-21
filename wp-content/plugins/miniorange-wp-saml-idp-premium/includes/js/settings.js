jQuery(document).ready(function () {
    $idp = jQuery;

    // when the service provider dropdown value changes then
    // submit the change service provider form
    // so that the current service provider can be set
    $idp("select[name='service_provider']").change(function(){
        $idp("input[name='service_provider']").val($idp(this).val());
        $idp("#change_sp").submit();
    });

    // clicking any element with mo_idp_help_title class will trigger a
    // slidetoggle animation on nearest element having the mo_idp_help_desc
    // class
    $idp(".mo_idp_help_title").click(function(e){
        e.preventDefault();
        $idp(this).next('.mo_idp_help_desc').slideToggle(400);
    });

    $idp(".mo_idp_checkbox").click(function(){
        $idp(this).next('.mo_idp_help_desc').slideToggle(400);
    });

    $idp("#lk_check1").change(function(){
        if($idp("#lk_check2").is(":checked") && $idp("#lk_check1").is(":checked")){
            $idp("#activate_plugin").removeAttr('disabled');
        }
    });

    $idp("#lk_check2").change(function(){
        if($idp("#lk_check2").is(":checked") && $idp("#lk_check1").is(":checked")){
            $idp("#activate_plugin").removeAttr('disabled');
        }
    });

    // this is the ribbon styletopbar to choose between protocols
    $idp("div[class^='protocol_choice_'").click(function(){
        if(!$idp(this).hasClass("selected")){
            $idp(this).parent().parent().next("form").fadeOut();
            $idp("#add_sp input[name=\"action\"]").val($idp(this).data('toggle'));
            $idp(".loader").fadeIn();
            $idp("#add_sp").submit();
        }
    });

    // any element with copyClip class will
    // copy the text in the element having
    // copyBody class
    $idp(".copyClip").click(function(){
        $idp(this).next(".copyBody").select();
        document.execCommand('copy');
    });

    $idp('a[aria-label="Deactivate Login using WordPress Users"]').click(function(e){
        $idp("#mo_idp_feedback_modal").show();
        e.preventDefault();
    });

    // user is trying to remove his account
    $idp('#remove_accnt').click(function(e){
        $idp("#remove_accnt_form").submit();
    });

    // admin is refreshing license
    $idp("#refresh_sp_user").click(function(){
        $idp("#refresh_sp_users_form").submit()
    });

    // admin is trying to goback to the login page
    $idp("#goToLoginPage").click(function (e) {
        $idp("#goToLoginPageForm").submit();
    })
});

function showTestWindow(url) {
    var myWindow = window.open(url, "TEST SAML IDP", "scrollbars=1 width=800, height=600");
}

function deleteSpSettings() {
    jQuery("#mo_idp_delete_sp_settings_form").submit();
}

function mo_valid_query(f) {
    !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
        /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
}

function mo2f_upgradeform(planType){
    jQuery("#requestOrigin").val(planType);
    jQuery("#mocf_loginform").submit();
}

function submitExportForm() {
    jQuery("#export_config_form").submit();
}

function showSamlManualPage() {
    jQuery("#samlProviderManual").show();
    jQuery("#samlProviderUpload").hide();
}

function showSamlUploadPage() {
    jQuery("#samlProviderManual").hide();
    jQuery("#samlProviderUpload").show();
}

function showGenerateDiv() {
    jQuery("#generateCertiDiv").show();
    jQuery("#customCertiDiv").hide();
}

function showCustomDiv(){
    jQuery("#generateCertiDiv").hide();
    jQuery("#customCertiDiv").show();
}

function submitResetForm() {
    jQuery("#reset_certi_form").submit();
}

function copyToClipboard(copyButton, element, copyelement) {
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    temp.remove();
    jQuery(copyelement).text("Copied");

    jQuery(copyButton).mouseout(function(){
        jQuery(copyelement).text("Copy to Clipboard");
    });
}

function toggleContactForm() {
    var contact_text = jQuery(".mo-idp-contact-container");
    var contact_form = jQuery("#idp-contact-button-form");
    if(contact_text.is(":hidden")){
        contact_text.show();
        contact_form.slideToggle();
    } else {
        contact_text.hide();
        contact_form.slideToggle();
    }
}

//------------------------------------------------------
// FEEDBACK FORM FUNCTIONS
//------------------------------------------------------

//feedback forms stuff
function mo_idp_feedback_goback() {
    $idp("#mo_idp_feedback_modal").hide();
}

function gatherplaninfo(name,users){
    document.getElementById("plan-name").value=name;
    document.getElementById("plan-users").value=users;
    document.getElementById("mo_idp_request_quote_form").submit();
}

function SelectAll()
{
    var elements = document.getElementsByClassName('roles');

    if(document.getElementById('select-all').checked)
    {
        for(var i = 0; i < elements.length; i++) {
            document.getElementById("roles"+ i ).checked = true;
            document.getElementById('hidden_select-all').value = 1 ;          
        }
    }
    else
    {
        for(var i = 0; i < elements.length; i++) {
            document.getElementById("roles"+ i ).checked = false;
            document.getElementById('hidden_select-all').value = 0 ;
        }
    }
}

function UnCheckParent()
{
    var elements = document.getElementsByClassName('roles');
    if(document.getElementsByClassName('roles').checked != true)
    document.getElementById('select-all').checked = false ;

    for(var i = 0; i < elements.length; i++) {
        if(document.getElementById("roles"+ i ).checked == false)
        {
            document.getElementById('select-all').checked = false ;
            document.getElementById('hidden_select-all').value = 0 ;
            break;
        }
        else{
            document.getElementById('select-all').checked = true ; 
            document.getElementById('hidden_select-all').value = 1 ;          
        }
    }
}

function GiveRestrictedRolesArray()
{
    var elements = document.getElementsByClassName('roles');
    var size = elements.length;
    var roles = [] ;
    for(var i = 0 ; i < elements.length ; i++)
    {
        if(document.getElementById("roles"+i).checked != false){
            roles.push(document.getElementById("roles"+i).name);
        }
    }

    document.getElementById('allowed_roles').value = roles;
}