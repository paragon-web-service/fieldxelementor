<?php


namespace IDP\Helper\SAML2;

use IDP\Helper\Factory\ResponseHandlerFactory;
use IDP\Helper\Utilities\MoIDPUtility;
class GenerateLogoutResponse implements ResponseHandlerFactory
{
    private $xml;
    private $id;
    private $version;
    private $destination;
    private $inResponseTo;
    private $issuer;
    private $status;
    public function __construct($HB, $QB, $Ru)
    {
        $this->xml = new \DOMDocument("\x31\x2e\60", "\165\x74\x66\x2d\70");
        $this->issuer = $QB;
        $this->destination = $Ru;
        $this->inResponseTo = $HB;
    }
    public function generateResponse()
    {
        $Fg = $this->createLogoutResponseElement();
        $this->xml->appendChild($Fg);
        $QB = $this->buildIssuer();
        $Fg->appendChild($QB);
        $wv = $this->buildStatus();
        $Fg->appendChild($wv);
        $LW = $this->xml->saveXML();
        return $LW;
    }
    protected function createLogoutResponseElement()
    {
        $Fg = $this->xml->createElementNS("\165\162\x6e\x3a\x6f\x61\163\x69\163\x3a\156\x61\155\145\163\72\164\143\x3a\123\x41\115\x4c\x3a\x32\56\x30\72\160\x72\157\164\x6f\x63\x6f\x6c", "\163\141\x6d\x6c\x70\72\x4c\x6f\x67\157\x75\164\122\x65\163\160\x6f\x6e\x73\145");
        $Fg->setAttribute("\x49\104", $this->generateUniqueID(40));
        $Fg->setAttribute("\x56\x65\162\x73\151\x6f\156", "\x32\x2e\x30");
        $Fg->setAttribute("\x49\163\x73\x75\145\x49\156\163\x74\x61\156\x74", str_replace("\53\x30\60\x3a\x30\60", "\132", gmdate("\x63", time())));
        $Fg->setAttribute("\x44\145\163\164\x69\156\141\x74\x69\157\156", $this->destination);
        $Fg->setAttribute("\x49\x6e\122\x65\x73\x70\157\x6e\x73\145\124\x6f", $this->inResponseTo);
        return $Fg;
    }
    protected function buildIssuer()
    {
        return $this->xml->createElementNS("\165\162\156\x3a\x6f\141\163\x69\163\72\156\141\x6d\145\163\72\x74\143\72\123\101\115\x4c\72\62\x2e\60\72\x61\x73\163\145\x72\x74\x69\x6f\x6e", "\163\141\155\x6c\x3a\111\x73\x73\x75\x65\x72", $this->issuer);
    }
    protected function buildStatus()
    {
        $ye = $this->xml->createElementNS("\x75\162\x6e\72\x6f\141\163\x69\163\x3a\x6e\x61\x6d\145\x73\72\x74\x63\x3a\123\x41\115\114\72\62\x2e\60\72\160\x72\x6f\x74\x6f\x63\157\x6c", "\x73\141\155\154\x70\72\123\x74\x61\164\x75\x73");
        $ye->appendChild($this->createStatusCode());
        return $ye;
    }
    protected function createStatusCode()
    {
        $mb = $this->xml->createElementNS("\165\162\156\x3a\x6f\x61\x73\151\x73\x3a\156\x61\155\x65\x73\x3a\164\143\72\123\101\115\114\x3a\62\56\60\x3a\160\162\x6f\164\157\143\157\x6c", "\163\x61\x6d\x6c\x70\x3a\x53\164\141\164\165\163\x43\157\144\x65");
        $mb->setAttribute("\126\141\x6c\x75\145", "\x75\x72\x6e\72\157\x61\x73\151\x73\x3a\156\x61\x6d\145\x73\72\x74\x63\72\123\x41\115\x4c\72\62\56\60\x3a\163\164\x61\x74\x75\x73\72\x53\165\143\x63\x65\163\163");
        return $mb;
    }
    function generateUniqueID($kd)
    {
        return MoIDPUtility::generateRandomAlphanumericValue($kd);
    }
}
