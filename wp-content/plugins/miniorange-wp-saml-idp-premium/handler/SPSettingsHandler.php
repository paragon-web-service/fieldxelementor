<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
final class SPSettingsHandler extends SPSettingsUtility
{
    use Instance;
    private function __construct()
    {
    }
    public function _mo_idp_save_new_sp($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfRequiredFieldsEmpty(array("\x69\x64\x70\137\x73\160\137\x6e\x61\155\x65" => $y8, "\151\144\x70\137\x73\160\x5f\151\163\x73\x75\145\x72" => $y8, "\151\144\160\137\x61\x63\163\x5f\165\x72\x6c" => $y8, "\x69\144\160\137\156\141\x6d\x65\151\144\x5f\x66\157\162\x6d\x61\164" => $y8));
        $kl = $fL = array();
        $iv = $kl["\x6d\x6f\137\x69\x64\160\137\163\160\x5f\x6e\x61\155\x65"] = $fL["\x6d\x6f\x5f\x69\x64\x70\x5f\x73\160\x5f\x6e\x61\x6d\145"] = sanitize_text_field($y8["\151\x64\160\137\x73\160\137\156\x61\155\x65"]);
        $QB = $fL["\155\157\137\151\144\160\x5f\163\160\x5f\x69\163\x73\165\x65\162"] = sanitize_text_field($y8["\151\144\x70\x5f\x73\160\137\151\x73\163\x75\x65\x72"]);
        $this->checkIssuerAlreadyInUse($QB, NULL, $iv);
        $this->checkNameAlreaydInUse($iv);
        $fL = $this->collectData($y8, $fL);
        $nx = $dbIDPQueries->insert_sp_data($fL);
        do_action("\x6d\157\137\151\x64\160\137\x73\x68\x6f\167\x5f\x6d\145\x73\163\x61\x67\x65", MoIDPMessages::showMessage("\123\x45\x54\124\x49\116\x47\x53\x5f\123\x41\126\105\104"), "\x53\x55\x43\x43\x45\x53\123");
    }
    public function _mo_idp_edit_sp($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfRequiredFieldsEmpty(array("\x69\x64\x70\137\163\160\137\x6e\141\x6d\x65" => $y8, "\151\144\160\137\x73\x70\137\x69\x73\163\165\145\162" => $y8, "\151\x64\x70\x5f\x61\143\x73\137\x75\x72\x6c" => $y8, "\151\x64\x70\x5f\x6e\141\155\x65\151\144\137\x66\x6f\x72\155\x61\x74" => $y8));
        $this->checkIfValidServiceProvider($y8, TRUE, "\163\x65\x72\166\151\x63\145\137\x70\x72\x6f\166\x69\144\145\162");
        $fL = $kl = array();
        $e6 = $kl["\151\x64"] = $y8["\163\x65\162\166\151\143\145\x5f\160\162\x6f\166\151\144\145\x72"];
        $iv = $fL["\155\x6f\x5f\151\144\160\x5f\163\x70\137\x6e\x61\155\145"] = sanitize_text_field($y8["\x69\144\x70\x5f\163\x70\137\156\141\155\145"]);
        $QB = $fL["\155\157\137\x69\x64\160\x5f\163\160\137\x69\x73\x73\x75\145\x72"] = sanitize_text_field($y8["\151\x64\x70\x5f\163\160\137\151\163\x73\165\145\x72"]);
        $this->checkIfValidServiceProvider($dbIDPQueries->get_sp_data($e6));
        $this->checkIssuerAlreadyInUse($QB, $e6, NULL);
        $this->checkNameAlreaydInUse($iv, $e6);
        $fL = $this->collectData($y8, $fL);
        $dbIDPQueries->update_sp_data($fL, $kl);
        do_action("\x6d\x6f\x5f\151\x64\160\x5f\163\150\157\167\137\x6d\x65\163\x73\141\x67\145", MoIDPMessages::showMessage("\x53\105\124\x54\x49\116\x47\123\x5f\123\101\x56\x45\x44"), "\x53\125\x43\103\x45\x53\x53");
    }
    public function mo_idp_delete_sp_settings($y8)
    {
        global $dbIDPQueries;
        MoIDPUtility::startSession();
        $this->checkIfValidPlugin();
        $RV = array();
        $RV["\151\144"] = $y8["\x73\160\x5f\x69\144"];
        $v0["\x6d\x6f\x5f\x73\160\137\x69\x64"] = $y8["\x73\x70\x5f\151\x64"];
        $dbIDPQueries->delete_sp($RV, $v0);
        if (!array_key_exists("\123\120", $_SESSION)) {
            goto Xj;
        }
        unset($_SESSION["\123\x50"]);
        Xj:
        do_action("\155\157\137\x69\144\x70\x5f\x73\150\157\167\137\x6d\x65\x73\x73\141\x67\145", MoIDPMessages::showMessage("\123\x50\137\104\x45\114\x45\124\105\x44"), "\x53\x55\103\x43\105\123\123");
    }
    public function mo_idp_change_name_id($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfValidServiceProvider($y8, TRUE, "\x73\x65\x72\166\x69\x63\x65\137\x70\x72\157\166\x69\x64\145\x72");
        $fL = $kl = array();
        $x9 = $kl["\x69\x64"] = $y8["\163\x65\x72\166\x69\143\x65\x5f\x70\x72\157\166\151\x64\x65\162"];
        $fL["\x6d\157\x5f\x69\144\160\x5f\156\x61\155\145\x69\x64\x5f\141\x74\x74\x72"] = $y8["\x69\x64\160\137\156\141\155\x65\151\144\137\x61\x74\x74\x72"];
        $dbIDPQueries->update_sp_data($fL, $kl);
        do_action("\x6d\157\137\x69\144\160\x5f\x73\150\x6f\167\x5f\155\145\163\163\141\x67\x65", MoIDPMessages::showMessage("\x53\105\124\124\x49\116\x47\x53\x5f\x53\101\126\x45\x44"), "\x53\x55\103\x43\105\123\x53");
    }
    public function _mo_sp_change_settings($y8)
    {
        $this->checkIfValidPlugin();
        $this->checkIfValidServiceProvider($y8, TRUE, "\x73\x65\162\x76\x69\x63\145\137\x70\x72\x6f\166\x69\x64\x65\162");
    }
    private function collectData($y8, $fL)
    {
        $fL["\x6d\157\x5f\151\144\160\x5f\x61\143\163\137\165\x72\x6c"] = sanitize_text_field($y8["\151\x64\160\x5f\x61\x63\x73\x5f\165\x72\154"]);
        $fL["\155\157\x5f\151\144\160\137\156\141\155\x65\151\x64\x5f\x66\x6f\162\155\141\x74"] = sanitize_text_field($y8["\x69\144\160\x5f\x6e\141\x6d\145\151\x64\137\146\157\x72\155\141\164"]);
        $fL["\155\157\x5f\151\144\x70\x5f\x70\x72\x6f\164\x6f\143\x6f\x6c\x5f\164\171\x70\x65"] = sanitize_text_field($y8["\x6d\x6f\x5f\x69\144\160\x5f\160\162\157\x74\157\x63\x6f\x6c\137\164\171\x70\145"]);
        $sB = isset($y8["\151\x64\x70\x5f\154\157\147\x6f\x75\164\x5f\x75\162\154"]) ? sanitize_text_field($y8["\x69\144\160\x5f\154\157\147\157\x75\x74\x5f\x75\x72\154"]) : NULL;
        $Ix = isset($y8["\155\x6f\137\x69\144\160\137\x63\145\x72\x74"]) ? SAMLUtilities::sanitize_certificate(trim($y8["\155\157\137\x69\144\160\x5f\143\x65\x72\164"])) : NULL;
        $KG = isset($y8["\155\157\137\151\144\x70\x5f\x63\145\162\x74\137\x65\156\x63\x72\x79\x70\x74"]) ? SAMLUtilities::sanitize_certificate(trim($y8["\155\x6f\x5f\x69\x64\160\137\x63\x65\x72\164\137\145\x6e\143\162\x79\x70\164"])) : NULL;
        $Hi = isset($y8["\x69\144\x70\137\144\x65\146\x61\165\x6c\x74\x5f\x72\x65\154\x61\171\x53\x74\x61\164\x65"]) ? sanitize_text_field($y8["\x69\x64\x70\x5f\x64\145\146\141\165\154\164\137\162\145\154\141\x79\123\x74\141\164\x65"]) : NULL;
        $SX = isset($y8["\155\x6f\x5f\151\x64\160\137\x6c\x6f\x67\x6f\165\x74\x5f\142\x69\156\144\x69\156\x67\x5f\164\171\160\x65"]) ? $y8["\155\x6f\137\x69\x64\160\x5f\x6c\157\147\x6f\165\164\x5f\x62\x69\x6e\144\151\156\147\137\164\171\160\145"] : "\110\164\164\x70\122\145\x64\151\162\145\143\x74";
        $fL["\x6d\157\137\151\144\160\x5f\x6c\157\147\157\x75\x74\x5f\x75\x72\154"] = $sB;
        $fL["\x6d\157\x5f\x69\144\160\137\143\x65\x72\164"] = $Ix;
        $fL["\155\x6f\x5f\x69\144\160\137\x63\x65\x72\x74\x5f\x65\156\143\162\171\160\x74"] = $KG;
        $fL["\x6d\x6f\137\x69\x64\160\x5f\x64\145\x66\141\165\154\x74\137\x72\145\154\x61\171\123\164\x61\164\x65"] = $Hi;
        $fL["\155\157\x5f\151\x64\x70\137\x6c\157\147\x6f\x75\x74\x5f\142\151\x6e\x64\151\156\x67\x5f\164\171\160\x65"] = $SX;
        $fL["\x6d\157\137\x69\144\x70\137\x72\x65\163\x70\157\x6e\163\145\x5f\163\x69\147\x6e\x65\144"] = isset($y8["\x69\x64\160\x5f\x72\x65\163\160\x6f\156\163\145\x5f\163\151\x67\x6e\x65\144"]) ? $y8["\x69\x64\160\x5f\162\145\163\x70\157\x6e\163\145\x5f\x73\x69\147\x6e\x65\x64"] : NULL;
        $fL["\155\x6f\137\151\x64\160\137\x61\x73\163\x65\162\164\x69\157\156\x5f\x73\151\147\x6e\x65\144"] = isset($y8["\x69\x64\160\x5f\x61\163\163\x65\x72\x74\151\x6f\156\x5f\x73\151\x67\156\145\x64"]) ? $y8["\151\x64\x70\137\141\163\163\x65\162\x74\151\157\156\x5f\x73\151\147\x6e\x65\144"] : NULL;
        $fL["\x6d\x6f\x5f\x69\x64\x70\x5f\145\156\143\x72\x79\160\x74\145\x64\x5f\x61\x73\x73\145\x72\x74\151\157\156"] = isset($y8["\x69\144\160\137\x65\x6e\x63\162\x79\160\164\145\144\137\x61\x73\163\145\x72\x74\151\157\156"]) ? $y8["\151\x64\x70\x5f\x65\x6e\x63\x72\171\160\x74\145\144\x5f\141\x73\163\x65\162\x74\x69\157\156"] : NULL;
        $this->checkIfValidEncryptionCertProvided($fL["\x6d\157\137\x69\x64\x70\x5f\x65\x6e\x63\x72\x79\160\164\x65\144\x5f\141\163\x73\145\162\164\151\157\156"], $fL["\x6d\157\137\x69\144\x70\137\143\x65\162\x74\137\x65\x6e\143\x72\x79\x70\164"]);
        return $fL;
    }
    public function show_sso_users($y8)
    {
        $this->checkIfValidPlugin();
        update_site_option("\155\x6f\x5f\151\144\160\x5f\163\x68\x6f\167\137\163\163\157\137\x75\x73\x65\x72\x73", array_key_exists("\163\150\x6f\x77\137\163\x73\x6f\137\165\163\145\x72\163", $y8) ? TRUE : FALSE);
        do_action("\155\x6f\137\x69\x64\x70\137\x73\x68\157\167\x5f\x6d\145\163\163\x61\x67\145", MoIDPMessages::showMessage("\x53\x45\x54\124\111\116\107\x53\x5f\x53\101\126\105\104"), "\x53\x55\103\x43\x45\123\x53");
    }
}
