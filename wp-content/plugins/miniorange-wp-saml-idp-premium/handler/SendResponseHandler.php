<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Factory\ResponseDecisionHandler;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
use RobRichards\XMLSecLibs\XMLSecurityKey;
final class SendResponseHandler extends BaseHandler
{
    use Instance;
    private function __construct()
    {
    }
    public function mo_idp_send_response($VW, $ur = NULL)
    {
        if (!MSI_DEBUG) {
            goto rh;
        }
        MoIDPUtility::mo_debug("\x47\x65\x6e\x65\162\x61\164\151\156\147\x20\x4c\x6f\147\151\x6e\x20\x52\x65\163\160\x6f\x6e\163\145");
        rh:
        $this->checkIfValidPlugin();
        $this->checkValidDomain();
        $this->checkIfValidLicense();
        $current_user = wp_get_current_user();
        $current_user = !MoIDPUtility::isBlank($current_user->ID) ? $current_user : get_user_by("\154\157\x67\151\x6e", $ur);
        if (strcasecmp($VW["\x72\x65\x71\x75\145\x73\x74\x54\171\x70\145"], MoIDPConstants::AUTHN_REQUEST) == 0) {
            goto B4;
        }
        if (strcasecmp($VW["\x72\145\x71\165\x65\x73\164\124\x79\x70\x65"], MoIDPConstants::WS_FED) == 0) {
            goto bJ;
        }
        if (strcasecmp($VW["\162\145\161\165\x65\163\x74\124\171\160\x65"], MoIDPConstants::JWT) == 0) {
            goto NL;
        }
        goto F8;
        B4:
        $VW = $this->getSAMLResponseParams($VW);
        goto F8;
        bJ:
        $VW = $this->getWSFedResponseParams($VW);
        goto F8;
        NL:
        $VW = $this->getJWTResponseParams($VW);
        F8:
        do_action("\155\x6f\x5f\143\x68\x65\143\x6b\x5f\x62\x65\146\x6f\x72\145\x5f\x6d\151\x73\162", $current_user, $VW, $ur);
        MoIDPUtility::cutol($current_user);
        $DA = ResponseDecisionHandler::getResponseHandler($VW[0], array($VW[1], $VW[2], $VW[3], $VW[4], $VW[5], $VW[6], $ur, $VW[8]));
        $wG = $DA->generateResponse();
        if (!MSI_DEBUG) {
            goto tT;
        }
        MoIDPUtility::mo_debug("\x4c\x6f\x67\x69\x6e\40\122\x65\163\160\157\156\163\145\40\x67\145\x6e\145\162\x61\x74\x65\144\x3a\40" . $wG);
        tT:
        if (!ob_get_contents()) {
            goto iY;
        }
        ob_clean();
        iY:
        MoIDPUtility::unsetCookieVariables(array("\x72\x65\x73\160\157\156\163\145\x5f\x70\x61\162\141\x6d\x73", "\x6d\157\111\144\160\x73\145\156\144\x53\101\x4d\x4c\x52\145\x73\x70\x6f\156\x73\145", "\141\x63\x73\x5f\165\x72\x6c", "\x61\165\x64\151\145\156\143\x65", "\x72\x65\x6c\x61\x79\123\164\x61\x74\145", "\162\145\161\x75\x65\x73\164\x49\x44", "\x6d\157\111\144\160\x73\145\156\144\127\163\106\x65\144\122\x65\163\x70\x6f\156\x73\x65", "\167\x74\x72\x65\141\x6c\155", "\x77\141", "\167\143\164\x78", "\x63\x6c\151\145\x6e\x74\122\x65\x71\165\x65\x73\x74\x49\x64"));
        if (strcasecmp($VW[0], MoIDPConstants::SAML_RESPONSE) == 0) {
            goto kc;
        }
        if (strcasecmp($VW[0], MoIDPConstants::WS_FED_RESPONSE) == 0) {
            goto aV;
        }
        if (strcasecmp($VW[0], MoIDPConstants::JWT_RESPONSE) == 0) {
            goto lJ;
        }
        goto om;
        kc:
        $this->_send_response($wG, $VW[7], $VW[1]);
        goto om;
        aV:
        $this->_send_ws_fed_response($wG, $VW[5]->mo_idp_acs_url . "\77\x63\x6c\x69\x65\x6e\164\122\145\161\x75\145\163\164\111\x64\x3d" . $VW[8], $VW[3], $VW[2]);
        goto om;
        lJ:
        $this->_send_jwt_response($VW[4]->mo_idp_acs_url . "\77\x6a\x77\164\75" . $wG, $VW[6]);
        om:
    }
    public function getSAMLResponseParams($VW)
    {
        global $dbIDPQueries;
        $Oy = $VW["\x61\x63\163\137\x75\162\154"];
        $vj = $VW["\151\163\163\x75\x65\162"];
        $Hi = isset($VW["\x72\x65\x6c\141\x79\x53\x74\141\164\145"]) ? $VW["\x72\x65\154\x61\x79\123\x74\141\164\145"] : NULL;
        $A2 = isset($VW["\162\x65\161\165\x65\x73\x74\x49\x44"]) ? $VW["\x72\145\161\x75\x65\163\x74\x49\x44"] : NULL;
        $FW = "\137" . MoIDPUtility::generateRandomAlphanumericValue(30);
        MoIDPUtility::addSPCookie($vj, $FW);
        $xj = is_multisite() ? get_sites() : null;
        $GX = is_null($xj) ? site_url("\x2f") : get_site_url($xj[0]->blog_id, "\x2f");
        $QB = get_site_option("\x6d\x6f\x5f\151\x64\x70\137\145\156\164\151\x74\171\x5f\151\x64") ? get_site_option("\x6d\x6f\137\x69\144\x70\x5f\145\x6e\x74\x69\x74\171\x5f\151\144") : MSI_URL;
        $di = $dbIDPQueries->get_sp_from_acs($Oy);
        $e6 = !empty($di) ? $di->id : null;
        $Ko = $dbIDPQueries->get_all_sp_attributes($e6);
        return array(MoIDPConstants::SAML_RESPONSE, $Oy, $QB, $vj, $A2, $Ko, $di, $Hi, $FW);
    }
    public function getWSFedResponseParams($VW)
    {
        global $dbIDPQueries;
        $uM = $VW["\x63\x6c\151\x65\156\x74\122\145\x71\x75\x65\x73\164\111\144"];
        $Nv = $VW["\x77\164\x72\x65\x61\154\x6d"];
        $QS = $VW["\x77\x61"];
        $Hi = isset($VW["\162\x65\154\x61\x79\123\164\141\x74\145"]) ? $VW["\x72\x65\154\x61\171\x53\x74\x61\x74\x65"] : NULL;
        $E2 = isset($VW["\x77\143\164\x78"]) ? $VW["\x77\143\x74\x78"] : NULL;
        $xj = is_multisite() ? get_sites() : null;
        $GX = is_null($xj) ? site_url("\57") : get_site_url($xj[0]->blog_id, "\57");
        $QB = get_site_option("\155\157\x5f\x69\144\x70\137\x65\x6e\164\151\x74\171\x5f\x69\x64") ? get_site_option("\155\x6f\137\151\x64\x70\x5f\x65\x6e\x74\151\164\171\x5f\x69\144") : MSI_URL;
        $di = $dbIDPQueries->get_sp_from_issuer($Nv);
        $e6 = !empty($di) ? $di->id : null;
        $Ko = $dbIDPQueries->get_all_sp_attributes($e6);
        return array(MoIDPConstants::WS_FED_RESPONSE, $Nv, $QS, $E2, $QB, $di, $Ko, $Hi, $uM);
    }
    private function _send_response($zB, $Hi, $Oy)
    {
        if (!MSI_DEBUG) {
            goto u1;
        }
        MoIDPUtility::mo_debug("\123\x65\156\x64\x69\156\147\x20\123\x41\x4d\114\x20\x4c\x6f\x67\x69\156\40\122\145\x73\x70\157\156\x73\x65");
        u1:
        $zB = base64_encode($zB);
        echo "\15\xa\11\x9\74\x68\x74\155\154\76\xd\12\x9\11\11\x3c\150\145\x61\x64\76\15\xa\x9\11\x9\11\74\155\145\164\141\x20\150\164\x74\160\x2d\x65\161\165\x69\x76\x3d\x22\143\141\143\150\x65\55\143\157\x6e\x74\x72\x6f\154\x22\40\143\x6f\x6e\164\145\x6e\164\75\42\x6e\157\x2d\143\141\x63\x68\x65\42\x3e\15\12\x9\x9\11\11\x3c\x6d\x65\164\141\x20\x68\x74\164\x70\x2d\145\x71\165\151\166\75\42\x70\x72\141\x67\x6d\x61\42\x20\x63\x6f\x6e\x74\x65\156\x74\75\x22\156\x6f\x2d\143\141\143\150\x65\x22\76\xd\12\x9\11\11\74\x2f\x68\145\141\144\x3e\xd\12\x9\x9\11\x3c\142\x6f\144\171\76\xd\xa\11\11\x9\74\146\157\x72\x6d\40\151\144\x3d\x22\162\x65\163\x70\157\x6e\x73\145\x66\x6f\162\155\42\x20\141\143\x74\x69\157\156\x3d\x22" . $Oy . "\42\40\x6d\x65\x74\x68\x6f\144\x3d\x22\x70\x6f\163\x74\42\x3e\15\12\11\x9\11\x9\74\151\156\160\x75\x74\40\x74\x79\160\145\x3d\42\150\x69\x64\x64\145\156\x22\40\x6e\141\x6d\145\75\x22\x53\101\115\x4c\x52\x65\x73\x70\157\156\163\145\42\40\x76\x61\154\165\x65\x3d\x22" . htmlspecialchars($zB) . "\x22\x20\x2f\x3e";
        if (!($Hi != "\x2f")) {
            goto bu;
        }
        echo "\x3c\x69\156\160\165\164\x20\x74\171\160\145\x3d\42\150\151\144\x64\145\x6e\42\40\156\x61\x6d\x65\x3d\42\122\145\x6c\141\x79\123\x74\141\164\x65\42\40\x76\141\154\x75\145\75\x22" . $Hi . "\x22\40\x2f\x3e";
        bu:
        echo "\74\x2f\x66\157\162\155\76\xd\12\11\x9\11\74\57\142\x6f\x64\171\x3e\15\12\11\x9\74\163\143\162\x69\160\164\76\xd\xa\11\11\x9\x64\157\143\x75\x6d\145\156\164\56\147\x65\x74\105\154\x65\x6d\x65\x6e\164\102\x79\111\x64\50\x27\162\145\x73\x70\157\x6e\x73\145\146\157\x72\155\x27\51\x2e\x73\x75\142\x6d\x69\164\x28\x29\73\x9\15\12\11\11\74\57\163\143\x72\151\x70\164\76\xd\12\x9\11\74\57\x68\164\155\x6c\x3e";
        exit;
    }
    private function _send_ws_fed_response($vn, $Oy, $E2, $QS)
    {
        if (!MSI_DEBUG) {
            goto sl;
        }
        MoIDPUtility::mo_debug("\123\x65\x6e\144\151\156\147\x20\x57\x53\55\x46\x45\x44\40\x4c\x6f\147\151\156\40\122\145\163\x70\157\x6e\163\145");
        sl:
        echo "\xd\xa\11\11\x3c\150\164\x6d\154\76\15\12\11\x9\11\x3c\150\145\141\x64\x3e\15\xa\x9\11\11\x9\x3c\x6d\x65\164\x61\x20\x68\164\x74\160\55\x65\x71\x75\x69\166\75\42\143\141\143\x68\145\x2d\x63\x6f\x6e\x74\162\x6f\x6c\x22\40\143\157\x6e\x74\145\156\164\x3d\x22\x6e\x6f\55\x63\x61\x63\150\x65\x22\x3e\xd\xa\x9\11\x9\x9\74\155\x65\x74\x61\40\x68\164\164\160\55\145\161\165\151\x76\75\42\160\162\141\x67\x6d\x61\42\x20\x63\157\156\164\x65\x6e\164\75\x22\x6e\157\55\x63\141\x63\x68\145\x22\76\xd\xa\11\x9\x9\x3c\57\150\x65\141\144\76\15\xa\11\11\11\74\x62\157\x64\171\x3e\15\12\11\x9\11\x9\x3c\x66\157\162\x6d\x20\151\x64\x3d\42\x72\x65\x73\x70\157\156\x73\145\146\157\x72\x6d\42\40\x61\143\164\151\x6f\x6e\x3d\x22" . $Oy . "\42\x20\x6d\x65\x74\150\157\144\x3d\x22\x70\x6f\163\164\x22\x3e\xd\12\x9\11\x9\11\11\x3c\x69\156\160\x75\x74\40\164\171\x70\x65\75\42\150\151\x64\x64\145\156\x22\40\156\141\155\x65\75\42\167\x61\42\40\166\x61\x6c\165\x65\75\42" . $QS . "\x22\40\x2f\x3e\xd\xa\11\x9\11\xd\xa\x9\x9\11\11\11\74\x69\156\160\165\x74\x20\x74\x79\160\x65\x3d\42\x68\151\x64\144\x65\x6e\42\x20\156\141\x6d\145\x3d\x22\x77\162\x65\x73\x75\154\164\42\x20\166\x61\x6c\x75\145\75\42" . htmlentities($vn) . "\x22\x20\x2f\x3e\15\xa\x9\x9\11\x9\x9\x3c\x69\156\x70\x75\164\40\164\171\x70\145\x3d\x22\150\151\144\x64\145\x6e\42\40\x6e\141\155\x65\x3d\42\x77\143\164\x78\42\x20\166\x61\154\165\145\x3d\42" . $E2 . "\x22\40\57\x3e";
        echo "\11\74\x2f\146\157\x72\x6d\76\xd\12\11\x9\x9\x3c\57\x62\157\x64\x79\x3e\15\xa\11\11\11\74\163\143\162\x69\x70\x74\76\15\12\x9\11\11\11\x64\157\x63\165\155\x65\x6e\x74\x2e\147\x65\164\x45\154\145\155\x65\x6e\x74\x42\x79\111\144\50\47\162\x65\x73\x70\x6f\156\163\x65\x66\x6f\x72\155\x27\51\56\x73\165\x62\x6d\x69\x74\50\51\x3b\x9\15\12\x9\11\x9\74\57\163\x63\162\151\x70\x74\76\xd\12\11\11\x3c\x2f\x68\164\155\154\76";
        exit;
    }
    private function _send_jwt_response($L3, $ga)
    {
        if (!MSI_DEBUG) {
            goto yo;
        }
        MoIDPUtility::mo_debug("\x53\145\156\x64\151\x6e\x67\40\112\x57\124\40\114\x6f\x67\151\156\40\x52\x65\163\160\x6f\x6e\x73\x65");
        yo:
        $L3 = !MoIDPUtility::isBlank($ga) ? $L3 . "\46\162\x65\164\x75\x72\x6e\x5f\164\x6f\x3d" . $ga : $L3;
        header("\x4c\157\x63\141\164\x69\157\x6e\x3a\x20" . $L3);
        exit;
    }
    public function getJWTResponseParams($VW)
    {
        global $dbIDPQueries;
        $c9 = $VW["\152\167\x74\137\x65\156\x64\x70\x6f\151\x6e\x74"];
        $vs = $VW["\x73\x68\x61\162\145\144\123\x65\143\x72\x65\x74"];
        $ga = $VW["\x72\145\164\x75\x72\x6e\137\x74\157\137\165\x72\x6c"];
        $xj = is_multisite() ? get_sites() : null;
        $GX = is_null($xj) ? site_url("\x2f") : get_site_url($xj[0]->blog_id, "\x2f");
        $di = $dbIDPQueries->get_sp_from_acs($c9);
        $e6 = !empty($di) ? $di->id : null;
        $Ko = $dbIDPQueries->get_all_sp_attributes($e6);
        $B5 = $di->mo_idp_nameid_format;
        return array(MoIDPConstants::JWT_RESPONSE, $c9, $B5, $vs, $di, $Ko, $ga, NULL, NULL);
    }
    public function _send_logout_request($c1, $Hi, $zZ)
    {
        if (!MSI_DEBUG) {
            goto kb;
        }
        MoIDPUtility::mo_debug("\x53\145\156\144\x69\x6e\147\x20\x53\x41\115\x4c\40\x4c\x6f\147\157\165\164\40\122\x65\x71\x75\145\x73\164");
        kb:
        $y0 = htmlspecialchars($c1);
        $c1 = base64_encode($c1);
        echo "\x3c\146\x6f\162\155\x20\x69\x64\75\42\x72\145\161\x75\x65\x73\x74\x66\x6f\162\x6d\x22\x20\141\x63\x74\151\157\x6e\x3d\42" . $zZ . "\42\40\x6d\x65\164\x68\157\144\x3d\42\160\x6f\163\164\x22\x3e\15\xa\11\x9\x9\x3c\x69\x6e\160\x75\x74\40\164\x79\x70\x65\x3d\42\x68\x69\144\144\145\156\42\40\156\141\x6d\145\x3d\x22\123\101\x4d\114\122\145\161\x75\145\x73\x74\x22\40\166\141\154\165\145\x3d\42" . $c1 . "\42\40\x2f\76";
        if (!($Hi != "\57")) {
            goto p3;
        }
        echo "\74\151\x6e\160\165\x74\40\x74\x79\x70\x65\75\42\150\x69\x64\144\x65\156\42\x20\x6e\141\155\145\75\x22\122\145\154\x61\x79\123\164\141\x74\145\42\x20\166\141\x6c\x75\145\75\42" . $Hi . "\x22\40\57\76";
        p3:
        echo "\x3c\57\x66\x6f\162\155\x3e\xd\xa\x9\x9\74\x73\x63\162\151\x70\164\76\15\xa\11\x9\11\144\157\143\165\155\145\x6e\164\56\147\145\x74\105\x6c\x65\155\x65\156\164\102\x79\111\144\50\x22\x72\145\x71\165\x65\163\x74\146\x6f\162\155\x22\x29\56\163\165\x62\x6d\x69\x74\x28\51\73\11\15\12\11\x9\74\57\x73\143\162\x69\160\x74\76";
        exit;
    }
    public function _send_logout_response($zB, $Hi, $zZ)
    {
        if (!MSI_DEBUG) {
            goto Ze;
        }
        MoIDPUtility::mo_debug("\x53\145\x6e\x64\x69\156\x67\x20\x53\x41\x4d\x4c\40\x4c\157\147\157\165\164\x20\x52\x65\163\160\x6f\x6e\x73\x65");
        Ze:
        $zB = base64_encode($zB);
        $wG = htmlspecialchars($zB);
        echo "\74\146\157\162\155\x20\x69\144\75\42\x72\x65\163\160\x6f\156\x73\145\x66\x6f\162\155\x22\x20\141\143\164\x69\x6f\x6e\75\42" . $zZ . "\x22\x20\x6d\145\x74\x68\x6f\x64\75\42\160\157\x73\x74\42\76\xd\xa\x9\x9\11\11\x3c\151\156\160\x75\x74\40\164\171\160\145\75\42\x68\151\x64\144\x65\156\x22\x20\x6e\141\x6d\x65\x3d\42\123\101\115\114\x52\x65\163\160\157\x6e\163\145\42\40\166\x61\154\x75\x65\75\42" . $wG . "\x22\x2f\76\xd\12\11\x9\x9\x9\74\151\x6e\x70\165\x74\x20\164\171\x70\x65\75\42\150\151\x64\x64\145\156\x22\40\156\x61\x6d\145\75\42\122\x65\x6c\x61\x79\123\164\x61\x74\145\x22\x20\x76\141\154\165\x65\75\x22" . $Hi . "\x22\40\x2f\76\xd\12\11\x9\x9\x9\74\x2f\x66\x6f\162\155\x3e\15\xa\x9\x9\x9\74\163\x63\162\151\160\x74\x3e\xd\xa\x9\x9\x9\11\x64\157\x63\x75\155\x65\156\x74\56\147\145\164\105\154\x65\x6d\145\x6e\164\x42\171\x49\144\50\42\x72\x65\163\x70\x6f\x6e\163\145\146\157\162\x6d\42\x29\56\163\165\x62\155\x69\164\50\x29\73\11\15\xa\x9\11\x9\x3c\57\163\x63\162\151\160\164\76";
        exit;
    }
    public function mo_idp_send_logout_response($QB, $vN, $Li)
    {
        global $dbIDPQueries;
        if (!MSI_DEBUG) {
            goto xI;
        }
        MoIDPUtility::mo_debug("\x47\145\x6e\145\x72\x61\x74\151\156\147\40\x53\101\115\x4c\x20\x4c\x6f\147\157\165\164\x20\x52\x65\163\x70\x6f\156\163\145");
        xI:
        if (!isset($_SESSION["\x6d\157\137\x69\144\x70\137\x6c\x6f\x67\x6f\165\164\137\x72\x65\x71\x75\145\163\164\137\x69\163\163\x75\145\x72"])) {
            goto im;
        }
        unset($_SESSION["\x6d\x6f\137\151\144\x70\x5f\x6c\x6f\x67\x6f\165\164\x5f\x72\x65\x71\x75\x65\x73\164\x5f\x69\163\x73\165\x65\x72"]);
        im:
        if (!isset($_SESSION["\x6d\157\137\151\144\160\137\x6c\157\x67\x6f\165\x74\137\162\x65\x71\165\x65\163\x74\137\151\x64"])) {
            goto rb;
        }
        unset($_SESSION["\155\x6f\x5f\151\x64\160\x5f\154\157\147\x6f\x75\164\x5f\x72\x65\x71\x75\145\x73\164\137\x69\144"]);
        rb:
        if (!isset($_SESSION["\x6d\157\137\x69\144\x70\137\x6c\157\x67\x6f\165\x74\137\162\x65\x6c\141\171\137\x73\164\141\164\145"])) {
            goto K0;
        }
        unset($_SESSION["\155\x6f\137\151\144\160\x5f\x6c\x6f\x67\x6f\165\164\137\162\x65\154\141\x79\137\x73\164\x61\x74\x65"]);
        K0:
        if (!ob_get_contents()) {
            goto D5;
        }
        ob_clean();
        D5:
        MoIDPUtility::unsetCookieVariables(array("\x6d\157\137\151\144\x70\x5f\154\x6f\147\x6f\x75\x74\137\x72\x65\161\165\145\163\x74\x5f\x69\163\x73\x75\145\x72", "\x6d\x6f\137\151\144\160\x5f\154\x6f\147\x6f\x75\x74\137\x72\145\161\x75\x65\x73\x74\x5f\151\x64", "\x6d\157\x5f\151\144\x70\x5f\154\157\x67\157\165\164\x5f\162\145\x6c\x61\171\137\163\x74\x61\x74\145", "\x6d\157\137\x73\160\137\x63\x6f\165\156\x74", "\155\157\x5f\163\160\137\61\x5f\x69\163\x73\x75\145\x72"));
        $di = $dbIDPQueries->get_sp_from_issuer($QB);
        $QB = get_site_option("\x6d\x6f\x5f\x69\144\x70\x5f\x65\x6e\164\151\x74\x79\x5f\x69\144") ? get_site_option("\x6d\x6f\137\151\x64\160\x5f\145\x6e\164\x69\x74\171\137\151\x64") : MSI_URL;
        $Ru = $di->mo_idp_logout_url;
        $fc = $di->mo_idp_logout_binding_type;
        if ($fc == "\110\x74\x74\160\x52\145\144\151\x72\x65\x63\164") {
            goto hw;
        }
        $Y1 = SAMLUtilities::createLogoutResponse($vN, $QB, $Ru, "\110\164\164\x70\x50\x6f\x73\x74");
        $KL = MoIDPUtility::getPrivateKeyPath();
        $NT = MoIDPUtility::getPublicCertPath();
        $zI = SAMLUtilities::signXML($Y1, $NT, $KL, "\x53\164\x61\164\x75\x73");
        $this->_send_logout_response($zI, $Li, $Ru);
        goto AK;
        hw:
        $Y1 = SAMLUtilities::createLogoutResponse($vN, $QB, $Ru);
        $Pr = $Ru;
        $Pr .= strpos($Ru, "\77") !== FALSE ? "\46" : "\x3f";
        $Pr .= "\123\101\115\x4c\122\x65\163\x70\x6f\x6e\163\145\x3d" . $Y1 . "\46\122\145\154\141\171\x53\x74\141\164\145\75" . urlencode($Li) . "\x26\x53\151\147\x41\154\147\x3d" . urlencode(XMLSecurityKey::RSA_SHA256);
        $Bj = array("\164\x79\160\145" => "\160\162\151\x76\x61\x74\x65");
        $xO = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $Bj);
        $uH = MoIDPUtility::getPrivateKeyPath();
        $xO->loadKey($uH, TRUE);
        $c2 = $xO->signData($Pr);
        $c2 = base64_encode($c2);
        $N5 = $Pr;
        $N5 .= strpos($N5, "\x3f") !== false ? "\x26" : "\77";
        $N5 .= "\x53\x69\147\x6e\x61\x74\x75\162\x65\75" . urlencode($c2);
        header("\x4c\157\x63\141\164\x69\157\156\72\x20" . $N5);
        exit;
        AK:
    }
    public function mo_idp_send_logout_request($Pb, $QB, $Pr, $fc, $FW)
    {
        if (!MSI_DEBUG) {
            goto Tl;
        }
        MoIDPUtility::mo_debug("\x47\x65\x6e\x65\162\141\164\x69\x6e\x67\40\x53\101\115\114\40\x4c\157\x67\157\165\164\x20\122\145\x71\x75\145\163\x74");
        Tl:
        $Hi = "\57";
        if ($fc == "\x48\164\164\x70\120\x6f\x73\x74") {
            goto WM;
        }
        $fr = SAMLUtilities::createLogoutRequest($Pb, $FW, $QB, $Pr);
        $fr = "\x53\101\x4d\x4c\x52\145\x71\165\145\x73\x74\x3d" . $fr . "\46\x52\x65\x6c\141\x79\123\x74\141\x74\145\75" . urlencode($Hi) . "\46\123\x69\x67\x41\154\x67\75" . urlencode(XMLSecurityKey::RSA_SHA256);
        $Bj = array("\x74\x79\x70\x65" => "\160\x72\x69\x76\141\x74\x65");
        $xO = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $Bj);
        $uH = MoIDPUtility::getPrivateKeyPath();
        $xO->loadKey($uH, TRUE);
        $c2 = $xO->signData($fr);
        $c2 = base64_encode($c2);
        $N5 = $Pr;
        $N5 .= strpos($Pr, "\x3f") !== false ? "\x26" : "\77";
        $N5 .= $fr . "\x26\123\x69\147\156\x61\164\165\162\x65\75" . urlencode($c2);
        header("\114\157\x63\141\x74\151\157\156\72\40" . $N5);
        exit;
        goto md;
        WM:
        $fr = SAMLUtilities::createLogoutRequest($Pb, $FW, $QB, $Pr, "\110\164\164\x70\x50\157\163\164");
        $KL = MoIDPUtility::getPrivateKeyPath();
        $NT = MoIDPUtility::getPublicCertPath();
        $c1 = SAMLUtilities::signXML($fr, $NT, $KL, "\116\x61\155\x65\x49\104");
        $this->_send_logout_request($c1, $Hi, $Pr);
        md:
    }
}
