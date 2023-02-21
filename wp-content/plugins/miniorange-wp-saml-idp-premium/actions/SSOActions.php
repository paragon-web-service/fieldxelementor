<?php


namespace IDP\Actions;

use IDP\Exception\InvalidRequestInstantException;
use IDP\Handler\ProcessRequestHandler;
use IDP\Handler\ReadRequestHandler;
use IDP\Handler\SendResponseHandler;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\SAML2\AuthnRequest;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Exception\NotRegisteredException;
use IDP\Exception\InvalidRequestVersionException;
use IDP\Exception\InvalidServiceProviderException;
use IDP\Exception\InvalidSignatureInRequestException;
use IDP\Exception\InvalidSSOUserException;
use IDP\Exception\LicenseExpiredException;
class SSOActions
{
    use Instance;
    private $readRequestHandler;
    private $sendResponseHandler;
    private $requestProcessHandler;
    private $requestParams = array("\123\x41\x4d\x4c\x52\145\161\165\145\x73\164", "\x6f\x70\164\x69\157\156", "\x77\164\162\x65\x61\154\155", "\123\x41\115\x4c\x52\x65\x73\160\x6f\156\x73\145");
    private function __construct()
    {
        $this->readRequestHandler = ReadRequestHandler::instance();
        $this->sendResponseHandler = SendResponseHandler::instance();
        $this->requestProcessHandler = ProcessRequestHandler::instance();
        add_action("\x69\156\151\x74", array($this, "\x5f\150\141\x6e\144\154\x65\137\123\123\x4f"));
        add_action("\x77\x70\137\x6c\x6f\x67\x69\x6e", array($this, "\155\x6f\x5f\x69\x64\x70\137\150\x61\156\x64\154\145\137\x70\157\x73\x74\x5f\154\157\147\151\x6e"), 99);
        add_action("\167\160\x5f\x6c\157\x67\x6f\x75\x74", array($this, "\155\157\x5f\151\144\160\137\154\x6f\x67\157\165\164"), 1, 1);
        add_filter("\x6d\157\144\x69\x66\x79\x5f\163\141\x6d\154\137\141\x74\x74\x72\x5f\166\141\x6c\165\145", array($this, "\x6d\157\x64\x69\146\x79\x55\x73\x65\x72\101\164\x74\162\151\142\x75\x74\145\104\x61\x74\145\x56\141\154\x75\145"), 1, 1);
        add_action("\155\157\137\x63\x68\x65\x63\x6b\x5f\142\145\146\x6f\162\x65\137\x6d\151\x73\162", array($this, "\155\157\x5f\151\x64\160\x5f\162\x65\163\164\162\151\x63\164\137\x75\x73\x65\x72\x73"), 1, 3);
        add_filter("\x6d\x6f\x5f\x69\144\160\137\x63\x75\x73\164\157\x6d\137\x6c\157\147\x69\x6e\137\165\162\154", array($this, "\x6d\157\137\x69\144\160\137\x72\x65\x74\x75\162\156\x5f\x63\165\163\164\x6f\155\137\154\157\x67\x69\156"), 10, 1);
    }
    public function _handle_SSO()
    {
        $ua = array_keys($_REQUEST);
        $ZX = array_intersect($ua, $this->requestParams);
        if (!(count($ZX) <= 0)) {
            goto ZB;
        }
        return;
        ZB:
        try {
            $this->_route_data(array_values($ZX)[0]);
        } catch (NotRegisteredException $S5) {
            if (!MSI_DEBUG) {
                goto BA;
            }
            MoIDPUtility::mo_debug("\x45\x78\143\145\160\x74\x69\157\x6e\x20\117\143\143\165\x72\x72\x65\x64\40\144\x75\162\151\156\147\x20\123\123\x4f\x20" . $S5);
            BA:
            wp_die(MoIDPMessages::SAML_INVALID_OPERATION);
        } catch (InvalidRequestInstantException $S5) {
            if (!MSI_DEBUG) {
                goto dl;
            }
            MoIDPUtility::mo_debug("\105\170\143\x65\x70\x74\x69\157\156\x20\117\143\143\x75\x72\162\145\144\40\144\x75\x72\151\156\147\40\x53\x53\x4f\40" . $S5);
            dl:
            wp_die($S5->getMessage());
        } catch (InvalidRequestVersionException $S5) {
            if (!MSI_DEBUG) {
                goto JL;
            }
            MoIDPUtility::mo_debug("\105\170\x63\145\x70\164\151\x6f\x6e\x20\x4f\143\143\x75\162\x72\x65\144\x20\144\x75\x72\151\x6e\147\40\123\123\x4f\40" . $S5);
            JL:
            wp_die($S5->getMessage());
        } catch (InvalidServiceProviderException $S5) {
            if (!MSI_DEBUG) {
                goto JN;
            }
            MoIDPUtility::mo_debug("\x45\170\143\145\160\x74\x69\157\x6e\x20\x4f\143\143\165\162\x72\145\144\x20\144\165\x72\x69\156\147\40\123\x53\117\40" . $S5);
            JN:
            wp_die($S5->getMessage());
        } catch (InvalidSignatureInRequestException $S5) {
            if (!MSI_DEBUG) {
                goto Vy;
            }
            MoIDPUtility::mo_debug("\105\170\x63\x65\160\x74\151\x6f\156\40\x4f\143\143\165\162\162\145\x64\40\144\165\x72\x69\156\x67\40\123\123\x4f\40" . $S5);
            Vy:
            wp_die($S5->getMessage());
        } catch (InvalidSSOUserException $S5) {
            if (!MSI_DEBUG) {
                goto ko;
            }
            MoIDPUtility::mo_debug("\x45\170\x63\x65\160\164\151\x6f\x6e\40\117\143\x63\165\162\162\145\x64\x20\x64\x75\162\151\x6e\x67\40\x53\123\117\x20" . $S5);
            ko:
            wp_die($S5->getMessage());
        } catch (LicenseExpiredException $S5) {
            if (!MSI_DEBUG) {
                goto n2;
            }
            MoIDPUtility::mo_debug("\x45\x78\x63\145\160\164\151\x6f\156\40\x4f\x63\143\x75\x72\x72\145\144\40\144\x75\162\x69\156\x67\40\x53\x53\117\40" . $S5);
            n2:
            wp_die($S5->getMessage());
        } catch (\Exception $S5) {
            if (!MSI_DEBUG) {
                goto cq;
            }
            MoIDPUtility::mo_debug("\105\170\143\x65\160\x74\151\157\156\x20\117\x63\x63\165\162\x72\145\x64\x20\x64\x75\162\x69\156\x67\40\123\123\x4f\x20" . $S5);
            cq:
            wp_die($S5->getMessage());
        }
    }
    public function _route_data($tt)
    {
        switch ($tt) {
            case $this->requestParams[0]:
                $this->readRequestHandler->_read_request($_REQUEST, $_GET, MoIDPConstants::SAML);
                goto Xi;
            case $this->requestParams[1]:
                $this->_initiate_saml_response($_REQUEST);
                goto Xi;
            case $this->requestParams[2]:
                $this->readRequestHandler->_read_request($_REQUEST, $_GET, MoIDPConstants::WS_FED);
                goto Xi;
            case $this->requestParams[3]:
                $this->readRequestHandler->_read_saml_response($_REQUEST, $_GET);
                goto Xi;
        }
        gJ:
        Xi:
    }
    public function mo_idp_handle_post_login($ur)
    {
        if (!(array_key_exists("\162\145\163\x70\x6f\x6e\163\x65\137\x70\x61\162\141\x6d\163", $_COOKIE) && !MoIDPUtility::isBlank($_COOKIE["\162\x65\163\x70\x6f\x6e\163\145\x5f\160\141\x72\141\x6d\163"]))) {
            goto Fo;
        }
        try {
            if (!(isset($_COOKIE["\x6d\157\111\144\160\163\x65\x6e\x64\123\x41\115\x4c\x52\145\163\160\x6f\156\x73\x65"]) && strcmp($_COOKIE["\155\x6f\x49\144\160\x73\145\156\x64\123\x41\115\x4c\x52\145\x73\x70\157\x6e\x73\x65"], "\164\162\165\145") == 0)) {
                goto kT;
            }
            $this->sendResponseHandler->mo_idp_send_response(["\162\x65\x71\x75\x65\x73\164\124\x79\x70\145" => MoIDPConstants::AUTHN_REQUEST, "\x61\143\x73\x5f\x75\x72\x6c" => $_COOKIE["\141\x63\163\x5f\165\162\x6c"], "\x69\163\163\165\145\x72" => $_COOKIE["\141\x75\144\151\x65\x6e\x63\x65"], "\162\145\154\x61\x79\123\x74\x61\164\x65" => $_COOKIE["\162\x65\x6c\x61\171\123\164\x61\164\145"], "\x72\x65\x71\x75\x65\163\164\111\104" => array_key_exists("\x72\x65\161\165\145\163\x74\x49\x44", $_COOKIE) ? $_COOKIE["\162\x65\x71\165\x65\163\164\x49\x44"] : null], $ur);
            kT:
            if (!(isset($_COOKIE["\155\157\x49\144\160\x73\145\156\144\x57\x73\106\x65\144\122\x65\163\160\157\x6e\x73\x65"]) && strcmp($_COOKIE["\x6d\157\111\x64\x70\163\x65\x6e\x64\x57\163\106\x65\x64\122\145\163\160\x6f\x6e\x73\x65"], "\164\x72\165\x65") == 0)) {
                goto X7;
            }
            $this->sendResponseHandler->mo_idp_send_response(["\162\145\x71\x75\145\163\164\124\x79\160\145" => MoIDPConstants::WS_FED, "\143\154\x69\x65\x6e\x74\x52\145\161\x75\145\x73\164\x49\144" => $_COOKIE["\143\x6c\151\x65\x6e\164\x52\145\161\165\145\x73\164\111\144"], "\167\164\162\145\x61\x6c\x6d" => $_COOKIE["\x77\x74\x72\x65\x61\154\155"], "\x77\141" => $_COOKIE["\x77\141"], "\x72\x65\x6c\141\171\123\x74\x61\164\145" => $_COOKIE["\x72\x65\154\x61\x79\x53\164\x61\x74\145"], "\167\x63\x74\x78" => $_COOKIE["\167\x63\x74\x78"]], $ur);
            X7:
            if (!(isset($_COOKIE["\155\157\111\x64\x70\x53\x65\156\x64\x4a\x57\124\122\145\163\160\x6f\x6e\163\x65"]) && strcmp($_COOKIE["\155\x6f\x49\144\160\123\x65\x6e\144\x4a\127\x54\x52\145\x73\160\157\x6e\163\x65"], "\164\162\165\x65") == 0)) {
                goto kr;
            }
            $this->sendResponseHandler->mo_idp_send_response(["\162\145\161\165\145\163\164\x54\171\x70\x65" => MoIDPConstants::JWT, "\152\x77\x74\137\145\x6e\144\160\x6f\x69\156\x74" => $_COOKIE["\152\x77\x74\x5f\145\156\144\x70\157\x69\x6e\x74"], "\x73\x68\141\x72\x65\144\x53\x65\143\x72\x65\x74" => $_COOKIE["\163\x68\x61\162\x65\144\x53\x65\143\x72\x65\164"], "\x72\x65\x74\165\x72\156\x5f\x74\x6f\x5f\x75\x72\154" => $_COOKIE["\162\145\164\165\x72\x6e\137\x74\157\137\165\162\x6c"]], $ur);
            kr:
        } catch (NotRegisteredException $S5) {
            if (!MSI_DEBUG) {
                goto qL;
            }
            MoIDPUtility::mo_debug("\105\170\143\x65\160\x74\x69\x6f\x6e\x20\x4f\x63\143\x75\162\x72\x65\144\x20\x64\x75\162\x69\x6e\x67\40\x53\x53\117\40" . $S5);
            qL:
            wp_die(MoIDPMessages::SAML_INVALID_OPERATION);
        } catch (InvalidSSOUserException $S5) {
            if (!MSI_DEBUG) {
                goto CH;
            }
            MoIDPUtility::mo_debug("\x45\x78\143\145\160\x74\x69\157\156\40\117\x63\x63\165\x72\162\145\144\40\144\165\x72\151\x6e\x67\x20\x53\123\117\40" . $S5);
            CH:
            wp_die($S5->getMessage());
        } catch (LicenseExpiredException $S5) {
            if (!MSI_DEBUG) {
                goto on;
            }
            MoIDPUtility::mo_debug("\x45\x78\143\x65\160\164\151\157\156\40\x4f\x63\143\165\x72\x72\145\144\x20\x64\165\162\x69\x6e\147\x20\123\x53\x4f\40" . $S5);
            on:
            wp_die($S5->getMessage());
        }
        Fo:
    }
    private function _initiate_saml_response($oF)
    {
        if ($_REQUEST["\x6f\x70\164\x69\157\x6e"] == "\164\x65\163\x74\103\x6f\x6e\x66\151\147") {
            goto wy;
        }
        if ($_REQUEST["\x6f\160\164\x69\x6f\x6e"] === "\163\x61\155\154\x5f\x75\163\145\162\137\x6c\x6f\x67\x69\156") {
            goto nQ;
        }
        if ($_REQUEST["\157\160\x74\x69\157\x6e"] === "\x74\145\x73\164\x5f\152\167\164") {
            goto pY;
        }
        if ($_REQUEST["\x6f\x70\x74\151\x6f\156"] === "\152\x77\x74\137\154\x6f\147\x69\x6e") {
            goto OQ;
        }
        if ($_REQUEST["\157\160\x74\151\x6f\156"] === "\x6d\x6f\x5f\x69\x64\160\137\x6d\x65\164\x61\144\141\x74\141") {
            goto Jh;
        }
        goto dN;
        wy:
        $this->sendSAMLResponseBasedOnRequestData($oF);
        goto dN;
        nQ:
        $this->sendSAMLResponseBasedOnSPName($_REQUEST["\x73\160"], $_REQUEST["\162\145\154\x61\x79\123\164\x61\x74\x65"]);
        goto dN;
        pY:
        $this->sendJWTTestToken($_REQUEST);
        goto dN;
        OQ:
        $this->sendJwtToken($_REQUEST["\x73\x70"], $_REQUEST["\x72\x65\x6c\141\x79\123\164\x61\164\x65"]);
        goto dN;
        Jh:
        MoIDPUtility::showMetadata();
        dN:
    }
    private function sendSAMLResponseBasedOnRequestData($oF)
    {
        $mP = !array_key_exists("\x64\x65\146\x61\165\154\164\122\x65\x6c\x61\x79\x53\164\141\164\x65", $oF) || MoIDPUtility::isBlank($_REQUEST["\144\145\146\141\x75\x6c\164\122\145\154\141\x79\123\164\141\164\x65"]) ? "\57" : $_REQUEST["\144\145\x66\141\x75\154\x74\122\x65\x6c\141\171\123\x74\x61\164\145"];
        $this->sendResponseHandler->mo_idp_send_response(["\x72\x65\161\x75\145\x73\x74\124\171\x70\145" => MoIDPConstants::AUTHN_REQUEST, "\141\143\x73\x5f\165\x72\x6c" => $_REQUEST["\141\143\x73"], "\x69\x73\163\165\145\x72" => $_REQUEST["\x69\163\163\x75\145\162"], "\x72\x65\154\x61\171\123\x74\x61\164\x65" => $mP]);
    }
    private function sendSAMLResponseBasedOnSPName($DL, $Hi)
    {
        global $dbIDPQueries;
        $di = $dbIDPQueries->get_sp_from_name($DL);
        if (MoIDPUtility::isBlank($di)) {
            goto zx;
        }
        $mP = !MoIDPUtility::isBlank($Hi) ? $Hi : (MoIDPUtility::isBlank($di->mo_idp_default_relayState) ? "\57" : $di->mo_idp_default_relayState);
        if (is_user_logged_in()) {
            goto Z1;
        }
        $PE = new AuthnRequest();
        $PE = $PE->setAssertionConsumerServiceURL($di->mo_idp_acs_url)->setIssuer($di->mo_idp_sp_issuer)->setRequestID(null);
        $this->requestProcessHandler->setSAMLSessionCookies($PE, $mP);
        Z1:
        $this->sendResponseHandler->mo_idp_send_response(["\162\145\161\165\x65\163\x74\124\x79\160\x65" => MoIDPConstants::AUTHN_REQUEST, "\141\143\163\x5f\x75\162\x6c" => $di->mo_idp_acs_url, "\151\x73\163\x75\x65\162" => $di->mo_idp_sp_issuer, "\x72\145\x6c\141\171\x53\x74\141\x74\145" => $mP]);
        zx:
    }
    public function mo_idp_logout($Iu)
    {
        if (!($Iu != 0)) {
            goto Vz;
        }
        if (!ob_get_contents()) {
            goto rq;
        }
        ob_clean();
        rq:
        setcookie("\x6d\157\x5f\151\x64\x70\x5f\154\x61\x73\x74\x5f\154\x6f\x67\147\x65\144\x5f\151\x6e\137\x75\163\x65\x72", $Iu, time() + 600, "\57");
        Vz:
        MoIDPUtility::startSession();
        if ($Iu != 0) {
            goto jp;
        }
        if (!MSI_DEBUG) {
            goto cN;
        }
        MoIDPUtility::mo_debug("\125\163\x65\x72\x20\x61\154\162\145\141\144\171\x20\x6c\157\147\147\145\144\40\157\165\x74\x2e\x20\123\x65\156\144\151\x6e\x67\40\x6c\157\147\157\165\x74\x20\162\x65\x73\x70\x6f\156\x73\x65\56");
        cN:
        $I_ = array_key_exists("\155\x6f\137\151\x64\x70\137\154\x6f\147\x6f\165\x74\137\x72\145\161\x75\x65\x73\x74\x5f\x69\163\163\x75\145\x72", $_SESSION) ? $_SESSION["\x6d\x6f\137\x69\x64\x70\137\x6c\157\x67\157\165\x74\137\162\x65\161\165\145\x73\164\x5f\151\x73\163\165\145\x72"] : NULL;
        $vN = array_key_exists("\x6d\157\137\x69\144\x70\x5f\x6c\x6f\147\x6f\x75\x74\137\162\x65\161\x75\145\x73\x74\x5f\x69\x64", $_SESSION) ? $_SESSION["\x6d\157\137\151\x64\160\137\154\x6f\147\157\165\x74\x5f\x72\145\161\165\x65\163\x74\x5f\151\144"] : NULL;
        $Li = array_key_exists("\x6d\157\137\x69\x64\160\x5f\x6c\157\147\x6f\x75\164\x5f\162\145\x6c\x61\171\x5f\163\164\x61\x74\x65", $_SESSION) ? $_SESSION["\x6d\157\137\151\x64\160\137\x6c\157\147\157\x75\x74\x5f\162\145\154\141\x79\137\x73\x74\141\x74\x65"] : NULL;
        if (MoIDPUtility::isBlank($I_)) {
            goto fd;
        }
        $this->sendResponseHandler->mo_idp_send_logout_response($I_, $vN, $Li);
        fd:
        goto Jw;
        jp:
        $this->requestProcessHandler->checkAndLogoutUserFromLoggedInSPs($Iu);
        Jw:
    }
    private function sendJWTTestToken($oF)
    {
        $this->sendResponseHandler->mo_idp_send_response(["\162\145\161\x75\x65\x73\164\124\171\160\x65" => MoIDPConstants::JWT, "\152\167\164\x5f\145\156\x64\160\157\x69\x6e\164" => $oF["\x61\x63\163"], "\163\150\x61\162\x65\144\x53\145\x63\x72\x65\164" => $oF["\x69\163\163\x75\145\x72"], "\162\x65\x74\165\162\156\x5f\x74\x6f\137\x75\x72\x6c" => $oF["\x64\x65\146\x61\165\154\164\x52\145\154\141\x79\x53\164\x61\x74\145"]]);
    }
    private function sendJWTToken($DL, $Hi)
    {
        global $dbIDPQueries;
        $di = $dbIDPQueries->get_sp_from_name($DL);
        $mP = !MoIDPUtility::isBlank($Hi) ? $Hi : (MoIDPUtility::isBlank($di->mo_idp_default_relayState) ? "\x2f" : $di->mo_idp_default_relayState);
        if (is_user_logged_in()) {
            goto KE;
        }
        $PE = ["\x6a\x77\164\x5f\145\156\x64\160\157\x69\x6e\x74" => $di->mo_idp_acs_url, "\163\150\x61\x72\x65\144\x53\145\143\x72\145\x74" => $di->mo_idp_sp_issuer, "\162\x65\x74\x75\x72\156\137\164\x6f\x5f\x75\x72\x6c" => $mP];
        $this->requestProcessHandler->setJWTSessionCookies($PE, $mP);
        KE:
        if (MoIDPUtility::isBlank($di)) {
            goto fr;
        }
        $this->sendResponseHandler->mo_idp_send_response(["\x72\145\161\165\x65\163\164\x54\171\x70\145" => MoIDPConstants::JWT, "\152\167\164\137\x65\156\144\x70\x6f\x69\x6e\x74" => $di->mo_idp_acs_url, "\163\150\141\162\x65\144\x53\145\143\162\145\164" => $di->mo_idp_sp_issuer, "\x72\x65\x74\165\x72\x6e\137\164\157\137\165\x72\x6c" => $mP]);
        fr:
    }
    public function modifyUserAttributeDateValue($l7)
    {
        $kg = array("\144\x2e\x6d\56\x59", "\x64\x2f\155\57\131", "\x64\x2d\x6d\x2d\131", "\131\57\x6d\x2f\x64", "\131\55\x6d\55\144", "\131\56\x6d\x2e\x64", "\x6d\x2e\x64\x2e\x59", "\x6d\x2d\144\55\x59", "\155\57\x64\57\131");
        $wy = false;
        foreach ($kg as $K6) {
            $BQ = \DateTime::createFromFormat($K6, $l7);
            $wy = $BQ && $BQ->format($K6) == $l7 ? true : false;
            if (!$wy) {
                goto PC;
            }
            $l7 = str_replace("\x2f", "\55", $l7);
            $l7 = date("\155\55\x64\55\131", strtotime($l7));
            $l7 = str_replace("\x2d", "\x2f", $l7);
            PC:
            dz:
        }
        h6:
        return $l7;
    }
    function mo_idp_restrict_users($current_user, $VW, $ur)
    {
        if (!empty(get_site_option("\x6d\157\x5f\151\144\160\x5f\x72\157\x6c\145\x5f\142\141\163\145\144\x5f\x72\145\x73\x74\x72\151\x63\x74\x69\x6f\x6e"))) {
            goto q5;
        }
        return;
        q5:
        $am = !is_array(get_site_option("\155\x6f\x5f\151\x64\x70\x5f\x73\163\157\x5f\141\154\x6c\x6f\167\145\144\137\x72\x6f\154\145\x73")) ? array() : MoIDPUtility::sanitizeAssociativeArray(get_site_option("\155\157\x5f\151\144\x70\x5f\163\163\157\137\x61\x6c\154\x6f\167\145\144\137\x72\157\154\x65\163"));
        $jd = $current_user->roles;
        $xl = end($jd);
        foreach ($jd as $FJ) {
            if (!isset($am[$FJ])) {
                goto c4;
            }
            return;
            goto Pw;
            c4:
            if (!($xl === $FJ)) {
                goto Bh;
            }
            throw new InvalidSSOUserException();
            Bh:
            Pw:
            zh:
        }
        Ax:
    }
    public function mo_idp_return_custom_login($L3)
    {
        $Kl = get_site_option("\x6d\157\137\x69\144\160\137\x63\165\163\164\157\155\x5f\154\157\x67\x69\x6e\137\165\x72\x6c");
        if (!(isset($Kl) && !empty($Kl))) {
            goto oI;
        }
        $L3 = $Kl;
        oI:
        return $L3;
    }
}
