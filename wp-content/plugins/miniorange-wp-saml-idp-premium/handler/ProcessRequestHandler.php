<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\SAML2\AuthnRequest;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\WSFED\WsFedRequest;
final class ProcessRequestHandler extends BaseHandler
{
    use Instance;
    private $sendResponseHandler;
    private function __construct()
    {
        $this->sendResponseHandler = SendResponseHandler::instance();
    }
    public function mo_idp_authorize_user($Hi, $aD)
    {
        switch ($aD->getRequestType()) {
            case MoIDPConstants::AUTHN_REQUEST:
                $this->startProcessForSamlResponse($Hi, $aD);
                goto et;
            case MoIDPConstants::WS_FED:
                $this->startProcessForWsFedResponse($Hi, $aD);
                goto et;
        }
        r1:
        et:
    }
    public function startProcessForSamlResponse($Hi, $aD)
    {
        if (is_user_logged_in()) {
            goto BO;
        }
        $this->setSAMLSessionCookies($aD, $Hi);
        goto M7;
        BO:
        $this->sendResponseHandler->mo_idp_send_response(array("\x72\145\x71\165\145\163\164\x54\x79\160\145" => $aD->getRequestType(), "\x61\x63\x73\137\165\x72\x6c" => $aD->getAssertionConsumerServiceURL(), "\151\x73\163\165\x65\162" => $aD->getIssuer(), "\162\x65\154\x61\x79\x53\x74\141\x74\x65" => $Hi, "\162\145\161\x75\x65\163\x74\111\104" => $aD->getRequestID()));
        M7:
    }
    public function startProcessForWsFedResponse($Hi, $aD)
    {
        if (is_user_logged_in()) {
            goto bq;
        }
        $this->setWSFedSessionCookies($aD, $Hi);
        goto Sw;
        bq:
        $this->sendResponseHandler->mo_idp_send_response(array("\x72\145\161\x75\145\x73\164\x54\x79\x70\x65" => $aD->getRequestType(), "\x63\x6c\x69\145\x6e\164\x52\145\161\165\145\163\164\x49\144" => $aD->getClientRequestId(), "\x77\164\x72\x65\141\154\155" => $aD->getWtrealm(), "\167\x61" => $aD->getWa(), "\162\x65\154\x61\171\123\x74\141\x74\145" => $Hi, "\167\143\x74\x78" => $aD->getWctx()));
        Sw:
    }
    public function setWSFedSessionCookies(WsFedRequest $aD, $Hi)
    {
        if (!ob_get_contents()) {
            goto FE;
        }
        ob_clean();
        FE:
        setcookie("\162\145\x73\160\157\156\163\x65\x5f\160\x61\x72\141\x6d\x73", "\151\x73\x53\x65\164", time() + 21600, "\x2f");
        setcookie("\155\157\x49\x64\160\163\x65\156\x64\x57\x73\x46\145\x64\122\145\163\x70\x6f\x6e\163\145", "\x74\x72\x75\x65", time() + 21600, "\x2f");
        setcookie("\167\164\x72\x65\x61\x6c\x6d", $aD->getWtrealm(), time() + 21600, "\57");
        setcookie("\167\x61", $aD->getWa(), time() + 21600, "\x2f");
        setcookie("\167\143\164\170", $aD->getWctx(), time() + 21600, "\57");
        setcookie("\162\145\x6c\x61\x79\x53\x74\141\164\145", $Hi, time() + 21600, "\x2f");
        setcookie("\x63\154\x69\x65\156\x74\122\145\x71\x75\145\163\x74\x49\144", $aD->getClientRequestId(), time() + 21600, "\x2f");
        $L3 = wp_login_url();
        $L3 = apply_filters("\x6d\157\137\x69\x64\x70\137\143\x75\163\x74\x6f\155\137\154\x6f\147\x69\x6e\137\165\x72\154", $L3);
        wp_safe_redirect($L3);
        exit;
    }
    public function setSAMLSessionCookies(AuthnRequest $aD, $Hi)
    {
        if (!ob_get_contents()) {
            goto bf;
        }
        ob_clean();
        bf:
        setcookie("\162\145\163\x70\x6f\x6e\x73\145\137\160\141\162\x61\155\163", "\151\x73\x53\145\164", time() + 21600, "\57");
        setcookie("\155\x6f\x49\144\x70\163\145\156\x64\x53\x41\115\114\x52\x65\163\160\157\x6e\x73\145", "\164\162\165\145", time() + 21600, "\57");
        setcookie("\141\x63\x73\x5f\x75\x72\154", $aD->getAssertionConsumerServiceURL(), time() + 21600, "\57");
        setcookie("\x61\x75\x64\x69\x65\156\x63\x65", $aD->getIssuer(), time() + 21600, "\x2f");
        setcookie("\162\145\x6c\141\x79\x53\164\141\x74\145", $Hi, time() + 21600, "\57");
        setcookie("\x72\145\x71\x75\x65\163\164\x49\x44", $aD->getRequestID(), time() + 21600, "\57");
        $L3 = wp_login_url();
        $L3 = apply_filters("\x6d\157\x5f\x69\144\x70\x5f\x63\x75\163\x74\x6f\155\x5f\x6c\157\x67\x69\x6e\137\165\x72\154", $L3);
        wp_safe_redirect($L3);
        exit;
    }
    public function setJWTSessionCookies(array $aD, $Hi)
    {
        if (!ob_get_contents()) {
            goto DL;
        }
        ob_clean();
        DL:
        setcookie("\x72\145\163\x70\157\156\163\x65\x5f\x70\141\162\x61\x6d\163", "\151\x73\x53\x65\164", time() + 21600, "\x2f");
        setcookie("\155\157\111\144\160\123\x65\x6e\144\112\127\x54\122\145\x73\160\x6f\x6e\163\145", "\164\162\165\x65", time() + 21600, "\57");
        setcookie("\152\167\164\x5f\145\156\x64\160\157\151\x6e\x74", $aD["\152\x77\x74\x5f\x65\x6e\144\x70\x6f\151\156\164"], time() + 21600, "\57");
        setcookie("\x73\150\x61\x72\x65\x64\x53\x65\x63\x72\145\164", $aD["\x73\x68\141\x72\x65\144\x53\x65\143\162\145\x74"], time() + 21600, "\57");
        setcookie("\x72\145\x74\x75\162\x6e\x5f\164\x6f\137\x75\x72\x6c", $Hi, time() + 21600, "\x2f");
        $L3 = wp_login_url();
        $L3 = apply_filters("\155\157\137\x69\x64\160\x5f\143\165\163\164\x6f\155\x5f\154\157\147\151\156\137\x75\162\154", $L3);
        wp_safe_redirect($L3);
        exit;
    }
    public function checkAndLogoutUserFromLoggedInSPs($Iu)
    {
        if (!MSI_DEBUG) {
            goto dP;
        }
        MoIDPUtility::mo_debug("\103\x68\x65\143\x6b\151\x6e\147\x20\151\x66\x20\164\150\145\x72\x65\x27\163\x20\141\x20\166\141\x6c\x69\144\x20\123\120\40\163\x65\x73\163\151\x6f\x6e");
        dP:
        if (!MSI_DEBUG) {
            goto hW;
        }
        MoIDPUtility::mo_debug("\x53\x50\x20\x43\157\165\156\164\40\x3a\40" . $_COOKIE["\x6d\x6f\x5f\163\160\x5f\143\x6f\165\x6e\x74"]);
        hW:
        if (!(!isset($_COOKIE["\155\x6f\x5f\x73\160\x5f\x63\x6f\165\156\x74"]) || $_COOKIE["\x6d\x6f\x5f\163\160\x5f\143\157\x75\156\x74"] < 1)) {
            goto p_;
        }
        return;
        p_:
        if (isset($_SESSION["\155\157\137\151\144\160\137\154\157\147\x6f\x75\x74\137\162\145\x71\165\x65\163\x74\x5f\151\x73\x73\x75\145\x72"])) {
            goto p9;
        }
        $this->mo_idp_initiated_logout($Iu);
        return;
        p9:
        $this->checkAndSwapSPInSessionForLogout($_COOKIE["\155\x6f\x5f\163\x70\137\x31\x5f\x69\163\x73\x75\145\162"], $_SESSION["\155\157\137\151\144\x70\137\x6c\157\147\157\165\164\x5f\x72\145\x71\x75\x65\x73\164\x5f\151\x73\163\x75\145\162"], $_COOKIE["\x6d\157\x5f\163\160\x5f\x63\x6f\165\156\164"]);
        $this->mo_idp_sp_initiated_logout($Iu);
    }
    public function processLogoutResponseFromSP()
    {
        if (!(isset($_COOKIE["\155\x6f\137\x69\x64\x70\137\154\x61\x73\x74\137\x6c\x6f\x67\x67\145\144\x5f\x69\156\x5f\165\163\145\162"]) && !MoIDPUtility::isBlank($_COOKIE["\x6d\157\137\151\x64\x70\x5f\x6c\141\163\164\x5f\154\157\x67\x67\145\144\137\x69\x6e\x5f\x75\163\145\162"]))) {
            goto jV;
        }
        $Iu = $_COOKIE["\155\x6f\137\151\144\160\x5f\x6c\141\163\x74\x5f\x6c\157\x67\147\145\x64\137\151\156\x5f\165\163\145\x72"];
        jV:
        if (!(!array_key_exists("\x6d\x6f\x5f\x73\x70\137\x63\157\165\x6e\x74", $_COOKIE) || MoIDPUtility::isBlank($_COOKIE["\x6d\157\137\x73\x70\137\143\157\x75\x6e\x74"]) || $_COOKIE["\x6d\x6f\x5f\163\x70\137\x63\x6f\165\156\164"] == 0)) {
            goto Bt;
        }
        wp_redirect(site_url());
        exit;
        Bt:
        if (isset($_COOKIE["\x6d\157\x5f\151\x64\160\x5f\154\157\147\x6f\x75\164\137\x72\145\161\x75\x65\x73\x74\137\151\163\163\165\145\x72"]) && !MoIDPUtility::isBlank($_COOKIE["\x6d\157\137\151\144\160\137\x6c\157\x67\x6f\165\164\x5f\162\145\161\x75\145\163\164\x5f\151\163\163\165\145\x72"])) {
            goto cI;
        }
        $this->mo_idp_initiated_logout($Iu);
        goto Ji;
        cI:
        $this->mo_idp_sp_initiated_logout($Iu);
        Ji:
    }
    private function checkAndSwapSPInSessionForLogout($J4, $OU, $Fs)
    {
        if (!(strpos($J4, $OU) === false)) {
            goto Kw;
        }
        $N2 = '';
        $rM = 1;
        jU:
        if (!($rM <= $Fs)) {
            goto yO;
        }
        if (!(strpos($_COOKIE["\155\157\x5f\163\160\x5f" . $rM . "\x5f\151\163\x73\x75\145\x72"], $OU) !== false)) {
            goto Ls;
        }
        $N2 = $rM;
        goto yO;
        Ls:
        j7:
        $rM++;
        goto jU;
        yO:
        if (!ob_get_contents()) {
            goto Vi;
        }
        ob_clean();
        Vi:
        $AR = $_COOKIE["\155\157\x5f\x73\160\137" . $N2 . "\x5f\151\x73\163\x75\145\162"];
        $Og = $_COOKIE["\155\157\137\163\160\x5f" . $N2 . "\x5f\163\x65\163\x73\x69\x6f\156\x49\156\144\x65\x78"];
        $k8 = $_COOKIE["\x6d\157\137\163\160\137\61\x5f\x73\145\163\163\151\x6f\x6e\111\x6e\x64\145\x78"];
        setcookie("\155\157\x5f\x73\160\137" . $N2 . "\137\151\163\163\x75\x65\162", $J4, time() + 21600, "\x2f");
        setcookie("\155\157\x5f\x73\x70\x5f" . $N2 . "\137\163\x65\163\x73\151\x6f\x6e\111\156\144\x65\x78", $k8, time() + 21600, "\x2f");
        $_COOKIE["\x6d\157\137\x73\160\137" . $N2 . "\x5f\151\x73\x73\165\x65\162"] = $J4;
        $_COOKIE["\x6d\157\137\x73\x70\137" . $N2 . "\x5f\163\x65\163\163\151\x6f\156\x49\156\144\145\x78"] = $k8;
        setcookie("\x6d\157\137\x73\160\137\x31\137\151\x73\163\x75\x65\162", $AR, time() + 21600, "\x2f");
        setcookie("\155\x6f\137\163\160\x5f\x31\x5f\x73\145\x73\163\x69\157\156\x49\x6e\144\x65\x78", $Og, time() + 21600, "\57");
        $_COOKIE["\155\157\137\163\x70\x5f\x31\x5f\x69\163\x73\x75\x65\x72"] = $AR;
        $_COOKIE["\x6d\x6f\x5f\163\160\x5f\x31\137\163\x65\x73\163\x69\157\x6e\111\156\x64\x65\170"] = $Og;
        Kw:
    }
    private function mo_idp_initiated_logout($Iu)
    {
        if (!MSI_DEBUG) {
            goto XF;
        }
        MoIDPUtility::mo_debug("\120\162\145\x70\x61\x72\151\x6e\147\40\x6f\x75\x74\40\x49\x44\x50\40\151\156\x69\164\151\141\x74\145\144\40\x6c\x6f\147\157\x75\164");
        XF:
        if (!ob_get_contents()) {
            goto D7;
        }
        ob_clean();
        D7:
        global $dbIDPQueries;
        $current_user = get_user_by("\111\x44", $Iu);
        $C4 = isset($_COOKIE["\x6d\157\x5f\x73\160\137\x63\x6f\x75\x6e\164"]) ? $_COOKIE["\x6d\x6f\137\163\160\137\143\157\165\156\164"] : 0;
        if (!isset($_COOKIE["\x6d\x6f\137\x73\160\x5f\143\x6f\x75\x6e\x74"])) {
            goto n5;
        }
        setcookie("\155\157\137\163\x70\x5f\x63\x6f\x75\156\164", $C4 - 1);
        n5:
        if (!($C4 < 1)) {
            goto qa;
        }
        return;
        qa:
        if (!($C4 == 1)) {
            goto tm;
        }
        MoIDPUtility::unsetCookieVariables(array("\x6d\157\137\163\x70\137\143\x6f\165\x6e\x74"));
        tm:
        $QB = $_COOKIE["\x6d\x6f\137\163\160\x5f" . $C4 . "\137\151\163\163\165\145\x72"];
        $FW = $_COOKIE["\x6d\x6f\x5f\x73\x70\137" . $C4 . "\137\163\x65\x73\x73\x69\157\x6e\x49\156\144\x65\170"];
        MoIDPUtility::unsetCookieVariables(array("\155\x6f\137\163\x70\137" . $C4 . "\137\151\x73\163\165\145\x72", "\x6d\x6f\137\163\160\x5f" . $C4 . "\x5f\163\145\x73\163\x69\157\x6e\111\156\x64\145\170"));
        $di = $dbIDPQueries->get_sp_from_issuer($QB);
        if (!MSI_DEBUG) {
            goto QM;
        }
        MoIDPUtility::mo_debug("\x53\145\x6e\x64\151\156\147\x20\x6f\x75\x74\x20\x49\104\x50\40\151\156\x69\x74\x69\x61\x74\x65\144\40\154\x6f\x67\x6f\x75\164\x20\162\145\161\x75\145\163\164\x20\164\157\40\72\x20" . $QB);
        QM:
        $ek = get_site_option("\155\x6f\137\151\144\160\137\145\x6e\164\151\x74\x79\137\151\x64") ? get_site_option("\155\157\137\x69\x64\x70\137\x65\x6e\164\151\x74\171\x5f\x69\x64") : MSI_URL;
        if (MoIDPUtility::isBlank($di->mo_idp_logout_url)) {
            goto M4;
        }
        $this->sendResponseHandler->mo_idp_send_logout_request($di->mo_idp_nameid_attr === "\x65\x6d\x61\151\154\101\144\x64\x72\x65\163\163" ? $current_user->user_email : $current_user->user_login, $ek, $di->mo_idp_logout_url, $di->mo_idp_logout_binding_type, $FW);
        M4:
    }
    private function mo_idp_sp_initiated_logout($Iu)
    {
        if (!MSI_DEBUG) {
            goto vW;
        }
        MoIDPUtility::mo_debug("\x53\145\x6e\x64\x69\156\147\40\x6f\165\x74\x20\x53\x50\40\151\156\x69\x74\151\141\x74\145\x64\x20\154\157\x67\157\165\x74\x20\x72\145\163\160\x6f\156\x73\145");
        vW:
        $C4 = $_COOKIE["\155\x6f\137\163\160\x5f\143\x6f\165\x6e\164"];
        $QB = $_COOKIE["\155\157\137\163\x70\137" . $C4 . "\137\x69\163\x73\x75\x65\x72"];
        if ($C4 == 1) {
            goto U1;
        }
        if (isset($_COOKIE["\x6d\157\137\151\x64\x70\137\x6c\157\x67\157\x75\x74\x5f\162\x65\x71\x75\x65\163\x74\x5f\151\163\x73\165\x65\162"])) {
            goto FD;
        }
        if (!ob_get_contents()) {
            goto nJ;
        }
        ob_clean();
        nJ:
        setcookie("\155\x6f\137\151\x64\x70\137\x6c\157\147\157\x75\x74\137\x72\145\x71\165\x65\x73\x74\137\x69\163\x73\165\x65\x72", $_SESSION["\x6d\157\137\151\x64\160\137\x6c\x6f\x67\x6f\x75\164\137\162\145\161\165\145\x73\164\137\x69\x73\x73\x75\x65\162"], time() + 21600, "\x2f");
        setcookie("\x6d\x6f\x5f\x69\x64\x70\x5f\x6c\157\147\157\165\x74\x5f\x72\x65\x6c\x61\x79\x5f\x73\x74\141\x74\145", $_SESSION["\155\x6f\137\x69\144\160\137\x6c\x6f\x67\157\x75\164\x5f\162\x65\154\x61\171\137\x73\164\141\164\145"], time() + 21600, "\x2f");
        setcookie("\155\x6f\x5f\151\144\160\137\x6c\157\147\157\165\164\x5f\x72\145\x71\165\145\163\x74\x5f\151\144", $_SESSION["\x6d\x6f\137\151\x64\x70\x5f\154\157\x67\157\165\164\137\x72\x65\x71\x75\145\163\x74\137\x69\144"], time() + 21600, "\57");
        FD:
        $this->mo_idp_initiated_logout($Iu);
        goto Wa;
        U1:
        $I_ = isset($_COOKIE["\155\x6f\137\151\144\160\x5f\154\x6f\x67\x6f\165\x74\137\162\x65\161\x75\145\163\x74\x5f\x69\x73\x73\x75\145\162"]) ? $_COOKIE["\x6d\x6f\137\x69\x64\x70\137\x6c\x6f\x67\157\165\x74\137\162\145\161\x75\145\163\164\x5f\151\163\163\x75\145\x72"] : $_SESSION["\155\x6f\x5f\x69\144\160\x5f\154\x6f\147\x6f\165\x74\x5f\x72\x65\x71\x75\x65\x73\x74\x5f\151\x73\163\165\145\162"];
        $vN = isset($_COOKIE["\155\x6f\137\151\x64\x70\137\154\157\147\157\x75\164\137\x72\145\x71\x75\145\163\x74\x5f\151\144"]) ? $_COOKIE["\x6d\x6f\137\x69\x64\160\137\x6c\157\x67\157\x75\164\x5f\162\145\x71\165\145\163\164\x5f\151\144"] : $_SESSION["\155\x6f\x5f\x69\144\x70\137\154\x6f\x67\x6f\165\164\x5f\162\x65\161\x75\x65\x73\x74\137\151\x64"];
        $Li = urldecode(isset($_COOKIE["\155\157\137\151\144\160\137\154\157\147\157\x75\164\137\x72\x65\154\141\171\x5f\163\164\x61\164\x65"]) ? $_COOKIE["\155\x6f\137\x69\x64\160\137\154\x6f\x67\157\x75\164\x5f\x72\x65\x6c\x61\171\137\x73\x74\141\164\145"] : $_SESSION["\x6d\157\x5f\x69\144\160\x5f\x6c\157\x67\x6f\x75\164\x5f\162\x65\154\x61\171\x5f\163\x74\141\x74\145"]);
        $this->sendResponseHandler->mo_idp_send_logout_response($I_, $vN, $Li);
        Wa:
    }
}
