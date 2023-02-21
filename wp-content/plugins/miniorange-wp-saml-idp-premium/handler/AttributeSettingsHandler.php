<?php


namespace IDP\Handler;

use IDP\Exception\JSErrorException;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
final class AttributeSettingsHandler extends SPSettingsUtility
{
    use Instance;
    public function mo_add_role_attribute($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfValidServiceProvider($y8, TRUE, "\x73\145\162\166\151\x63\145\137\x70\x72\x6f\x76\x69\x64\145\162");
        $this->checkIfJSErrorMessage($y8);
        $fL = $kl = array();
        $cK = null;
        $x9 = $kl["\151\x64"] = $y8["\x73\145\162\x76\x69\143\145\x5f\x70\162\x6f\166\x69\x64\145\x72"];
        if (!isset($y8["\151\x64\160\x5f\162\157\154\x65\x5f\141\x74\x74\x72\x69\x62\165\164\x65"])) {
            goto Cl;
        }
        $Ee = $fL["\155\x6f\137\x69\x64\x70\137\145\156\x61\142\154\145\137\147\162\x6f\x75\160\x5f\x6d\x61\160\160\x69\156\x67"] = $y8["\151\x64\x70\x5f\162\157\x6c\145\x5f\141\164\x74\162\x69\x62\165\164\145"];
        Cl:
        if (empty($Ee)) {
            goto Nq;
        }
        $cK = "\147\x72\x6f\165\x70\115\x61\x70\x4e\x61\155\x65";
        $vF = sanitize_text_field($y8["\x6d\157\137\151\144\160\x5f\162\x6f\x6c\x65\137\155\x61\160\x70\151\x6e\x67\137\x6e\141\x6d\x65"]);
        Nq:
        $Ko = $dbIDPQueries->get_sp_role_attribute($x9);
        if (!isset($Ko)) {
            goto c2;
        }
        $u0["\x6d\x6f\137\x73\x70\137\x69\x64"] = $x9;
        $u0["\155\x6f\137\x73\160\x5f\141\164\x74\x72\137\156\141\x6d\x65"] = "\x67\x72\x6f\x75\x70\x4d\x61\x70\116\141\x6d\145";
        $u0["\x6d\157\137\141\x74\x74\162\x5f\164\171\x70\x65"] = 1;
        $dbIDPQueries->delete_sp_attributes($u0);
        c2:
        if (is_null($cK)) {
            goto Ec;
        }
        if (MoIDPUtility::isBlank($vF)) {
            goto iU;
        }
        $qz = array();
        $qz["\x6d\157\x5f\163\x70\x5f\151\144"] = $x9;
        $qz["\x6d\x6f\137\163\x70\137\x61\164\x74\x72\137\x6e\141\155\145"] = sanitize_text_field($cK);
        $qz["\155\x6f\x5f\x73\x70\137\x61\x74\164\x72\x5f\166\x61\154\x75\145"] = sanitize_text_field($vF);
        $qz["\155\x6f\137\x61\x74\x74\162\137\x74\171\160\x65"] = 1;
        $dbIDPQueries->insert_sp_attributes($qz);
        $dbIDPQueries->update_sp_data($fL, $kl);
        iU:
        Ec:
        do_action("\x6d\x6f\137\x69\x64\160\x5f\x73\x68\157\167\137\155\145\x73\x73\141\x67\x65", MoIDPMessages::showMessage("\x53\105\x54\124\x49\x4e\x47\x53\137\123\x41\x56\105\x44"), "\123\125\x43\x43\105\x53\123");
    }
    public function mo_idp_save_attr_settings($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfValidServiceProvider($y8, TRUE, "\163\145\162\x76\x69\x63\145\x5f\160\x72\157\x76\151\x64\x65\x72");
        $this->checkIfJSErrorMessage($y8);
        $fL = $kl = array();
        $x9 = $kl["\151\x64"] = $y8["\163\x65\162\166\x69\x63\145\x5f\160\162\157\166\151\x64\x65\x72"];
        $cK = isset($y8["\155\x6f\137\x69\144\160\x5f\x61\164\164\162\x69\142\x75\164\145\x5f\155\141\160\x70\x69\x6e\x67\137\x6e\141\155\145"]) ? $y8["\x6d\x6f\137\x69\144\160\x5f\141\164\164\x72\151\142\x75\x74\x65\137\x6d\141\x70\160\151\156\147\137\x6e\141\155\145"] : '';
        $vF = isset($y8["\x6d\157\x5f\151\144\x70\137\x61\x74\x74\x72\151\142\165\x74\145\137\155\x61\160\160\x69\156\x67\x5f\166\x61\x6c"]) ? $y8["\155\157\x5f\151\144\160\137\x61\x74\164\x72\x69\x62\x75\x74\x65\137\155\141\160\160\x69\156\147\x5f\166\x61\154"] : '';
        $Ko = $dbIDPQueries->get_sp_attributes($x9);
        if (!isset($Ko)) {
            goto Zl;
        }
        $u0["\155\x6f\137\x73\160\x5f\x69\x64"] = $x9;
        $u0["\x6d\x6f\x5f\141\x74\x74\x72\x5f\x74\x79\x70\145"] = 0;
        $dbIDPQueries->delete_sp_attributes($u0);
        Zl:
        if (empty($cK)) {
            goto tZ;
        }
        foreach ($cK as $xO => $l7) {
            if (MoIDPUtility::isBlank($l7)) {
                goto Br;
            }
            $qz = array();
            $qz["\x6d\x6f\137\x73\x70\137\x69\x64"] = $x9;
            $qz["\155\x6f\137\x73\160\x5f\x61\164\164\x72\137\x6e\141\x6d\145"] = sanitize_text_field($l7);
            $qz["\x6d\x6f\x5f\x73\x70\x5f\x61\x74\x74\162\137\166\141\154\x75\145"] = sanitize_text_field($vF[$xO]);
            $dbIDPQueries->insert_sp_attributes($qz);
            Br:
            Q_:
        }
        bt:
        tZ:
        do_action("\x6d\x6f\x5f\x69\144\x70\x5f\163\x68\157\x77\x5f\155\x65\x73\163\141\x67\145", MoIDPMessages::showMessage("\x53\105\124\124\111\116\107\123\x5f\123\x41\x56\x45\x44"), "\x53\x55\103\x43\x45\x53\123");
    }
    public function mo_save_custom_idp_attr($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        $this->checkIfValidServiceProvider($y8, TRUE, "\x73\145\x72\166\x69\143\145\137\x70\x72\157\x76\151\x64\145\162");
        $this->checkIfJSErrorMessage($y8);
        $fL = $kl = array();
        $x9 = $kl["\151\144"] = $y8["\163\x65\x72\x76\151\x63\145\137\160\x72\x6f\166\151\x64\145\162"];
        $cK = isset($y8["\x6d\x6f\x5f\151\144\160\x5f\x61\x74\x74\x72\151\142\x75\164\145\137\x6d\141\x70\160\151\x6e\x67\137\x6e\x61\x6d\145"]) ? $y8["\155\x6f\x5f\151\x64\x70\x5f\141\x74\164\162\151\142\165\x74\145\137\x6d\x61\x70\160\x69\156\x67\137\156\x61\x6d\x65"] : '';
        $vF = isset($y8["\155\157\x5f\x69\144\x70\137\x61\164\x74\162\151\142\165\x74\x65\137\x6d\141\160\x70\x69\156\147\x5f\x76\x61\x6c"]) ? $y8["\x6d\x6f\x5f\x69\x64\160\137\x61\164\164\x72\151\142\x75\164\145\137\155\x61\160\x70\151\156\x67\137\166\141\154"] : '';
        $Ko = $dbIDPQueries->get_custom_sp_attr($x9);
        if (!isset($Ko)) {
            goto F5;
        }
        $u0["\155\157\x5f\163\x70\x5f\151\144"] = $x9;
        $u0["\155\x6f\x5f\141\164\x74\x72\x5f\164\171\x70\145"] = 2;
        $dbIDPQueries->delete_sp_attributes($u0);
        F5:
        if (empty($cK)) {
            goto uy;
        }
        foreach ($cK as $xO => $l7) {
            if (MoIDPUtility::isBlank($l7)) {
                goto Im;
            }
            $qz = array();
            $qz["\155\157\x5f\x73\160\137\151\144"] = $x9;
            $qz["\155\157\x5f\x73\160\x5f\141\164\164\162\x5f\156\141\x6d\x65"] = sanitize_text_field(stripslashes($l7));
            $qz["\x6d\157\x5f\x73\160\x5f\x61\164\164\x72\137\x76\x61\154\165\x65"] = sanitize_text_field(stripslashes($vF[$xO]));
            $qz["\x6d\x6f\x5f\141\x74\x74\162\137\x74\171\x70\145"] = 2;
            $dbIDPQueries->insert_sp_attributes($qz);
            Im:
            UN:
        }
        MP:
        uy:
        do_action("\155\157\x5f\x69\144\x70\137\x73\x68\x6f\x77\x5f\x6d\x65\x73\163\141\x67\x65", MoIDPMessages::showMessage("\x53\105\124\124\111\116\107\x53\137\123\x41\126\105\104"), "\x53\125\x43\x43\105\x53\x53");
    }
}
