<?php


namespace RobRichards\XMLSecLibs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use RobRichards\XMLSecLibs\Utils\XPath as XPath;
class XMLSecEnc
{
    const template = "\x3c\170\x65\156\143\72\x45\156\x63\x72\171\x70\x74\145\x64\x44\141\164\x61\x20\x78\x6d\x6c\156\163\x3a\170\x65\x6e\143\75\47\150\x74\164\x70\x3a\57\57\167\167\167\56\167\63\x2e\157\162\147\x2f\62\x30\x30\61\57\x30\x34\57\170\x6d\x6c\145\x6e\143\43\x27\x3e\xd\xa\x20\40\x20\74\x78\145\x6e\x63\72\x43\x69\160\150\145\162\x44\x61\x74\x61\x3e\15\12\x20\x20\x20\40\x20\x20\x3c\170\145\x6e\143\x3a\x43\151\x70\150\145\x72\126\x61\154\x75\145\x3e\x3c\57\x78\x65\x6e\x63\72\103\151\160\150\145\x72\126\141\x6c\165\x65\x3e\xd\xa\40\40\x20\74\57\170\145\156\x63\72\103\151\x70\150\x65\x72\x44\141\164\x61\76\15\xa\74\x2f\x78\145\x6e\x63\x3a\x45\156\x63\x72\x79\x70\164\145\x64\104\x61\164\x61\x3e";
    const Element = "\150\164\164\x70\72\57\x2f\167\167\x77\56\x77\63\56\157\x72\x67\x2f\x32\x30\60\61\x2f\x30\64\57\x78\155\x6c\x65\x6e\143\x23\105\154\x65\x6d\145\156\164";
    const Content = "\150\164\x74\160\x3a\x2f\57\x77\167\x77\56\x77\63\x2e\x6f\162\147\x2f\x32\60\60\x31\57\x30\64\57\170\x6d\154\x65\x6e\143\x23\103\x6f\x6e\x74\145\156\x74";
    const URI = 3;
    const XMLENCNS = "\150\x74\x74\x70\72\57\57\167\x77\x77\x2e\167\x33\56\x6f\162\147\57\x32\x30\x30\61\x2f\60\64\57\170\x6d\x6c\145\x6e\x63\x23";
    private $encdoc = null;
    private $rawNode = null;
    public $type = null;
    public $encKey = null;
    private $references = array();
    public function __construct()
    {
        $this->_resetTemplate();
    }
    private function _resetTemplate()
    {
        $this->encdoc = new DOMDocument();
        $this->encdoc->loadXML(self::template);
    }
    public function addReference($Pk, $D_, $ZV)
    {
        if ($D_ instanceof DOMNode) {
            goto R_;
        }
        throw new Exception("\44\156\157\x64\145\40\x69\x73\x20\x6e\157\164\40\157\146\40\x74\171\x70\x65\x20\x44\x4f\x4d\116\x6f\x64\145");
        R_:
        $m2 = $this->encdoc;
        $this->_resetTemplate();
        $DI = $this->encdoc;
        $this->encdoc = $m2;
        $xL = XMLSecurityDSig::generateGUID();
        $sU = $DI->documentElement;
        $sU->setAttribute("\111\144", $xL);
        $this->references[$Pk] = array("\156\157\144\x65" => $D_, "\164\171\x70\145" => $ZV, "\145\156\143\x6e\x6f\x64\x65" => $DI, "\162\x65\146\165\162\151" => $xL);
    }
    public function setNode($D_)
    {
        $this->rawNode = $D_;
    }
    public function encryptNode($td, $A3 = true)
    {
        $fL = '';
        if (!empty($this->rawNode)) {
            goto sv;
        }
        throw new Exception("\116\x6f\144\145\40\x74\157\x20\x65\x6e\143\162\x79\160\164\x20\150\x61\x73\40\156\x6f\x74\40\x62\x65\145\156\x20\x73\x65\x74");
        sv:
        if ($td instanceof XMLSecurityKey) {
            goto vh;
        }
        throw new Exception("\x49\x6e\166\x61\154\151\144\40\x4b\x65\171");
        vh:
        $oy = $this->rawNode->ownerDocument;
        $da = new DOMXPath($this->encdoc);
        $Nd = $da->query("\x2f\170\145\156\x63\72\105\156\x63\162\x79\160\x74\x65\144\104\x61\164\141\57\170\145\x6e\x63\x3a\x43\151\160\150\145\162\104\x61\x74\x61\x2f\x78\x65\156\143\72\x43\x69\160\x68\x65\x72\x56\x61\154\x75\x65");
        $jn = $Nd->item(0);
        if (!($jn == null)) {
            goto fX;
        }
        throw new Exception("\105\x72\x72\157\x72\40\x6c\157\143\x61\164\151\156\147\x20\103\x69\x70\150\145\162\126\141\x6c\x75\x65\x20\145\154\x65\x6d\x65\x6e\x74\x20\x77\151\x74\x68\151\156\40\x74\x65\155\160\154\x61\x74\x65");
        fX:
        switch ($this->type) {
            case self::Element:
                $fL = $oy->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute("\124\x79\160\x65", self::Element);
                goto CI;
            case self::Content:
                $iV = $this->rawNode->childNodes;
                foreach ($iV as $Ag) {
                    $fL .= $oy->saveXML($Ag);
                    X0:
                }
                mj:
                $this->encdoc->documentElement->setAttribute("\124\x79\160\x65", self::Content);
                goto CI;
            default:
                throw new Exception("\x54\171\160\x65\x20\151\x73\x20\x63\165\162\x72\145\156\164\154\x79\40\156\x6f\x74\x20\163\x75\160\160\157\x72\164\x65\144");
        }
        VX:
        CI:
        $eI = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\145\x6e\x63\72\x45\156\x63\x72\x79\160\x74\x69\x6f\156\115\145\164\x68\157\144"));
        $eI->setAttribute("\x41\154\147\x6f\x72\151\x74\150\x6d", $td->getAlgorithm());
        $jn->parentNode->parentNode->insertBefore($eI, $jn->parentNode->parentNode->firstChild);
        $ha = base64_encode($td->encryptData($fL));
        $l7 = $this->encdoc->createTextNode($ha);
        $jn->appendChild($l7);
        if ($A3) {
            goto gg;
        }
        return $this->encdoc->documentElement;
        goto Nt;
        gg:
        switch ($this->type) {
            case self::Element:
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto ro;
                }
                return $this->encdoc;
                ro:
                $Tw = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                $this->rawNode->parentNode->replaceChild($Tw, $this->rawNode);
                return $Tw;
            case self::Content:
                $Tw = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                uC:
                if (!$this->rawNode->firstChild) {
                    goto xT;
                }
                $this->rawNode->removeChild($this->rawNode->firstChild);
                goto uC;
                xT:
                $this->rawNode->appendChild($Tw);
                return $Tw;
        }
        fj:
        jk:
        Nt:
    }
    public function encryptReferences($td)
    {
        $uA = $this->rawNode;
        $Xn = $this->type;
        foreach ($this->references as $Pk => $ln) {
            $this->encdoc = $ln["\x65\x6e\x63\x6e\x6f\x64\145"];
            $this->rawNode = $ln["\x6e\157\144\x65"];
            $this->type = $ln["\164\x79\x70\x65"];
            try {
                $zL = $this->encryptNode($td);
                $this->references[$Pk]["\x65\x6e\x63\156\157\x64\x65"] = $zL;
            } catch (Exception $S5) {
                $this->rawNode = $uA;
                $this->type = $Xn;
                throw $S5;
            }
            aU:
        }
        Eg:
        $this->rawNode = $uA;
        $this->type = $Xn;
    }
    public function getCipherValue()
    {
        if (!empty($this->rawNode)) {
            goto V3;
        }
        throw new Exception("\116\x6f\144\145\x20\164\157\40\144\x65\x63\162\171\160\x74\40\150\x61\163\40\x6e\157\164\x20\142\x65\x65\156\x20\163\145\x74");
        V3:
        $oy = $this->rawNode->ownerDocument;
        $da = new DOMXPath($oy);
        $da->registerNamespace("\170\155\154\x65\x6e\x63\x72", self::XMLENCNS);
        $lB = "\56\57\x78\155\154\145\x6e\x63\x72\x3a\x43\x69\160\x68\145\x72\x44\x61\x74\x61\57\x78\x6d\x6c\x65\x6e\x63\162\72\103\151\x70\150\145\162\x56\141\154\x75\145";
        $ET = $da->query($lB, $this->rawNode);
        $D_ = $ET->item(0);
        if ($D_) {
            goto Yf;
        }
        return null;
        Yf:
        return base64_decode($D_->nodeValue);
    }
    public function decryptNode($td, $A3 = true)
    {
        if ($td instanceof XMLSecurityKey) {
            goto YP;
        }
        throw new Exception("\x49\156\166\x61\154\151\144\40\x4b\145\171");
        YP:
        $ZU = $this->getCipherValue();
        if ($ZU) {
            goto c7;
        }
        throw new Exception("\103\141\x6e\x6e\x6f\x74\40\154\x6f\143\141\164\x65\x20\145\156\143\x72\x79\x70\x74\x65\x64\x20\144\x61\x74\141");
        goto zI;
        c7:
        $d0 = $td->decryptData($ZU);
        if ($A3) {
            goto k9;
        }
        return $d0;
        goto JX;
        k9:
        switch ($this->type) {
            case self::Element:
                $rQ = new DOMDocument();
                $rQ->loadXML($d0);
                if (!($this->rawNode->nodeType == XML_DOCUMENT_NODE)) {
                    goto pR;
                }
                return $rQ;
                pR:
                $Tw = $this->rawNode->ownerDocument->importNode($rQ->documentElement, true);
                $this->rawNode->parentNode->replaceChild($Tw, $this->rawNode);
                return $Tw;
            case self::Content:
                if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                    goto SJ;
                }
                $oy = $this->rawNode->ownerDocument;
                goto R6;
                SJ:
                $oy = $this->rawNode;
                R6:
                $Or = $oy->createDocumentFragment();
                $Or->appendXML($d0);
                $EB = $this->rawNode->parentNode;
                $EB->replaceChild($Or, $this->rawNode);
                return $EB;
            default:
                return $d0;
        }
        Pc:
        lc:
        JX:
        zI:
    }
    public function encryptKey($ms, $fD, $MA = true)
    {
        if (!(!$ms instanceof XMLSecurityKey || !$fD instanceof XMLSecurityKey)) {
            goto n7;
        }
        throw new Exception("\x49\x6e\166\x61\154\x69\x64\40\x4b\145\x79");
        n7:
        $zz = base64_encode($ms->encryptData($fD->key));
        $u2 = $this->encdoc->documentElement;
        $DS = $this->encdoc->createElementNS(self::XMLENCNS, "\170\x65\156\143\72\x45\x6e\x63\x72\x79\160\x74\x65\x64\x4b\x65\x79");
        if ($MA) {
            goto X2;
        }
        $this->encKey = $DS;
        goto gv;
        X2:
        $Zr = $u2->insertBefore($this->encdoc->createElementNS("\150\164\x74\x70\x3a\57\x2f\167\x77\167\56\x77\63\56\157\162\147\x2f\62\x30\60\x30\x2f\60\71\57\x78\x6d\154\144\x73\x69\x67\x23", "\144\163\151\x67\x3a\113\145\x79\x49\x6e\146\x6f"), $u2->firstChild);
        $Zr->appendChild($DS);
        gv:
        $eI = $DS->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\145\156\x63\x3a\x45\156\x63\x72\x79\x70\x74\151\x6f\156\115\145\164\150\x6f\144"));
        $eI->setAttribute("\101\x6c\147\x6f\x72\x69\164\x68\x6d", $ms->getAlgorith());
        if (empty($ms->name)) {
            goto qV;
        }
        $Zr = $DS->appendChild($this->encdoc->createElementNS("\x68\x74\x74\x70\72\57\57\x77\167\167\56\x77\63\x2e\157\x72\147\x2f\62\x30\60\60\57\60\71\x2f\170\155\x6c\x64\x73\151\x67\43", "\144\x73\x69\x67\72\113\x65\x79\111\156\146\157"));
        $Zr->appendChild($this->encdoc->createElementNS("\150\x74\164\160\72\57\x2f\167\x77\167\56\167\x33\56\x6f\162\x67\57\62\x30\x30\x30\x2f\x30\x39\x2f\x78\x6d\x6c\x64\x73\x69\147\x23", "\144\x73\151\x67\72\x4b\145\171\116\x61\155\145", $ms->name));
        qV:
        $gJ = $DS->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\145\x6e\143\x3a\103\151\x70\x68\x65\x72\x44\x61\164\x61"));
        $gJ->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\145\156\x63\x3a\x43\x69\160\x68\145\x72\x56\x61\154\x75\145", $zz));
        if (!(is_array($this->references) && count($this->references) > 0)) {
            goto lB;
        }
        $Yv = $DS->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\x78\145\156\x63\x3a\122\x65\146\x65\x72\x65\x6e\x63\x65\x4c\x69\163\x74"));
        foreach ($this->references as $Pk => $ln) {
            $xL = $ln["\162\145\146\x75\162\151"];
            $uq = $Yv->appendChild($this->encdoc->createElementNS(self::XMLENCNS, "\170\x65\156\143\72\104\141\164\x61\122\x65\146\145\x72\x65\x6e\x63\145"));
            $uq->setAttribute("\x55\122\111", "\43" . $xL);
            Rb:
        }
        wZ:
        lB:
        return;
    }
    public function decryptKey($DS)
    {
        if ($DS->isEncrypted) {
            goto a1;
        }
        throw new Exception("\113\x65\x79\x20\151\163\x20\x6e\157\164\x20\x45\x6e\143\x72\171\160\164\x65\x64");
        a1:
        if (!empty($DS->key)) {
            goto O5;
        }
        throw new Exception("\113\145\171\40\151\163\40\155\x69\x73\x73\x69\x6e\x67\x20\144\141\164\x61\40\164\157\x20\x70\145\x72\x66\157\x72\x6d\x20\164\x68\145\40\144\x65\143\162\171\x70\164\151\157\156");
        O5:
        return $this->decryptNode($DS, false);
    }
    public function locateEncryptedData($sU)
    {
        if ($sU instanceof DOMDocument) {
            goto My;
        }
        $oy = $sU->ownerDocument;
        goto qS;
        My:
        $oy = $sU;
        qS:
        if (!$oy) {
            goto Qx;
        }
        $wC = new DOMXPath($oy);
        $lB = "\57\x2f\x2a\133\154\x6f\x63\x61\154\55\156\x61\155\145\50\x29\75\x27\x45\156\143\162\x79\160\164\145\x64\x44\141\164\x61\47\40\x61\156\x64\x20\x6e\x61\x6d\145\163\160\141\x63\145\55\165\162\151\x28\51\75\x27" . self::XMLENCNS . "\47\135";
        $ET = $wC->query($lB);
        return $ET->item(0);
        Qx:
        return null;
    }
    public function locateKey($D_ = null)
    {
        if (!empty($D_)) {
            goto Ac;
        }
        $D_ = $this->rawNode;
        Ac:
        if ($D_ instanceof DOMNode) {
            goto yy;
        }
        return null;
        yy:
        if (!($oy = $D_->ownerDocument)) {
            goto aH;
        }
        $wC = new DOMXPath($oy);
        $wC->registerNamespace("\x78\x6d\x6c\x73\145\x63\145\x6e\x63", self::XMLENCNS);
        $lB = "\56\x2f\x2f\x78\155\x6c\x73\145\x63\x65\x6e\x63\x3a\x45\156\x63\162\171\x70\x74\151\157\156\115\x65\x74\x68\x6f\x64";
        $ET = $wC->query($lB, $D_);
        if (!($p1 = $ET->item(0))) {
            goto b6;
        }
        $ma = $p1->getAttribute("\x41\x6c\x67\x6f\x72\x69\164\150\x6d");
        try {
            $td = new XMLSecurityKey($ma, array("\164\x79\x70\145" => "\160\x72\151\x76\141\164\x65"));
        } catch (Exception $S5) {
            return null;
        }
        return $td;
        b6:
        aH:
        return null;
    }
    public static function staticLocateKeyInfo($DK = null, $D_ = null)
    {
        if (!(empty($D_) || !$D_ instanceof DOMNode)) {
            goto qt;
        }
        return null;
        qt:
        $oy = $D_->ownerDocument;
        if ($oy) {
            goto AQ;
        }
        return null;
        AQ:
        $wC = new DOMXPath($oy);
        $wC->registerNamespace("\x78\155\x6c\163\x65\x63\145\156\143", self::XMLENCNS);
        $wC->registerNamespace("\170\155\154\x73\x65\143\x64\163\x69\147", XMLSecurityDSig::XMLDSIGNS);
        $lB = "\x2e\57\170\x6d\154\163\145\x63\144\x73\151\x67\x3a\113\x65\x79\x49\156\x66\x6f";
        $ET = $wC->query($lB, $D_);
        $p1 = $ET->item(0);
        if ($p1) {
            goto Iq;
        }
        return $DK;
        Iq:
        foreach ($p1->childNodes as $Ag) {
            switch ($Ag->localName) {
                case "\x4b\x65\171\x4e\x61\155\145":
                    if (empty($DK)) {
                        goto NE;
                    }
                    $DK->name = $Ag->nodeValue;
                    NE:
                    goto iA;
                case "\x4b\145\171\126\x61\x6c\165\145":
                    foreach ($Ag->childNodes as $Gd) {
                        switch ($Gd->localName) {
                            case "\104\123\101\113\145\x79\126\141\154\x75\145":
                                throw new Exception("\104\123\101\x4b\145\x79\126\x61\x6c\x75\145\x20\143\165\x72\162\145\156\x74\x6c\x79\x20\x6e\157\x74\x20\x73\165\160\x70\157\x72\x74\x65\144");
                            case "\122\x53\x41\x4b\145\171\126\x61\x6c\165\145":
                                $mQ = null;
                                $j1 = null;
                                if (!($jh = $Gd->getElementsByTagName("\115\x6f\x64\165\x6c\x75\x73")->item(0))) {
                                    goto bo;
                                }
                                $mQ = base64_decode($jh->nodeValue);
                                bo:
                                if (!($El = $Gd->getElementsByTagName("\x45\x78\160\157\156\145\156\x74")->item(0))) {
                                    goto VA;
                                }
                                $j1 = base64_decode($El->nodeValue);
                                VA:
                                if (!(empty($mQ) || empty($j1))) {
                                    goto xw;
                                }
                                throw new Exception("\x4d\151\163\163\x69\x6e\147\40\115\x6f\144\165\x6c\x75\x73\40\157\162\x20\x45\170\160\157\x6e\145\156\x74");
                                xw:
                                $U_ = XMLSecurityKey::convertRSA($mQ, $j1);
                                $DK->loadKey($U_);
                                goto VG;
                        }
                        sO:
                        VG:
                        yx:
                    }
                    oE:
                    goto iA;
                case "\x52\x65\164\x72\151\145\x76\141\154\115\x65\x74\x68\157\144":
                    $ZV = $Ag->getAttribute("\x54\x79\160\145");
                    if (!($ZV !== "\x68\164\x74\x70\x3a\x2f\57\x77\x77\x77\x2e\x77\x33\56\157\x72\x67\57\62\60\60\x31\x2f\60\64\x2f\x78\x6d\x6c\x65\156\143\x23\x45\156\x63\x72\171\x70\164\x65\x64\113\145\171")) {
                        goto ai;
                    }
                    goto iA;
                    ai:
                    $V7 = $Ag->getAttribute("\125\122\x49");
                    if (!($V7[0] !== "\x23")) {
                        goto gy;
                    }
                    goto iA;
                    gy:
                    $e6 = substr($V7, 1);
                    $lB = "\x2f\x2f\170\155\154\163\145\143\x65\x6e\x63\x3a\x45\156\143\x72\171\160\x74\145\x64\x4b\x65\171\x5b\100\111\144\75\x22" . XPath::filterAttrValue($e6, XPath::DOUBLE_QUOTE) . "\42\x5d";
                    $KN = $wC->query($lB)->item(0);
                    if ($KN) {
                        goto ON;
                    }
                    throw new Exception("\x55\x6e\x61\142\x6c\x65\x20\x74\x6f\x20\154\157\x63\141\164\x65\x20\x45\x6e\143\162\x79\160\x74\x65\x64\x4b\x65\x79\x20\167\x69\x74\x68\x20\x40\x49\144\x3d\47{$e6}\47\56");
                    ON:
                    return XMLSecurityKey::fromEncryptedKeyElement($KN);
                case "\x45\156\x63\162\171\x70\x74\145\144\x4b\x65\171":
                    return XMLSecurityKey::fromEncryptedKeyElement($Ag);
                case "\130\x35\60\71\104\141\164\141":
                    if (!($yE = $Ag->getElementsByTagName("\x58\x35\60\x39\x43\x65\162\164\151\x66\x69\143\x61\x74\145"))) {
                        goto a7;
                    }
                    if (!($yE->length > 0)) {
                        goto VT;
                    }
                    $l0 = $yE->item(0)->textContent;
                    $l0 = str_replace(array("\15", "\12", "\40"), '', $l0);
                    $l0 = "\x2d\x2d\x2d\x2d\55\x42\105\107\111\x4e\x20\103\x45\x52\x54\111\106\111\x43\x41\x54\x45\x2d\x2d\55\55\55\12" . chunk_split($l0, 64, "\xa") . "\55\55\55\x2d\55\x45\116\104\x20\103\105\x52\124\111\106\x49\103\101\124\105\x2d\x2d\x2d\x2d\55\12";
                    $DK->loadKey($l0, false, true);
                    VT:
                    a7:
                    goto iA;
            }
            gD:
            iA:
            G5:
        }
        kE:
        return $DK;
    }
    public function locateKeyInfo($DK = null, $D_ = null)
    {
        if (!empty($D_)) {
            goto GY;
        }
        $D_ = $this->rawNode;
        GY:
        return self::staticLocateKeyInfo($DK, $D_);
    }
}
