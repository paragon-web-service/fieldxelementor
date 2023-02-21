<?php


namespace IDP\Helper\Factory;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\SAML2\AuthnRequest;
use IDP\Helper\SAML2\LogoutRequest;
use IDP\Helper\WSFED\WsFedRequest;
class RequestDecisionHandler
{
    public static function getRequestHandler($ZV, $oF, $z7, $VW = array())
    {
        switch ($ZV) {
            case MoIDPConstants::SAML:
                return self::getSAMLRequestHandler($oF, $z7);
                goto wR;
            case MoIDPConstants::WS_FED:
                return self::getWSFedRequestHandler($oF, $z7);
                goto wR;
            case MoIDPConstants::LOGOUT_REQUEST:
                return self::getLogoutRequestHandler($VW[0], $VW[1], $VW[2], $VW[3]);
                goto wR;
            case MoIDPConstants::AUTHN_REQUEST:
                return new AuthnRequest($VW[0]);
                goto wR;
        }
        M0:
        wR:
    }
    public static function getSAMLRequestHandler($oF, $z7)
    {
        $fr = $oF["\123\x41\x4d\x4c\122\x65\x71\x75\x65\163\x74"];
        $fr = base64_decode($fr);
        if (!array_key_exists("\x53\101\115\x4c\122\145\x71\x75\145\x73\x74", $z7)) {
            goto vo;
        }
        $fr = gzinflate($fr);
        vo:
        $i2 = new \DOMDocument();
        $i2->loadXML($fr);
        $pn = $i2->firstChild;
        if ($pn->localName == "\x4c\157\147\x6f\x75\164\x52\145\x71\165\x65\163\x74") {
            goto JQ;
        }
        return new AuthnRequest($pn);
        goto Ci;
        JQ:
        return new LogoutRequest($pn);
        Ci:
    }
    public static function getWSFedRequestHandler($oF, $z7)
    {
        return new WsFedRequest($oF);
    }
    public static function getAuthnRequestHandler($eL)
    {
        return;
    }
    public static function getLogoutRequestHandler($Pb, $FW, $QB, $Ru)
    {
        $vq = new LogoutRequest();
        $vq->setIssuer($QB);
        $vq->setDestination($Ru);
        $vq->setNameId($Pb);
        $vq->setSessionIndexes($FW);
        return $vq;
    }
}
