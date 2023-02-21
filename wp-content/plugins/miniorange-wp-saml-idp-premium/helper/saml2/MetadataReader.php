<?php


namespace IDP\Helper\SAML2;

use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
use IDP\Exception\InvalidSPSSODescriptorException;
class MetadataReader
{
    private $identityProviders;
    private $serviceProviders;
    public function __construct(\DOMNode $eL = NULL)
    {
        $this->identityProviders = array();
        $this->serviceProviders = array();
        $Pm = SAMLUtilities::xpQuery($eL, "\56\57\163\x61\155\x6c\137\155\145\164\x61\x64\141\164\x61\x3a\105\156\164\x69\x74\171\104\145\x73\143\162\151\160\x74\157\162");
        foreach ($Pm as $eA) {
            $y7 = SAMLUtilities::xpQuery($eA, "\56\x2f\163\141\x6d\x6c\137\x6d\x65\164\141\144\x61\164\141\72\123\120\x53\x53\x4f\104\145\x73\x63\162\151\x70\164\157\x72");
            if (!(isset($y7) && !empty($y7))) {
                goto Ld;
            }
            array_push($this->serviceProviders, new ServiceProviders($eA));
            Ld:
            kk:
        }
        tt:
    }
    public function getIdentityProviders()
    {
        return $this->identityProviders;
    }
    public function getServiceProviders()
    {
        return $this->serviceProviders;
    }
}
class ServiceProviders
{
    public $spName;
    public $nameID;
    public $entityID;
    public $acsUrl;
    public $signedRequest;
    public $assertionSigned;
    public $logoutDetails;
    public $sloBindingType;
    public $signingCertificate;
    public $encryptionCertificate;
    public function __construct(\DOMElement $eL = NULL)
    {
        $this->spName = '';
        $this->sloBindingType = '';
        $this->loginDetails = array();
        $this->logoutDetails = array();
        $this->signingCertificate = array();
        $this->encryptionCertificate = array();
        $this->nameID = "\x75\162\x6e\x3a\157\141\x73\x69\x73\72\156\x61\155\x65\x73\x3a\x74\143\x3a\123\101\115\x4c\72\61\x2e\x31\x3a\x6e\x61\x6d\145\151\144\x2d\146\157\x72\x6d\x61\x74\x3a\x65\x6d\x61\151\154\x41\144\x64\x72\145\163\163";
        if (!$eL->hasAttribute("\145\156\x74\x69\164\x79\111\x44")) {
            goto fP;
        }
        $this->entityID = $eL->getAttribute("\145\x6e\x74\x69\164\x79\111\104");
        fP:
        $y7 = SAMLUtilities::xpQuery($eL, "\x2e\x2f\163\x61\155\x6c\x5f\x6d\x65\164\x61\x64\x61\164\141\x3a\123\120\123\x53\x4f\x44\145\163\x63\162\151\x70\x74\157\x72");
        if (count($y7) > 1) {
            goto n8;
        }
        if (empty($y7)) {
            goto eX;
        }
        goto BP;
        n8:
        throw new InvalidSPSSODescriptorException("\115\x4f\122\105\x5f\x53\120");
        goto BP;
        eX:
        throw new InvalidSPSSODescriptorException("\115\111\123\123\x49\116\x47\137\x53\x50");
        BP:
        if (!$y7[0]->hasAttribute("\x41\x75\164\x68\x6e\122\x65\x71\165\x65\x73\164\163\123\x69\x67\x6e\x65\144")) {
            goto gB;
        }
        $this->signedRequest = $y7[0]->getAttribute("\x41\x75\164\150\x6e\x52\145\x71\165\x65\x73\164\163\x53\x69\x67\x6e\x65\144");
        gB:
        if (!$y7[0]->hasAttribute("\127\x61\156\x74\x41\x73\163\145\162\164\x69\x6f\156\x73\x53\x69\x67\156\x65\x64")) {
            goto z3;
        }
        $this->assertionSigned = $y7[0]->getAttribute("\127\x61\156\x74\x41\x73\163\x65\x72\164\x69\x6f\156\163\x53\151\147\156\x65\144");
        z3:
        $Ao = $y7[0];
        $VM = SAMLUtilities::xpQuery($eL, "\56\57\x73\x61\x6d\x6c\x5f\155\145\164\x61\x64\x61\x74\x61\x3a\x45\x78\164\x65\156\163\151\157\156\x73");
        if (!$VM) {
            goto yn;
        }
        $this->parseInfo($Ao);
        yn:
        $this->parseSSOService($Ao);
        $this->parseSLOService($Ao);
        $this->parsex509Certificate($Ao);
        $this->parseAcsURL($Ao);
    }
    private function parseInfo($eL)
    {
        $FK = SAMLUtilities::xpQuery($eL, "\56\57\155\144\165\151\x3a\125\111\111\156\x66\157\x2f\x6d\144\165\151\x3a\x44\151\x73\160\x6c\141\171\x4e\141\x6d\x65");
        foreach ($FK as $Pk) {
            if (!($Pk->hasAttribute("\170\x6d\154\72\154\x61\x6e\147") && $Pk->getAttribute("\170\155\154\72\154\x61\156\x67") == "\x65\156")) {
                goto gx;
            }
            $this->spName = $Pk->textContent;
            gx:
            F6:
        }
        X_:
    }
    private function parseSSOService($eL)
    {
        $oM = SAMLUtilities::xpQuery($eL, "\x2e\57\163\x61\x6d\154\137\155\145\x74\141\144\x61\x74\x61\72\x53\x69\x6e\x67\x6c\x65\123\151\147\x6e\117\156\x53\145\x72\x76\151\143\145");
        foreach ($oM as $I5) {
            $YR = str_replace("\165\162\x6e\x3a\157\x61\x73\x69\163\72\156\141\x6d\x65\x73\72\x74\143\72\123\101\x4d\114\72\x32\x2e\x30\x3a\x62\x69\x6e\x64\151\156\x67\163\72", '', $I5->getAttribute("\102\x69\x6e\x64\x69\156\147"));
            $this->loginDetails = array_merge($this->loginDetails, array($YR => $I5->getAttribute("\114\157\143\141\x74\151\157\156")));
            Qg:
        }
        sQ:
    }
    private function parseSLOService($eL)
    {
        $kw = SAMLUtilities::xpQuery($eL, "\x2e\57\163\141\x6d\154\137\x6d\145\x74\141\x64\141\x74\141\72\x53\151\x6e\147\x6c\145\114\157\x67\157\x75\x74\123\x65\x72\x76\x69\x63\145");
        if (!$kw) {
            goto Z5;
        }
        $this->sloBindingType = str_replace("\x75\162\x6e\x3a\x6f\x61\163\x69\163\72\x6e\x61\155\145\x73\72\164\143\x3a\x53\101\115\114\72\62\x2e\60\x3a\x62\x69\156\144\151\156\147\163\x3a", '', $kw[0]->getAttribute("\x42\x69\x6e\144\x69\156\147"));
        Z5:
        foreach ($kw as $wu) {
            $YR = str_replace("\165\x72\156\x3a\x6f\x61\163\x69\x73\x3a\156\141\155\145\x73\x3a\164\x63\72\x53\101\x4d\114\x3a\62\x2e\60\x3a\142\151\x6e\x64\151\156\147\163\x3a", '', $wu->getAttribute("\102\x69\156\144\151\x6e\147"));
            $this->logoutDetails = array_merge($this->logoutDetails, array($YR => $wu->getAttribute("\x4c\x6f\143\x61\x74\x69\x6f\156")));
            Jy:
        }
        ZR:
    }
    private function parsex509Certificate($eL)
    {
        foreach (SAMLUtilities::xpQuery($eL, "\56\x2f\x73\141\x6d\154\x5f\155\145\x74\x61\144\x61\164\141\x3a\x4b\145\x79\x44\145\163\143\162\x69\x70\164\x6f\162") as $b2) {
            if ($b2->hasAttribute("\165\x73\x65")) {
                goto E7;
            }
            $this->parseSigningCertificate($b2);
            goto Z_;
            E7:
            if ($b2->getAttribute("\165\163\x65") == "\145\156\x63\162\x79\x70\x74\x69\157\x6e") {
                goto Zd;
            }
            $this->parseSigningCertificate($b2);
            goto nD;
            Zd:
            $this->parseEncryptionCertificate($b2);
            nD:
            Z_:
            ZJ:
        }
        IP:
    }
    private function parseSigningCertificate($eL)
    {
        $J6 = SAMLUtilities::xpQuery($eL, "\x2e\x2f\144\163\x3a\x4b\x65\171\111\x6e\146\157\57\144\x73\x3a\130\x35\60\x39\x44\141\164\141\57\x64\163\x3a\130\65\60\x39\x43\x65\162\164\x69\146\x69\143\141\164\145");
        $F7 = trim($J6[0]->textContent);
        $F7 = str_replace(array("\15", "\xa", "\x9", "\40"), '', $F7);
        if (empty($J6)) {
            goto m6;
        }
        array_push($this->signingCertificate, SAMLUtilities::sanitize_certificate($F7));
        m6:
    }
    private function parseEncryptionCertificate($eL)
    {
        $J6 = SAMLUtilities::xpQuery($eL, "\x2e\57\144\x73\72\x4b\x65\x79\x49\x6e\x66\x6f\57\x64\163\72\x58\x35\60\71\104\x61\x74\x61\57\x64\x73\72\130\65\60\71\103\145\162\x74\x69\x66\x69\x63\x61\164\x65");
        $F7 = trim($J6[0]->textContent);
        $F7 = str_replace(array("\15", "\12", "\x9", "\40"), '', $F7);
        if (empty($J6)) {
            goto Mx;
        }
        array_push($this->encryptionCertificate, $F7);
        Mx:
    }
    private function parseAcsURL($eL)
    {
        $GQ = SAMLUtilities::xpQuery($eL, "\x2e\57\163\141\x6d\x6c\x5f\x6d\x65\164\141\x64\x61\164\x61\x3a\x41\163\163\x65\162\x74\151\x6f\156\x43\x6f\x6e\x73\165\x6d\x65\162\123\x65\x72\166\x69\143\145");
        if (!$GQ[0]->hasAttribute("\114\x6f\143\x61\164\151\x6f\156")) {
            goto WO;
        }
        $this->acsUrl = $GQ[0]->getAttribute("\x4c\157\143\x61\164\151\157\156");
        WO:
    }
}
