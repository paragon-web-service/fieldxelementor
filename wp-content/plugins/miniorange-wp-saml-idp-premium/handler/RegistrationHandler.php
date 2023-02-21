<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\PluginPageDetails;
use IDP\Helper\Utilities\TabDetails;
use IDP\Helper\Utilities\Tabs;
final class RegistrationHandler extends RegistrationUtility
{
    use Instance;
    private function __construct()
    {
        $this->_nonce = "\162\145\147\x5f\x68\x61\156\x64\154\x65\x72";
    }
    public function _idp_register_customer($y8)
    {
        $QV = sanitize_email($y8["\145\x6d\141\151\x6c"]);
        $xq = sanitize_text_field($y8["\x70\141\x73\163\x77\x6f\x72\x64"]);
        $Gl = sanitize_text_field($y8["\x63\x6f\x6e\x66\151\x72\155\x50\x61\x73\x73\167\x6f\162\x64"]);
        $this->checkIfRegReqFieldsEmpty(array($QV, $xq, $Gl));
        $this->checkPwdStrength($xq, $Gl);
        $this->pwdAndCnfrmPwdMatch($xq, $Gl);
        update_site_option("\x6d\157\137\x69\144\160\137\x61\144\155\x69\x6e\x5f\x65\155\x61\x69\154", $QV);
        update_site_option("\155\x6f\137\x69\144\x70\137\x61\144\x6d\x69\156\137\160\x61\x73\x73\x77\157\x72\x64", $xq);
        $Rp = json_decode(MoIDPUtility::checkCustomer(), true);
        switch ($Rp["\x73\164\x61\164\165\x73"]) {
            case "\x43\x55\123\x54\117\115\105\x52\x5f\116\117\124\x5f\106\117\125\116\x44":
                $this->_create_user_without_verification($QV, $xq);
                goto kV;
            default:
                $this->_get_current_customer($QV, $xq);
                goto kV;
        }
        ur:
        kV:
    }
    public function _mo_idp_phone_verification($y8)
    {
        $mA = sanitize_text_field($y8["\x70\150\x6f\x6e\145\137\x6e\x75\x6d\x62\x65\x72"]);
        $mA = str_replace("\x20", '', $mA);
        $this->isValidPhoneNumber($mA);
        update_site_option("\155\x6f\137\143\165\163\164\x6f\155\x65\162\137\166\141\x6c\x69\144\x61\x74\x69\x6f\156\x5f\141\144\155\x69\x6e\x5f\160\x68\x6f\156\145", $mA);
        $this->_send_otp_token('', $mA, "\x53\115\x53");
    }
    public function save_success_customer_config($e6, $o5, $kR, $nE)
    {
        update_site_option("\155\157\137\151\144\x70\x5f\x61\x64\x6d\x69\156\137\x63\165\163\164\x6f\x6d\145\162\137\x6b\x65\171", $e6);
        update_site_option("\x6d\x6f\137\x69\x64\160\137\141\144\155\151\x6e\x5f\141\160\151\x5f\153\x65\x79", $o5);
        update_site_option("\155\157\137\x69\x64\160\x5f\143\x75\x73\164\157\155\145\x72\x5f\164\157\x6b\145\x6e", $kR);
        delete_site_option("\x6d\157\137\151\x64\x70\137\166\x65\162\151\146\x79\137\143\x75\x73\x74\x6f\155\x65\162");
        delete_site_option("\155\157\x5f\151\x64\x70\137\156\145\x77\137\162\145\147\151\163\164\x72\x61\x74\x69\157\x6e");
        delete_site_option("\155\157\x5f\151\144\160\137\x61\x64\x6d\151\156\137\x70\141\163\163\x77\x6f\x72\144");
        delete_site_option("\x6d\157\137\151\x64\x70\x5f\x72\x65\x67\x69\163\x74\x72\141\164\151\x6f\156\x5f\163\x74\141\164\x75\163");
    }
    public function _mo_idp_go_back()
    {
        $this->isValidRequest();
        wp_clear_scheduled_hook("\x6d\157\137\151\x64\x70\x5f\x76\145\162\163\x69\x6f\x6e\137\x63\x68\145\x63\x6b");
        delete_site_option("\x6d\157\137\x69\x64\160\x5f\x74\x72\141\156\x73\141\x63\164\151\x6f\x6e\x49\x64");
        delete_site_option("\x6d\x6f\x5f\x69\144\160\x5f\x61\144\155\x69\x6e\x5f\160\x61\163\163\167\157\x72\x64");
        delete_site_option("\155\157\x5f\151\144\x70\x5f\162\x65\147\151\163\x74\162\x61\164\x69\x6f\156\x5f\x73\164\x61\x74\165\x73");
        delete_site_option("\155\157\137\151\144\x70\137\141\144\155\151\x6e\137\160\150\157\156\145");
        delete_site_option("\x6d\157\x5f\151\x64\160\x5f\x6e\x65\x77\x5f\162\145\x67\151\x73\x74\x72\x61\x74\151\157\x6e");
        delete_site_option("\155\x6f\137\x69\x64\x70\x5f\141\144\155\151\x6e\137\143\x75\163\164\x6f\x6d\145\162\137\x6b\145\x79");
        delete_site_option("\x6d\x6f\137\x69\x64\160\x5f\x61\x64\155\151\156\x5f\141\x70\x69\137\153\x65\171");
        delete_site_option("\155\x6f\x5f\151\x64\160\x5f\141\144\x6d\151\156\137\145\155\141\x69\154");
        if (!($_POST["\157\160\164\151\x6f\156"] === "\x72\145\155\157\166\145\137\x69\144\160\137\x61\x63\143\x6f\x75\156\164")) {
            goto HF;
        }
        delete_site_option("\x73\x6d\154\137\x69\144\160\x5f\x6c\x6b");
        delete_site_option("\x74\x5f\x73\151\164\x65\137\163\164\141\164\165\163");
        delete_site_option("\x73\151\164\x65\x5f\151\144\160\137\143\153\x6c");
        delete_site_option("\163\x6d\154\x5f\x69\144\x70\x5f\154\145\144");
        HF:
        update_site_option("\155\x6f\137\151\144\x70\x5f\x76\x65\x72\151\x66\x79\137\x63\165\163\164\157\x6d\145\x72", $_POST["\x6f\x70\x74\151\157\156"] === "\162\x65\155\x6f\166\x65\x5f\151\x64\x70\x5f\x61\x63\143\157\165\156\x74");
        update_site_option("\x6d\x6f\x5f\x69\x64\x70\x5f\156\x65\x77\137\162\145\x67\151\x73\x74\x72\141\164\151\x6f\156", $_POST["\x6f\160\164\151\157\x6e"] === "\x6d\157\x5f\151\144\x70\x5f\x67\x6f\x5f\x62\141\143\153");
        $s6 = remove_query_arg("\160\141\x67\x65", $_SERVER["\x52\105\x51\x55\105\x53\x54\137\125\122\x49"]);
        $iQ = TabDetails::instance()->_tabDetails[Tabs::PROFILE];
        wp_redirect(add_query_arg(array("\160\141\147\145" => $iQ->_menuSlug), $s6));
    }
    public function _mo_idp_forgot_password()
    {
        $QV = get_site_option("\x6d\x6f\137\x69\x64\x70\x5f\x61\144\155\151\156\137\145\155\141\x69\154");
        $Rp = json_decode(MoIDPUtility::forgotPassword($QV), true);
        $this->checkIfPasswordResetSuccesfully($Rp, "\x73\164\x61\x74\165\163");
        do_action("\x6d\x6f\x5f\151\x64\160\x5f\163\x68\157\x77\137\155\145\163\163\141\x67\145", MoIDPMessages::showMessage("\x50\x41\123\x53\x5f\122\x45\x53\105\124"), "\123\125\103\x43\x45\x53\123");
    }
    public function _mo_idp_verify_customer($y8)
    {
        $QV = sanitize_email($y8["\x65\x6d\141\x69\x6c"]);
        $xq = sanitize_text_field($y8["\x70\141\x73\163\167\x6f\x72\x64"]);
        $this->checkIfRequiredFieldsEmpty(array($QV, $xq));
        $this->_get_current_customer($QV, $xq);
    }
    public function _send_otp_token($QV, $mA, $yn)
    {
        $Rp = json_decode(MoIDPUtility::sendOtpToken($yn, $QV, $mA), true);
        $this->checkIfOTPSentSuccessfully($Rp, "\x73\x74\x61\x74\165\163");
        update_site_option("\155\x6f\137\x69\144\160\x5f\x74\162\x61\156\x73\141\x63\x74\151\157\156\111\x64", $Rp["\x74\x78\x49\x64"]);
        update_site_option("\x6d\157\137\151\144\x70\x5f\162\145\x67\151\163\164\x72\141\x74\x69\157\x6e\x5f\x73\x74\141\x74\x75\x73", "\x4d\x4f\x5f\117\x54\120\137\104\x45\114\111\126\x45\122\x45\x44\137\123\125\103\x43\105\x53\123");
        if ($yn == "\x45\115\101\111\114") {
            goto WL;
        }
        do_action("\155\157\137\x69\144\x70\137\163\x68\x6f\167\137\155\x65\163\163\x61\147\145", MoIDPMessages::showMessage("\120\x48\x4f\116\x45\x5f\117\x54\x50\x5f\x53\x45\x4e\124", array("\x70\x68\x6f\156\x65" => $mA)), "\123\125\x43\103\105\x53\123");
        goto mg;
        WL:
        do_action("\155\157\x5f\x69\144\160\137\x73\150\157\x77\137\155\x65\163\163\x61\x67\x65", MoIDPMessages::showMessage("\x45\115\101\111\x4c\137\x4f\124\120\137\x53\x45\116\124", array("\x65\x6d\x61\151\154" => $QV)), "\x53\x55\103\103\105\123\x53");
        mg:
    }
    public function _get_current_customer($QV, $xq)
    {
        $Rp = MoIdpUtility::getCustomerKey($QV, $xq);
        $Fn = json_decode($Rp, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            goto Pj;
        }
        update_site_option("\155\x6f\x5f\x69\144\160\137\x76\145\x72\x69\146\171\137\x63\165\x73\x74\x6f\x6d\x65\x72", true);
        delete_site_option("\x6d\x6f\137\151\x64\160\137\156\145\x77\137\x72\x65\x67\151\163\164\162\x61\164\151\x6f\156");
        do_action("\x6d\157\137\151\144\160\137\163\x68\x6f\167\x5f\155\145\163\x73\x61\x67\x65", MoIDPMessages::showMessage("\x41\103\x43\117\x55\x4e\x54\x5f\x45\130\x49\123\124\x53"), "\x45\x52\x52\x4f\x52");
        goto RH;
        Pj:
        update_site_option("\155\x6f\137\151\x64\160\x5f\141\144\x6d\x69\156\137\x65\x6d\141\151\154", $QV);
        $this->save_success_customer_config($Fn["\151\144"], $Fn["\x61\x70\151\x4b\x65\x79"], $Fn["\164\x6f\x6b\145\x6e"], $Fn["\141\x70\x70\x53\145\x63\162\145\164"]);
        if (!get_site_option("\x73\155\x6c\x5f\151\x64\x70\137\x6c\x6b")) {
            goto V8;
        }
        $this->verifyILK(get_site_option("\163\155\x6c\x5f\x69\x64\160\x5f\x6c\x6b"));
        V8:
        RH:
    }
    public function _idp_validate_otp($y8)
    {
        $iw = sanitize_text_field($y8["\x6f\164\160\137\x74\x6f\153\145\x6e"]);
        $this->checkIfOTPEntered(array("\157\164\x70\x5f\164\157\x6b\x65\156" => $y8));
        $Rp = json_decode(MoIDPUtility::validateOtpToken(get_site_option("\x6d\157\137\x69\x64\x70\137\164\162\141\x6e\x73\141\143\164\151\157\156\x49\x64"), $iw), true);
        $this->checkIfOTPValidationPassed($Rp, "\x73\x74\x61\164\x75\x73");
        $Fn = json_decode(MoIDPUtility::createCustomer(), true);
        if (strcasecmp($Fn["\x73\x74\141\164\165\163"], "\103\125\x53\x54\x4f\x4d\105\122\137\x55\x53\x45\x52\116\x41\115\105\x5f\x41\114\122\105\101\104\x59\137\x45\130\111\x53\124\123") == 0) {
            goto JT;
        }
        if (!(strcasecmp($Fn["\x73\164\x61\x74\165\163"], "\123\x55\103\103\105\123\123") == 0)) {
            goto qg;
        }
        $this->save_success_customer_config($Fn["\151\x64"], $Fn["\141\x70\x69\113\145\171"], $Fn["\x74\157\x6b\x65\156"], $Fn["\141\x70\160\123\x65\x63\x72\x65\164"]);
        do_action("\x6d\x6f\137\151\144\160\x5f\163\x68\157\x77\x5f\155\x65\x73\163\x61\x67\x65", MoIDPMessages::showMessage("\x4e\x45\127\137\x52\x45\x47\137\123\125\x43\x43\x45\x53"), "\x53\x55\103\x43\x45\123\x53");
        qg:
        goto Pu;
        JT:
        do_action("\155\157\x5f\151\x64\160\137\x73\x68\x6f\167\137\155\145\x73\163\x61\x67\x65", MoIDPMessages::showMessage("\101\x43\x43\117\125\x4e\124\x5f\105\x58\111\x53\x54\x53"), "\x53\125\103\x43\105\123\123");
        Pu:
    }
    public function _create_user_without_verification($QV, $xq)
    {
        $Fn = json_decode(MoIDPUtility::createCustomer(), true);
        if (strcasecmp($Fn["\x73\x74\141\x74\165\163"], "\x43\125\x53\x54\x4f\x4d\105\122\137\x55\123\105\122\116\x41\x4d\x45\x5f\101\114\x52\105\x41\x44\131\137\x45\x58\x49\x53\x54\123") == 0) {
            goto Tc;
        }
        if (!(strcasecmp($Fn["\163\x74\141\164\165\x73"], "\x53\x55\103\103\105\x53\x53") == 0)) {
            goto Zm;
        }
        $this->save_success_customer_config($Fn["\151\x64"], $Fn["\x61\x70\151\x4b\x65\171"], $Fn["\x74\x6f\x6b\145\156"], $Fn["\x61\160\x70\123\145\x63\x72\145\x74"]);
        do_action("\155\x6f\x5f\151\x64\x70\x5f\163\150\x6f\x77\137\x6d\145\163\x73\141\147\145", MoIDPMessages::showMessage("\116\105\127\x5f\122\105\107\137\123\x55\x43\x43\x45\x53"), "\x53\x55\x43\x43\x45\123\x53");
        Zm:
        goto TZ;
        Tc:
        $this->_get_current_customer($QV, $xq);
        TZ:
    }
    public function verifyILK($Oo)
    {
        $xO = get_site_option("\x6d\157\137\151\x64\160\x5f\143\x75\x73\164\x6f\x6d\145\162\137\x74\157\x6b\x65\x6e");
        $Oo = \AESEncryption::decrypt_data($Oo, $xO);
        $Rp = json_decode(MoIDPUtility::vml($Oo), true);
        if (array_key_exists("\x73\x74\x61\164\x75\x73", $Rp) && strcasecmp($Rp["\x73\164\x61\164\165\x73"], "\x53\125\x43\103\x45\x53\x53") == 0) {
            goto gO;
        }
        delete_site_option("\163\x6d\154\x5f\x69\144\160\137\x6c\153");
        do_action("\x6d\x6f\x5f\x69\x64\x70\x5f\x73\150\157\x77\x5f\x6d\x65\x73\x73\141\147\145", moIDPMessages::showMessage("\111\x4e\x56\101\114\x49\104\x5f\114\x49\103\x45\x4e\x53\x45"), "\x45\122\x52\117\x52");
        goto cm;
        gO:
        do_action("\x6d\x6f\x5f\151\144\x70\x5f\163\150\x6f\167\137\x6d\x65\x73\x73\141\x67\145", moIDPMessages::showMessage("\x52\105\x47\x5f\x53\x55\103\103\105\x53\123"), "\x53\x55\x43\x43\105\x53\x53");
        cm:
    }
}
