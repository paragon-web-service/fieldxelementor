<?php


namespace IDP\Helper\SAML2;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
use IDP\Helper\Factory\RequestHandlerFactory;
use IDP\Exception\InvalidRequestVersionException;
use IDP\Exception\MissingNameIdException;
use IDP\Exception\InvalidNumberOfNameIDsException;
use IDP\Exception\MissingIDException;
class LogoutRequest implements RequestHandlerFactory
{
    private $xml;
    private $tagName;
    private $id;
    private $issuer;
    private $destination;
    private $issueInstant;
    private $certificates;
    private $validators;
    private $notOnOrAfter;
    private $encryptedNameId;
    private $nameId;
    private $sessionIndexes;
    private $requestType = MoIDPConstants::LOGOUT_REQUEST;
    public function __construct(\DOMElement $eL = NULL)
    {
        $this->xml = new \DOMDocument("\61\x2e\60", "\x75\x74\x66\x2d\x38");
        if (!($eL === NULL)) {
            goto pZ;
        }
        return;
        pZ:
        $this->xml = $eL;
        $this->tagName = "\114\157\x67\x6f\x75\x74\x52\x65\161\x75\145\163\164";
        $this->id = $this->generateUniqueID(40);
        $this->issueInstant = time();
        $this->certificates = array();
        $this->validators = array();
        $this->issueInstant = SAMLUtilities::xsDateTimeToTimestamp($eL->getAttribute("\x49\163\163\x75\x65\111\156\x73\164\141\x6e\164"));
        $this->parseID($eL);
        $this->checkSAMLVersion($eL);
        if (!$eL->hasAttribute("\104\145\163\x74\151\156\x61\164\151\157\156")) {
            goto A3;
        }
        $this->destination = $eL->getAttribute("\x44\145\x73\x74\x69\x6e\x61\x74\151\157\x6e");
        A3:
        $this->parseIssuer($eL);
        $this->parseAndValidateSignature($eL);
        if (!$eL->hasAttribute("\x4e\157\164\x4f\x6e\x4f\162\101\x66\164\x65\x72")) {
            goto PL;
        }
        $this->notOnOrAfter = SAMLUtilities::xsDateTimeToTimestamp($eL->getAttribute("\x4e\x6f\164\117\156\117\x72\101\146\164\145\x72"));
        PL:
        $this->parseNameId($eL);
        $this->parseSessionIndexes($eL);
    }
    public function generateRequest()
    {
        $Fg = $this->createSAMLLogoutRequest();
        $this->xml->appendChild($Fg);
        $QB = $this->buildIssuer();
        $Fg->appendChild($QB);
        $Pb = $this->buildNameId();
        $Fg->appendChild($Pb);
        $FW = $this->buildSessionIndex();
        $Fg->appendChild($FW);
        $zX = $this->xml->saveXML();
        return $zX;
    }
    protected function createSAMLLogoutRequest()
    {
        $Fg = $this->xml->createElementNS("\165\x72\x6e\x3a\157\141\163\151\x73\72\x6e\141\155\145\x73\x3a\164\143\72\123\101\115\114\x3a\x32\56\x30\72\x70\162\x6f\164\x6f\143\157\154", "\x73\x61\x6d\154\160\72\114\157\147\157\x75\164\122\145\x71\x75\x65\x73\164");
        $Fg->setAttribute("\x49\104", $this->generateUniqueID(40));
        $Fg->setAttribute("\x56\145\x72\163\x69\157\x6e", "\x32\56\60");
        $Fg->setAttribute("\111\163\163\165\x65\111\x6e\163\164\x61\x6e\x74", str_replace("\x2b\60\60\x3a\x30\60", "\132", gmdate("\x63", time())));
        $Fg->setAttribute("\x44\x65\163\x74\x69\x6e\x61\x74\151\157\x6e", $this->destination);
        return $Fg;
    }
    protected function buildIssuer()
    {
        return $this->xml->createElementNS("\165\x72\156\72\x6f\x61\x73\151\163\72\156\x61\x6d\145\163\72\x74\143\72\x53\101\x4d\114\x3a\x32\56\60\72\x61\163\x73\145\162\x74\151\x6f\156", "\163\x61\x6d\154\72\x49\163\x73\165\x65\x72", $this->issuer);
    }
    protected function buildNameId()
    {
        return $this->xml->createElementNS("\165\x72\156\72\x6f\141\x73\151\163\72\x6e\x61\x6d\x65\x73\x3a\164\x63\x3a\x53\x41\x4d\x4c\72\x32\x2e\x30\72\141\163\x73\x65\162\x74\x69\157\x6e", "\163\141\x6d\x6c\x3a\116\x61\x6d\145\x49\x44", $this->nameId);
    }
    protected function buildSessionIndex()
    {
        return $this->xml->createElement("\163\141\155\154\160\72\123\x65\163\x73\x69\157\x6e\111\x6e\x64\145\170", is_array($this->sessionIndexes) ? $this->sessionIndexes[0] : $this->sessionIndexes);
    }
    protected function parseID($eL)
    {
        if ($eL->hasAttribute("\x49\x44")) {
            goto XP;
        }
        throw new MissingIDException();
        XP:
        $this->id = $eL->getAttribute("\x49\104");
    }
    protected function checkSAMLVersion($eL)
    {
        if (!($eL->getAttribute("\126\145\x72\163\x69\157\x6e") !== "\x32\x2e\60")) {
            goto go;
        }
        throw InvalidRequestVersionException();
        go:
    }
    protected function parseIssuer($eL)
    {
        $QB = SAMLUtilities::xpQuery($eL, "\x2e\x2f\163\141\x6d\x6c\137\141\x73\163\145\162\x74\x69\157\x6e\x3a\111\x73\x73\165\145\162");
        if (empty($QB)) {
            goto Bm;
        }
        $this->issuer = trim($QB[0]->textContent);
        Bm:
    }
    protected function parseSessionIndexes($eL)
    {
        $this->sessionIndexes = array();
        $ut = SAMLUtilities::xpQuery($eL, "\56\x2f\x73\x61\x6d\x6c\137\x70\162\157\x74\x6f\x63\x6f\x6c\x3a\123\145\163\x73\x69\x6f\x6e\111\x6e\x64\x65\x78");
        foreach ($ut as $FW) {
            $this->sessionIndexes[] = trim($FW->textContent);
            PO:
        }
        vQ:
    }
    protected function parseAndValidateSignature($eL)
    {
        try {
            $jK = SAMLUtilities::validateElement($eL);
            if (!($jK !== FALSE)) {
                goto FW;
            }
            $this->certificates = $jK["\x43\145\x72\164\151\x66\x69\143\141\x74\145\163"];
            $this->validators[] = array("\106\x75\156\143\x74\x69\157\156" => array("\x53\101\115\x4c\x55\164\x69\x6c\151\164\x69\x65\163", "\166\x61\x6c\x69\144\x61\164\x65\123\151\x67\x6e\x61\x74\x75\x72\145"), "\104\141\164\x61" => $jK);
            FW:
        } catch (Exception $S5) {
        }
    }
    protected function parseNameId($eL)
    {
        $Pb = SAMLUtilities::xpQuery($eL, "\56\57\x73\141\x6d\x6c\x5f\x61\x73\163\145\162\164\151\157\156\72\x4e\141\x6d\145\x49\104\x20\x7c\40\56\x2f\163\x61\155\154\137\x61\163\163\x65\162\164\x69\157\156\x3a\105\156\x63\x72\171\x70\164\x65\144\111\x44\57\x78\x65\x6e\143\72\x45\156\143\162\171\160\164\x65\x64\104\x61\164\x61");
        if (empty($Pb)) {
            goto fu;
        }
        if (count($Pb) > 1) {
            goto a3;
        }
        goto FS;
        fu:
        throw new MissingNameIdException();
        goto FS;
        a3:
        throw new InvalidNumberOfNameIDsException();
        FS:
        $Pb = $Pb[0];
        if ($Pb->localName === "\x45\x6e\x63\162\171\160\164\145\x64\x44\141\x74\141") {
            goto dC;
        }
        $this->nameId = SAMLUtilities::parseNameId($Pb);
        goto jZ;
        dC:
        $this->encryptedNameId = $Pb;
        jZ:
    }
    function generateUniqueID($kd)
    {
        return MoIDPUtility::generateRandomAlphanumericValue($kd);
    }
    public function __toString()
    {
        $Kn = "\114\117\107\x4f\125\x54\40\x52\105\121\125\105\x53\x54\x20\120\101\x52\101\115\x53\x20\x5b";
        $Kn .= "\124\x61\147\x4e\141\x6d\x65\x20\75\x20" . $this->tagName;
        $Kn .= "\54\x20\x76\x61\x6c\x69\x64\141\x74\157\162\x73\x20\75\x20\x20" . implode("\x2c", $this->validators);
        $Kn .= "\x2c\x20\x49\x44\40\x3d\40" . $this->id;
        $Kn .= "\x2c\x20\111\163\x73\x75\x65\x72\x20\75\40" . $this->issuer;
        $Kn .= "\54\40\116\157\x74\x20\x4f\x6e\40\117\x72\x20\x41\x66\164\145\162\x20\x3d\x20" . $this->notOnOrAfter;
        $Kn .= "\x2c\x20\104\145\x73\x74\x69\x6e\x61\164\151\157\156\x20\75\x20" . $this->destination;
        $Kn .= "\x2c\40\x45\x6e\x63\162\x79\160\164\145\144\x20\116\x61\x6d\145\111\104\40\75\40" . $this->encryptedNameId;
        $Kn .= "\54\x20\111\163\163\x75\x65\40\111\x6e\163\x74\141\x6e\x74\40\x3d\40" . $this->issueInstant;
        $Kn .= "\54\x20\x53\145\163\x73\151\x6f\x6e\x20\x49\156\x64\x65\170\145\x73\40\x3d\x20" . implode("\54", $this->sessionIndexes);
        $Kn .= "\135";
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
    public function getTagName()
    {
        return $this->tagName;
    }
    public function setTagName($su)
    {
        $this->tagName = $su;
        return $this;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($e6)
    {
        $this->id = $e6;
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
    public function getDestination()
    {
        return $this->destination;
    }
    public function setDestination($Ru)
    {
        $this->destination = $Ru;
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
    public function getCertificates()
    {
        return $this->certificates;
    }
    public function setCertificates($rq)
    {
        $this->certificates = $rq;
        return $this;
    }
    public function getValidators()
    {
        return $this->validators;
    }
    public function setValidators($Aw)
    {
        $this->validators = $Aw;
        return $this;
    }
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }
    public function setNotOnOrAfter($uk)
    {
        $this->notOnOrAfter = $uk;
        return $this;
    }
    public function getEncryptedNameId()
    {
        return $this->encryptedNameId;
    }
    public function setEncryptedNameId($QX)
    {
        $this->encryptedNameId = $QX;
        return $this;
    }
    public function getNameId()
    {
        return $this->nameId;
    }
    public function setNameId($Pb)
    {
        $this->nameId = $Pb;
        return $this;
    }
    public function getSessionIndexes()
    {
        return $this->sessionIndexes;
    }
    public function setSessionIndexes($ut)
    {
        $this->sessionIndexes = $ut;
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
