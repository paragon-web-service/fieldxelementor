<?php


namespace IDP;

use IDP\Actions\ManageUserTableViewAction;
use IDP\Actions\RegistrationActions;
use IDP\Actions\SettingsActions;
use IDP\Actions\SSOActions;
use IDP\Actions\UpdateFrameworkActions;
use IDP\Handler\SupportHandler;
use IDP\Helper\Constants\MoIdPDisplayMessages;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MenuItems;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\RewriteRules;
final class MoIDP
{
    use Instance;
    private function __construct()
    {
        $this->initializeGlobalVariables();
        $this->initializeActions();
        $this->addHooks();
        $this->addSecondaryHooks();
        $this->addShortCodeHooks();
    }
    function initializeGlobalVariables()
    {
        global $dbIDPQueries;
        $dbIDPQueries = MoDbQueries::instance();
    }
    function addHooks()
    {
        add_action("\155\157\x5f\x69\144\160\x5f\x73\150\x6f\167\x5f\x6d\x65\x73\163\x61\x67\145", array($this, "\x6d\x6f\x5f\163\x68\157\x77\137\155\145\163\163\x61\147\145"), 1, 2);
        add_action("\x61\144\x6d\151\x6e\x5f\155\x65\x6e\165", array($this, "\x6d\x6f\x5f\x69\x64\160\x5f\155\x65\x6e\x75"));
        add_action("\141\144\x6d\x69\x6e\137\x65\x6e\x71\x75\145\x75\145\x5f\x73\x63\162\x69\160\164\x73", array($this, "\155\157\x5f\151\144\x70\137\160\154\x75\x67\151\156\x5f\x73\x65\164\x74\151\156\x67\163\x5f\x73\x74\x79\x6c\145"));
        add_action("\141\x64\x6d\151\x6e\x5f\145\156\161\165\145\x75\x65\137\163\x63\x72\151\160\x74\163", array($this, "\x6d\157\x5f\151\144\160\x5f\160\154\165\x67\x69\x6e\137\163\145\x74\x74\151\x6e\x67\163\x5f\163\x63\162\x69\x70\x74"));
        add_action("\145\x6e\x71\165\145\165\145\x5f\x73\143\162\x69\x70\x74\163", array($this, "\x6d\x6f\x5f\x69\x64\160\137\x70\x6c\165\x67\151\x6e\x5f\x73\x65\x74\x74\x69\x6e\147\x73\137\163\164\171\154\x65"));
        add_action("\x65\156\161\x75\145\x75\145\x5f\163\x63\162\151\160\164\x73", array($this, "\x6d\157\x5f\151\x64\160\137\160\154\x75\x67\151\156\137\x73\145\x74\164\151\156\147\163\x5f\x73\143\162\x69\160\x74"));
        add_action("\141\144\x6d\151\x6e\137\146\x6f\157\x74\145\162", array($this, "\146\145\x65\x64\142\x61\143\x6b\137\162\x65\161\x75\145\163\164"));
        add_filter("\x70\154\x75\147\151\x6e\x5f\141\143\164\151\x6f\x6e\x5f\154\151\x6e\x6b\x73\x5f" . MSI_PLUGIN_NAME, array($this, "\x6d\x6f\137\151\144\160\137\160\x6c\165\147\x69\156\x5f\141\x6e\x63\x68\157\162\x5f\x6c\x69\156\153\163"));
        register_activation_hook(MSI_PLUGIN_NAME, array($this, "\x6d\157\137\x70\154\x75\147\151\156\137\141\143\164\151\166\141\164\145"));
    }
    function initializeActions()
    {
        RewriteRules::instance();
        SettingsActions::instance();
        RegistrationActions::instance();
        SSOActions::instance();
        ManageUserTableViewAction::instance();
        if (!(MoIDPUtility::micr() && MoIDPUtility::iclv())) {
            goto C0;
        }
        UpdateFrameworkActions::instance();
        C0:
    }
    function mo_idp_menu()
    {
        new MenuItems($this);
    }
    function mo_sp_settings()
    {
        include "\x63\x6f\x6e\x74\x72\157\154\x6c\x65\162\x73\57\x73\163\157\x2d\155\141\151\156\x2d\143\x6f\x6e\x74\x72\x6f\x6c\x6c\145\x72\x2e\x70\x68\160";
    }
    function mo_idp_plugin_settings_style()
    {
        wp_enqueue_style("\x6d\157\x5f\x69\x64\160\137\x61\x64\x6d\x69\156\137\163\145\x74\164\151\x6e\147\x73\137\163\164\x79\x6c\x65", MSI_CSS_URL);
        wp_enqueue_style("\167\x70\x2d\160\x6f\151\156\x74\x65\162");
    }
    function mo_idp_plugin_settings_script()
    {
        wp_enqueue_script("\155\157\x5f\151\x64\160\137\141\144\x6d\x69\156\x5f\163\x65\x74\x74\x69\156\x67\x73\137\x73\x63\162\151\160\x74", MSI_JS_URL, array("\152\161\x75\145\x72\171"));
    }
    function mo_plugin_activate()
    {
        global $dbIDPQueries;
        $dbIDPQueries->checkTablesAndRunQueries();
        if (!(get_site_option("\x6d\157\x5f\x69\x64\160\137\x6b\145\x65\x70\x5f\x73\145\x74\x74\x69\156\147\x73\x5f\151\x6e\x74\x61\x63\x74", NULL) === NULL)) {
            goto vd;
        }
        update_site_option("\155\x6f\x5f\151\144\x70\x5f\153\145\145\x70\137\163\x65\x74\x74\x69\156\x67\x73\137\x69\156\164\x61\x63\164", TRUE);
        vd:
    }
    function mo_show_message($Rp, $ZV)
    {
        new MoIdPDisplayMessages($Rp, $ZV);
    }
    function feedback_request()
    {
        include MSI_DIR . "\x63\157\x6e\x74\162\x6f\x6c\x6c\x65\162\x73\57\146\x65\145\144\142\x61\x63\x6b\56\x70\150\160";
    }
    function addSecondaryHooks()
    {
        add_action("\x66\x6c\165\163\150\x5f\x63\x61\x63\150\x65", array($this, "\x66\154\165\x73\150\x5f\x63\x61\143\x68\145"), 1);
        add_action("\163\164\x61\162\x74\144\160\162\157\x63\x65\163\163", array($this, "\163\164\x61\x72\164\x64\160\162\x6f\143\x65\x73\163"), 1);
        register_deactivation_hook(MSI_PLUGIN_NAME, array($this, "\x6d\x6f\137\x69\144\x70\x5f\144\145\x61\x63\164\x69\166\x61\x74\x65"));
    }
    function addShortCodeHooks()
    {
        add_shortcode("\155\x6f\137\x73\160\x5f\x6c\151\x6e\153", array($this, "\155\157\137\151\144\160\x5f\163\x68\x6f\162\164\143\x6f\x64\145"));
        add_shortcode("\x6d\157\x5f\x6a\x77\x74\137\x6c\151\156\x6b", array($this, "\155\x6f\x5f\x69\144\x70\x5f\152\167\x74\x5f\163\x68\x6f\162\x74\143\x6f\144\145"));
    }
    function mo_idp_shortcode($MP = null)
    {
        if (!(!MoIDPUtility::micr() || !MoIDPUtility::iclv())) {
            goto sr;
        }
        return "\x50\154\165\x67\151\x6e\x20\156\157\x74\x20\143\157\x6e\146\151\x67\165\x72\x65\144\x2e\x20\x50\x6c\x65\x61\163\x65\x20\x63\157\x6e\x74\x61\x63\164\x20\171\x6f\x75\x72\x20\163\151\164\x65\x20\141\144\x6d\151\x6e\151\x73\164\162\141\164\x6f\162\x2e";
        sr:
        if (is_user_logged_in()) {
            goto M8;
        }
        $Kn = "\74\141\40\x68\162\145\x66\75" . wp_login_url(get_permalink()) . "\76\114\x6f\147\40\151\156\74\x2f\141\76";
        goto gN;
        M8:
        if (!MoIDPUtility::isBlank($MP)) {
            goto BZ;
        }
        $Kn = "\x53\x68\157\162\x74\103\157\144\x65\40\x48\141\163\x6e\47\x74\40\142\x65\145\156\40\163\x65\164\40\160\162\x6f\160\x65\162\x6c\x79\x2e";
        goto uQ;
        BZ:
        $Kn = "\74\141\40\150\x72\145\x66\75\x22" . site_url() . "\x2f\x3f\157\x70\x74\151\x6f\156\75\163\x61\x6d\x6c\x5f\165\163\x65\x72\137\154\x6f\x67\151\x6e\x26\x73\160\x3d" . $MP["\x73\160"] . "\46\x72\145\154\141\x79\x53\x74\x61\164\x65\x3d" . $MP["\162\x65\154\x61\171\163\x74\x61\x74\x65"] . "\x22\76\xd\xa\x20\40\40\x20\x20\40\x20\40\x20\x20\x20\x20\40\40\x20\40\x20\x20\40\x20\x4c\x6f\147\x69\156\40\164\x6f\40" . $MP["\163\x70"] . "\74\x2f\141\x3e";
        uQ:
        gN:
        return $Kn;
    }
    function mo_idp_jwt_shortcode($MP = null)
    {
        if (!(!MoIDPUtility::micr() || !MoIDPUtility::iclv())) {
            goto Sv;
        }
        return "\120\x6c\x75\147\x69\x6e\x20\x6e\x6f\x74\40\x63\x6f\x6e\146\151\x67\165\162\145\x64\56\40\120\x6c\145\x61\x73\x65\x20\x63\x6f\156\x74\141\143\x74\x20\x79\x6f\x75\x72\x20\163\x69\164\145\x20\141\144\155\x69\156\151\163\x74\x72\141\x74\x6f\162\56";
        Sv:
        if (is_user_logged_in()) {
            goto qz;
        }
        $Kn = "\74\141\x20\x68\162\145\146\x3d" . wp_login_url(get_permalink()) . "\76\114\157\x67\40\x69\156\x3c\x2f\141\76";
        goto DR;
        qz:
        if (!MoIDPUtility::isBlank($MP)) {
            goto zK;
        }
        $Kn = "\x53\x68\x6f\162\164\x43\157\x64\145\40\x48\141\x73\x6e\47\164\x20\x62\145\x65\x6e\x20\163\x65\x74\x20\x70\x72\x6f\x70\145\162\154\171\56";
        goto Mo;
        zK:
        $Kn = "\x3c\141\x20\150\x72\145\146\x3d\42" . site_url() . "\x2f\x3f\157\160\x74\151\157\x6e\75\152\x77\164\137\154\157\147\x69\x6e\x26\x73\x70\x3d" . $MP["\x73\160"] . "\x26\162\145\154\141\171\x53\x74\141\164\145\75" . $MP["\x72\x65\x6c\141\x79\163\164\x61\164\x65"] . "\x22\x3e\15\12\40\40\40\x20\x20\x20\40\40\40\40\40\40\x20\x20\40\x20\x20\x20\x20\x20\114\x6f\147\x69\x6e\40\x74\x6f\x20" . $MP["\x73\x70"] . "\74\57\141\x3e";
        Mo:
        DR:
        return $Kn;
    }
    function flush_cache()
    {
        if (!(MoIDPUtility::micr() && MoIDPUtility::iclv())) {
            goto Yl;
        }
        MoIDPUtility::mius();
        Yl:
    }
    function startdprocess()
    {
        if (!MSI_DEBUG) {
            goto cd;
        }
        MoIDPUtility::mo_debug("\104\x65\141\x63\x74\151\166\141\x74\x69\x6e\147\x20\164\150\x65\x20\x50\x6c\165\x67\x69\x6e");
        cd:
        require_once ABSPATH . "\167\160\55\x61\x64\155\151\x6e\x2f\x69\156\x63\x6c\x75\144\145\163\57\x70\x6c\x75\x67\151\156\56\x70\x68\160";
        deactivate_plugins(MSI_PLUGIN_NAME);
        wp_die("\74\163\x74\x72\157\156\x67\x3e\x4c\113\x5f\105\122\x52\117\x52\72\40\x3c\57\163\164\x72\157\x6e\x67\x3e\123\x53\x4f\40\106\x61\151\x6c\145\144\x2e\40\x50\x6c\x65\x61\x73\145\x20\143\157\x6e\x74\141\x63\164\x20\171\x6f\x75\x72\x20\x61\144\x6d\x69\156\163\x74\x72\141\164\157\x72\x2e");
    }
    function mo_idp_deactivate()
    {
        do_action("\x66\154\x75\163\x68\137\143\141\143\x68\x65");
        wp_clear_scheduled_hook("\155\157\x5f\151\x64\160\x5f\166\145\162\163\x69\157\x6e\137\143\x68\x65\143\153");
        delete_site_option("\155\x6f\x5f\x69\144\160\x5f\x74\x72\141\156\x73\141\x63\x74\151\x6f\x6e\111\144");
        delete_site_option("\x6d\157\x5f\151\x64\160\137\x61\x64\155\x69\156\x5f\160\x61\163\163\x77\x6f\162\144");
        delete_site_option("\155\157\137\151\x64\160\137\x72\x65\147\151\x73\164\162\x61\164\151\x6f\x6e\137\163\164\141\164\x75\x73");
        delete_site_option("\x6d\157\137\151\x64\160\x5f\x61\x64\155\151\x6e\x5f\x70\x68\x6f\156\x65");
        delete_site_option("\x6d\x6f\x5f\x69\144\x70\137\x6e\x65\x77\x5f\x72\145\147\151\163\x74\162\141\x74\151\x6f\x6e");
        delete_site_option("\155\x6f\137\151\x64\x70\137\141\x64\x6d\151\156\137\x63\165\163\x74\157\x6d\145\x72\137\x6b\x65\x79");
        delete_site_option("\155\x6f\x5f\151\x64\x70\x5f\x61\x64\x6d\x69\156\137\141\160\x69\137\x6b\145\171");
        delete_site_option("\x6d\157\137\x69\x64\x70\137\x76\x65\x72\151\x66\171\x5f\143\x75\163\164\157\x6d\x65\162");
        delete_site_option("\x73\x6d\x6c\x5f\151\x64\160\137\x6c\x65\x64");
        delete_site_option("\151\x64\x70\x5f\x76\154\x5f\x63\150\145\x63\153\137\x74");
        delete_site_option("\151\x64\x70\137\166\x6c\x5f\x63\150\145\x63\153\137\x73");
        wp_redirect(self_admin_url("\x70\154\165\x67\x69\156\163\56\x70\x68\160\x3f\x64\145\x61\x63\x74\x69\x76\x61\164\145\75\x74\162\165\145"));
    }
    function mo_idp_plugin_anchor_links($cy)
    {
        if (!array_key_exists("\144\145\x61\143\164\151\166\141\x74\145", $cy)) {
            goto vi;
        }
        $iu = array();
        $fL = ["\x53\x65\x74\x74\x69\x6e\147\163" => "\151\144\x70\137\143\x6f\156\146\151\147\x75\162\145\137\151\144\160"];
        foreach ($fL as $xO => $tL) {
            $L3 = esc_url(add_query_arg("\160\141\x67\145", $tL, get_admin_url() . "\x61\x64\155\151\156\56\160\150\x70\77"));
            $uo = "\x3c\x61\40\150\162\145\x66\x3d\x27{$L3}\x27\x3e" . __($xO) . "\74\57\141\76";
            array_push($iu, $uo);
            A4:
        }
        m3:
        $cy = $iu + $cy;
        vi:
        return $cy;
    }
}
