<?php


namespace IDP\Handler;

use IDP\Exception\InvalidServiceProviderException;
use IDP\Exception\InvalidSignatureInRequestException;
use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Factory\RequestDecisionHandler;
use IDP\Helper\SAML2\AuthnRequest;
use IDP\Helper\SAML2\LogoutRequest;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
use IDP\Helper\WSFED\WsFedRequest;
use RobRichards\XMLSecLibs\XMLSecurityKey;
final class ReadRequestHandler extends BaseHandler
{
    use Instance;
    private $requestProcessHandler;
    private function __construct()
    {
        $this->requestProcessHandler = ProcessRequestHandler::instance();
    }
    public function _read_request(array $oF, array $z7, $ZV)
    {
        if (!MSI_DEBUG) {
            goto jW;
        }
        MoIDPUtility::mo_debug("\x52\145\141\x64\151\156\x67\x20\x53\101\115\x4c\40\122\x65\161\x75\x65\x73\164");
        jW:
        $this->checkIfValidPlugin();
        $aD = RequestDecisionHandler::getRequestHandler($ZV, $oF, $z7);
        $Hi = array_key_exists("\x52\x65\x6c\x61\171\123\164\141\x74\x65", $oF) ? $oF["\122\145\154\141\171\x53\x74\141\164\x65"] : "\x2f";
        switch ($aD->getRequestType()) {
            case MoIDPConstants::LOGOUT_REQUEST:
                $this->mo_idp_process_logout_request($aD, $Hi);
                goto Ix;
            case MoIDPConstants::AUTHN_REQUEST:
                $this->mo_idp_process_assertion_request($aD, $Hi, $z7);
                goto Ix;
            case MoIDPConstants::WS_FED:
                $this->mo_idp_process_ws_fed_request($aD, $Hi);
                goto Ix;
        }
        I9:
        Ix:
    }
    public function mo_idp_process_ws_fed_request(WsFedRequest $mq, $Hi)
    {
        global $dbIDPQueries;
        if (!MSI_DEBUG) {
            goto KZ;
        }
        MoIDPUtility::mo_debug($mq);
        KZ:
        $this->checkIfValidPlugin();
        $di = $dbIDPQueries->get_sp_from_issuer($mq->getWtRealm());
        $this->checkIfValidSP($di);
        $Z8 = $di->mo_idp_acs_url;
        $this->requestProcessHandler->mo_idp_authorize_user($Hi, $mq);
    }
    private function mo_idp_process_assertion_request(AuthnRequest $LX, $Hi, $z7)
    {
        global $dbIDPQueries;
        if (!MSI_DEBUG) {
            goto Q1;
        }
        MoIDPUtility::mo_debug($LX);
        Q1:
        $QB = $LX->getIssuer();
        $Z8 = $LX->getAssertionConsumerServiceURL();
        $di = $dbIDPQueries->get_sp_from_issuer($QB);
        $di = !isset($di) ? $dbIDPQueries->get_sp_from_acs($Z8) : $di;
        $this->checkIfValidSP($di);
        $QB = $di->mo_idp_sp_issuer;
        $Z8 = $di->mo_idp_acs_url;
        $LX->setIssuer($QB);
        $LX->setAssertionConsumerServiceURL($Z8);
        $eh = SAMLUtilities::validateElement($LX->getXml());
        $Dq = $di->mo_idp_cert;
        $Dq = XMLSecurityKey::getRawThumbprint($Dq);
        $Dq = iconv("\125\x54\x46\x2d\x38", "\103\120\61\x32\65\62\x2f\57\111\107\116\117\x52\x45", $Dq);
        $Dq = preg_replace("\x2f\x5c\x73\x2b\57", '', $Dq);
        if (empty($Dq)) {
            goto OD;
        }
        if ($eh !== FALSE) {
            goto YM;
        }
        if (array_key_exists("\123\151\x67\x6e\x61\164\165\x72\145", $z7)) {
            goto Mf;
        }
        throw new InvalidSignatureInRequestException();
        goto J9;
        YM:
        $this->validateSignatureInRequest($Dq, $eh);
        goto J9;
        Mf:
        if (array_key_exists("\x52\145\154\141\171\x53\164\x61\164\145", $z7)) {
            goto zf;
        }
        $eh = "\123\x41\115\114\x52\145\161\x75\145\x73\164\x3d" . urlencode($z7["\123\101\x4d\114\122\145\x71\x75\145\x73\164"]) . "\46\123\x69\147\x41\x6c\x67\x3d" . urlencode($z7["\x53\x69\147\101\x6c\x67"]);
        goto Ia;
        zf:
        $eh = "\123\x41\x4d\114\x52\145\161\x75\145\163\164\x3d" . urlencode($z7["\123\101\x4d\x4c\122\145\x71\165\145\163\164"]) . "\46\x52\145\x6c\x61\171\x53\164\x61\x74\145\75" . urlencode($z7["\122\x65\x6c\x61\171\x53\164\x61\x74\x65"]) . "\x26\123\151\147\x41\x6c\x67\75" . urlencode($z7["\x53\151\147\101\x6c\147"]);
        Ia:
        $B5 = $z7["\123\151\147\x41\x6c\x67"];
        $xO = new XMLSecurityKey($B5, array("\164\171\160\145" => "\x70\x75\x62\154\x69\x63"));
        $xO->loadKey($di->mo_idp_cert);
        $Kh = $xO->verifySignature($eh, base64_decode($z7["\123\x69\x67\156\x61\164\165\x72\145"]));
        if (!($Kh !== 1)) {
            goto ju;
        }
        throw new InvalidSignatureInRequestException();
        ju:
        J9:
        OD:
        $Hi = MoIDPUtility::isBlank($di->mo_idp_default_relayState) ? $Hi : $di->mo_idp_default_relayState;
        $this->requestProcessHandler->mo_idp_authorize_user($Hi, $LX);
    }
    public function checkIfValidSP($di)
    {
        if (!MoIDPUtility::isBlank($di)) {
            goto xq;
        }
        throw new InvalidServiceProviderException();
        xq:
    }
    public function validateSignatureInRequest($Dq, $eh)
    {
        if (SAMLUtilities::processRequest($Dq, $eh)) {
            goto qA;
        }
        throw new InvalidSignatureInRequestException();
        qA:
    }
    public function _read_saml_response(array $oF, array $z7)
    {
        if (!MSI_DEBUG) {
            goto P5;
        }
        MoIDPUtility::mo_debug("\x52\x65\x61\144\151\156\x67\40\123\x41\115\x4c\40\122\x65\163\160\x6f\156\x73\145");
        P5:
        $this->checkIfValidPlugin();
        $W5 = $oF["\x53\101\115\x4c\122\145\163\160\157\156\x73\145"];
        $Hi = array_key_exists("\122\x65\154\x61\171\123\x74\x61\x74\x65", $oF) ? $oF["\x52\x65\154\141\171\123\164\141\164\x65"] : "\57";
        $W5 = base64_decode($W5);
        if (!(array_key_exists("\x53\101\x4d\x4c\122\x65\163\160\x6f\x6e\x73\145", $z7) && !empty($z7["\123\x41\x4d\x4c\x52\x65\x73\160\157\156\x73\145"]))) {
            goto Fe;
        }
        $W5 = gzinflate($W5);
        Fe:
        $i2 = new \DOMDocument();
        $i2->loadXML($W5);
        $KQ = $i2->firstChild;
        if (!($KQ->localName != "\114\x6f\147\157\165\x74\122\145\x73\x70\x6f\x6e\x73\x65")) {
            goto N6;
        }
        return;
        N6:
        $this->requestProcessHandler->processLogoutResponseFromSP();
    }
    private function mo_idp_process_logout_request($vq, $Hi)
    {
        if (!MSI_DEBUG) {
            goto uX;
        }
        MoIDPUtility::mo_debug($vq);
        uX:
        MoIDPUtility::startSession();
        $_SESSION["\x6d\x6f\137\x69\x64\160\137\154\157\147\157\165\164\x5f\162\145\x71\x75\x65\163\x74\x5f\x69\x73\x73\x75\145\162"] = $vq->getIssuer();
        $_SESSION["\x6d\x6f\x5f\151\x64\160\x5f\154\x6f\x67\157\x75\164\137\x72\145\x71\165\145\163\x74\137\151\x64"] = $vq->getId();
        $_SESSION["\155\157\x5f\151\x64\160\x5f\x6c\x6f\x67\x6f\165\x74\137\x72\145\154\141\x79\137\x73\164\x61\x74\x65"] = $Hi;
        wp_logout();
    }
}
