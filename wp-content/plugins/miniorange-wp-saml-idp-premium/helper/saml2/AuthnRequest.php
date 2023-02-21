<?php


namespace IDP\Helper\SAML2;

use IDP\Helper\Utilities\SAMLUtilities;
use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Factory\RequestHandlerFactory;
use IDP\Exception\InvalidRequestInstantException;
use IDP\Exception\InvalidRequestVersionException;
use IDP\Exception\MissingIssuerValueException;
class AuthnRequest implements RequestHandlerFactory
{
    private $xml;
    private $nameIdPolicy;
    private $forceAuthn;
    private $isPassive;
    private $RequesterID = array();
    private $assertionConsumerServiceURL;
    private $protocolBinding;
    private $requestedAuthnContext;
    private $namespaceURI;
    private $destination;
    private $issuer;
    private $version;
    private $issueInstant;
    private $requestID;
    private $requestType = MoIDPConstants::AUTHN_REQUEST;
    public function __construct(\DOMElement $eL = null)
    {
        $this->nameIdPolicy = array();
        $this->forceAuthn = false;
        $this->isPassive = false;
        if (!($eL === null)) {
            goto CO;
        }
        return;
        CO:
        $this->xml = $eL;
        $this->forceAuthn = SAMLUtilities::parseBoolean($eL, "\106\157\162\x63\x65\x41\x75\164\150\x6e", false);
        $this->isPassive = SAMLUtilities::parseBoolean($eL, "\x49\x73\x50\141\x73\x73\151\x76\145", false);
        if (!$eL->hasAttribute("\x41\163\x73\145\162\164\x69\157\x6e\x43\x6f\156\x73\165\x6d\145\162\x53\145\162\166\151\143\x65\125\x52\x4c")) {
            goto ch;
        }
        $this->assertionConsumerServiceURL = $eL->getAttribute("\101\163\x73\145\x72\164\151\x6f\x6e\x43\x6f\156\x73\x75\x6d\145\x72\123\145\x72\166\x69\x63\x65\125\x52\114");
        ch:
        if (!$eL->hasAttribute("\120\162\157\x74\x6f\x63\x6f\x6c\102\151\x6e\144\151\x6e\147")) {
            goto Ko;
        }
        $this->protocolBinding = $eL->getAttribute("\x50\162\x6f\164\x6f\x63\x6f\x6c\x42\151\156\144\x69\x6e\x67");
        Ko:
        if (!$eL->hasAttribute("\101\164\x74\x72\x69\142\x75\164\x65\x43\157\x6e\163\165\x6d\151\156\x67\123\x65\x72\166\151\143\145\x49\x6e\x64\145\x78")) {
            goto ws;
        }
        $this->attributeConsumingServiceIndex = (int) $eL->getAttribute("\101\164\x74\162\151\x62\x75\x74\145\103\157\x6e\x73\x75\155\151\156\x67\x53\x65\162\x76\x69\x63\x65\x49\x6e\144\145\170");
        ws:
        if (!$eL->hasAttribute("\x41\x73\x73\145\162\x74\151\157\156\103\157\x6e\163\165\155\145\162\123\x65\162\166\151\x63\x65\x49\x6e\144\x65\x78")) {
            goto rc;
        }
        $this->assertionConsumerServiceIndex = (int) $eL->getAttribute("\101\163\163\145\162\x74\x69\157\x6e\x43\157\x6e\x73\x75\155\145\x72\x53\x65\162\x76\151\143\x65\111\x6e\144\x65\170");
        rc:
        if (!$eL->hasAttribute("\x44\x65\x73\x74\151\x6e\x61\164\x69\157\156")) {
            goto Lr;
        }
        $this->destination = $eL->getAttribute("\104\x65\163\x74\x69\156\141\x74\x69\157\x6e");
        Lr:
        if (!isset($eL->namespaceURI)) {
            goto FJ;
        }
        $this->namespaceURI = $eL->namespaceURI;
        FJ:
        if (!$eL->hasAttribute("\x56\145\x72\x73\x69\x6f\x6e")) {
            goto ow;
        }
        $this->version = $eL->getAttribute("\126\145\162\163\151\157\x6e");
        ow:
        if (!$eL->hasAttribute("\111\x73\163\x75\x65\x49\156\x73\164\x61\156\164")) {
            goto BR;
        }
        $this->issueInstant = $eL->getAttribute("\111\x73\163\x75\x65\111\x6e\x73\x74\141\x6e\164");
        BR:
        if (!$eL->hasAttribute("\x49\x44")) {
            goto sT;
        }
        $this->requestID = $eL->getAttribute("\x49\104");
        sT:
        $this->checkAuthnRequestIssueInstant();
        $this->checkSAMLRequestVersion();
        $this->parseNameIdPolicy($eL);
        $this->parseIssuer($eL);
        $this->parseRequestedAuthnContext($eL);
        $this->parseScoping($eL);
    }
    protected function parseIssuer(\DOMElement $eL)
    {
        $QB = SAMLUtilities::xpQuery($eL, "\x2e\57\163\x61\x6d\x6c\137\141\163\163\145\162\164\x69\157\156\72\x49\163\163\165\x65\162");
        if (!empty($QB)) {
            goto f6;
        }
        throw new MissingIssuerValueException();
        f6:
        $this->issuer = trim($QB[0]->textContent);
    }
    protected function parseNameIdPolicy(\DOMElement $eL)
    {
        $iD = SAMLUtilities::xpQuery($eL, "\x2e\x2f\x73\141\x6d\154\137\160\162\157\164\157\143\157\154\72\x4e\141\155\x65\111\x44\120\x6f\x6c\x69\143\171");
        if (!empty($iD)) {
            goto Ug;
        }
        return;
        Ug:
        $iD = $iD[0];
        if (!$iD->hasAttribute("\106\157\162\155\141\x74")) {
            goto Pf;
        }
        $this->nameIdPolicy["\106\x6f\162\155\141\x74"] = $iD->getAttribute("\x46\x6f\x72\155\x61\164");
        Pf:
        if (!$iD->hasAttribute("\123\x50\x4e\x61\x6d\145\121\165\141\154\x69\x66\x69\145\162")) {
            goto LC;
        }
        $this->nameIdPolicy["\x53\120\116\x61\x6d\145\121\165\141\x6c\x69\x66\151\145\x72"] = $iD->getAttribute("\x53\x50\x4e\141\x6d\145\121\165\141\154\x69\x66\x69\x65\162");
        LC:
        if (!$iD->hasAttribute("\x41\x6c\x6c\x6f\167\x43\162\x65\141\164\145")) {
            goto gS;
        }
        $this->nameIdPolicy["\x41\x6c\x6c\157\167\x43\162\145\141\164\145"] = SAMLUtilities::parseBoolean($iD, "\x41\x6c\154\157\x77\103\162\145\x61\x74\x65", false);
        gS:
    }
    protected function parseRequestedAuthnContext(\DOMElement $eL)
    {
        $zC = SAMLUtilities::xpQuery($eL, "\x2e\x2f\x73\141\155\154\x5f\160\162\x6f\x74\157\x63\157\154\x3a\122\145\x71\x75\145\x73\164\145\x64\101\165\164\150\x6e\x43\157\156\164\145\170\x74");
        if (!empty($zC)) {
            goto Qj;
        }
        return;
        Qj:
        $zC = $zC[0];
        $Pl = array("\x41\x75\x74\x68\156\x43\x6f\156\x74\x65\170\x74\103\x6c\x61\163\x73\x52\x65\x66" => array(), "\103\x6f\x6d\160\141\x72\x69\163\x6f\156" => "\x65\170\141\143\x74");
        $nj = SAMLUtilities::xpQuery($zC, "\56\x2f\163\141\x6d\154\137\141\x73\x73\x65\x72\x74\151\x6f\156\x3a\101\x75\164\150\156\x43\157\156\164\x65\170\x74\x43\x6c\141\163\x73\x52\145\x66");
        foreach ($nj as $rM) {
            $Pl["\101\x75\164\150\156\x43\x6f\156\x74\x65\x78\164\103\x6c\141\x73\x73\x52\x65\x66"][] = trim($rM->textContent);
            Kl:
        }
        xn:
        if (!$zC->hasAttribute("\103\x6f\x6d\x70\x61\162\151\x73\x6f\x6e")) {
            goto PQ;
        }
        $Pl["\x43\x6f\x6d\160\141\162\x69\x73\x6f\x6e"] = $zC->getAttribute("\x43\157\155\x70\141\x72\151\163\x6f\156");
        PQ:
        $this->requestedAuthnContext = $Pl;
    }
    protected function parseScoping(\DOMElement $eL)
    {
        $Ak = SAMLUtilities::xpQuery($eL, "\x2e\57\163\141\155\154\x5f\x70\162\157\x74\x6f\143\157\x6c\x3a\x53\x63\x6f\x70\151\156\x67");
        if (!empty($Ak)) {
            goto Uc;
        }
        return;
        Uc:
        $Ak = $Ak[0];
        if (!$Ak->hasAttribute("\120\x72\157\x78\x79\x43\x6f\x75\x6e\164")) {
            goto ew;
        }
        $this->ProxyCount = (int) $Ak->getAttribute("\x50\162\157\170\171\103\157\165\x6e\164");
        ew:
        $zN = SAMLUtilities::xpQuery($Ak, "\56\x2f\x73\x61\x6d\x6c\137\160\x72\157\x74\157\x63\157\154\72\111\x44\x50\114\151\x73\x74\x2f\163\x61\155\x6c\x5f\160\162\157\164\x6f\x63\157\x6c\72\x49\104\x50\x45\x6e\164\x72\171");
        foreach ($zN as $li) {
            if ($li->hasAttribute("\x50\x72\157\x76\151\144\x65\x72\111\x44")) {
                goto cY;
            }
            throw new \Exception("\103\157\165\154\x64\x20\x6e\157\x74\40\x67\x65\x74\x20\x50\162\157\x76\151\144\145\162\111\x44\x20\146\x72\157\x6d\x20\x53\x63\x6f\x70\x69\156\x67\57\111\x44\120\105\x6e\164\x72\x79\40\x65\x6c\x65\155\145\156\x74\x20\x69\156\x20\x41\x75\164\150\156\122\145\161\165\145\163\x74\40\157\x62\x6a\145\x63\x74");
            cY:
            $this->IDPList[] = $li->getAttribute("\120\162\x6f\166\151\x64\145\x72\x49\104");
            zq:
        }
        Ae:
        $Jw = SAMLUtilities::xpQuery($Ak, "\x2e\x2f\x73\x61\155\154\x5f\160\162\157\164\x6f\x63\x6f\x6c\x3a\x52\145\161\165\x65\163\164\145\162\111\104");
        foreach ($Jw as $ES) {
            $this->RequesterID[] = trim($ES->textContent);
            OE:
        }
        GM:
    }
    public function checkAuthnRequestIssueInstant()
    {
        if (!(strtotime($this->issueInstant) >= time() + 60)) {
            goto QE;
        }
        throw new InvalidRequestInstantException();
        QE:
    }
    public function checkSAMLRequestVersion()
    {
        if (!($this->version !== "\62\56\x30")) {
            goto OC;
        }
        throw new InvalidRequestVersionException();
        OC:
    }
    public function generateRequest()
    {
        return;
    }
    public function __toString()
    {
        $Kn = "\133\x20\101\125\124\110\x4e\40\x52\x45\121\125\105\x53\x54\40\120\101\122\x41\x4d\123";
        $Kn .= "\54\x20\116\x61\x6d\x65\163\x70\x61\143\x65\x55\x52\x49\40\75\40" . $this->namespaceURI;
        $Kn .= "\54\40\x50\162\157\x74\157\x63\157\x6c\x42\x69\156\x64\151\156\147\x20\75\x20" . $this->protocolBinding;
        $Kn .= "\54\40\111\104\x20\75\x20" . $this->requestID;
        $Kn .= "\x2c\40\111\163\163\x75\x65\162\x20\x3d\x20" . $this->issuer;
        $Kn .= "\x2c\x20\x41\x43\x53\x20\125\x52\x4c\x20\x3d\x20" . $this->assertionConsumerServiceURL;
        $Kn .= "\54\x20\104\145\x73\164\151\x6e\x61\164\151\x6f\x6e\40\75\x20" . $this->destination;
        $Kn .= "\54\40\x46\x6f\x72\x6d\x61\x74\x20\75\x20" . implode("\x2c", $this->nameIdPolicy);
        $Kn .= "\x2c\x20\101\154\x6c\x6f\x77\x20\103\x72\145\141\x74\145\x20\75\40" . implode("\x2c", $this->nameIdPolicy);
        $Kn .= "\x2c\x20\106\x6f\x72\x63\145\40\x41\x75\x74\150\x6e\x20\75\40" . $this->forceAuthn;
        $Kn .= "\x2c\40\x49\x73\x73\165\x65\40\111\156\x73\x74\141\x6e\x74\x20\x3d\40" . $this->issueInstant;
        $Kn .= "\54\x20\126\x65\162\x73\151\x6f\x6e\x20\x3d\40" . $this->version;
        $Kn .= "\x2c\40\x52\145\161\165\x65\x73\164\145\x72\111\x44\x20\x3d\40" . implode("\54", $this->RequesterID);
        $Kn .= "\x5d";
        return $Kn;
    }
    public function getXml()
    {
        return $this->xml;
    }
    public function setXml($eL)
    {
        $this->xml = $eL;
        return $this;
    }
    public function getNameIdPolicy()
    {
        return $this->nameIdPolicy;
    }
    public function setNameIdPolicy($iD)
    {
        $this->nameIdPolicy = $iD;
        return $this;
    }
    public function getForceAuthn()
    {
        return $this->forceAuthn;
    }
    public function setForceAuthn($ZP)
    {
        $this->forceAuthn = $ZP;
        return $this;
    }
    public function getIsPassive()
    {
        return $this->isPassive;
    }
    public function setIsPassive($RH)
    {
        $this->isPassive = $RH;
        return $this;
    }
    public function getRequesterID()
    {
        return $this->RequesterID;
    }
    public function setRequesterID($LR)
    {
        $this->RequesterID = $LR;
        return $this;
    }
    public function getAssertionConsumerServiceURL()
    {
        return $this->assertionConsumerServiceURL;
    }
    public function setAssertionConsumerServiceURL($xh)
    {
        $this->assertionConsumerServiceURL = $xh;
        return $this;
    }
    public function getProtocolBinding()
    {
        return $this->protocolBinding;
    }
    public function setProtocolBinding($dE)
    {
        $this->protocolBinding = $dE;
        return $this;
    }
    public function getRequestedAuthnContext()
    {
        return $this->requestedAuthnContext;
    }
    public function setRequestedAuthnContext($zC)
    {
        $this->requestedAuthnContext = $zC;
        return $this;
    }
    public function getNamespaceURI()
    {
        return $this->namespaceURI;
    }
    public function setNamespaceURI($hA)
    {
        $this->namespaceURI = $hA;
        return $this;
    }
    public function getDestination()
    {
        return $this->destination;
    }
    public function setDestination($Ru)
    {
        $this->destination = $Ru;
        return $this;
    }
    public function getIssuer()
    {
        return $this->issuer;
    }
    public function setIssuer($QB)
    {
        $this->issuer = $QB;
        return $this;
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function setVersion($oB)
    {
        $this->version = $oB;
        return $this;
    }
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }
    public function setIssueInstant($JE)
    {
        $this->issueInstant = $JE;
        return $this;
    }
    public function getRequestID()
    {
        return $this->requestID;
    }
    public function setRequestID($A2)
    {
        $this->requestID = $A2;
        return $this;
    }
    public function getRequestType()
    {
        return $this->requestType;
    }
    public function setRequestType($WY)
    {
        $this->requestType = $WY;
        return $this;
    }
}
