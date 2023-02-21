<?php


namespace RobRichards\XMLSecLibs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use RobRichards\XMLSecLibs\Utils\XPath as XPath;
class XMLSecurityDSig
{
    const XMLDSIGNS = "\150\164\x74\x70\x3a\x2f\x2f\x77\x77\x77\56\167\63\56\x6f\162\147\57\62\60\60\60\57\x30\x39\x2f\x78\155\154\x64\163\x69\147\43";
    const SHA1 = "\150\164\164\x70\x3a\x2f\57\x77\167\167\56\167\63\x2e\x6f\x72\147\57\62\x30\60\x30\57\60\x39\57\x78\x6d\154\144\x73\151\147\x23\x73\x68\x61\61";
    const SHA256 = "\150\164\164\x70\x3a\57\x2f\167\x77\x77\56\167\x33\56\157\x72\147\57\62\x30\60\61\x2f\60\x34\57\x78\155\x6c\x65\x6e\143\43\x73\150\x61\62\65\66";
    const SHA384 = "\150\164\x74\160\x3a\x2f\x2f\x77\167\x77\56\x77\x33\56\x6f\x72\x67\57\x32\60\60\x31\57\60\64\x2f\170\155\154\x64\163\x69\147\55\x6d\157\162\145\43\163\x68\x61\x33\70\64";
    const SHA512 = "\150\164\x74\160\72\x2f\57\x77\167\x77\56\167\x33\x2e\157\x72\x67\57\62\60\60\61\x2f\x30\64\x2f\x78\155\154\145\x6e\143\43\x73\x68\141\x35\61\62";
    const RIPEMD160 = "\150\164\x74\160\72\57\x2f\167\x77\167\56\167\63\56\x6f\x72\147\57\62\60\60\61\x2f\60\64\57\x78\155\154\145\156\x63\43\x72\x69\x70\145\x6d\144\61\66\x30";
    const C14N = "\x68\164\x74\160\x3a\57\57\167\167\167\x2e\x77\x33\x2e\x6f\162\147\x2f\x54\122\x2f\x32\60\x30\61\57\122\105\x43\x2d\x78\x6d\154\x2d\x63\61\64\x6e\55\x32\x30\x30\x31\60\x33\x31\x35";
    const C14N_COMMENTS = "\150\164\x74\x70\x3a\57\x2f\x77\167\167\56\x77\x33\x2e\157\x72\147\57\x54\x52\x2f\x32\60\x30\x31\x2f\x52\105\103\x2d\170\x6d\x6c\x2d\x63\61\x34\156\55\x32\x30\x30\61\60\x33\61\65\43\x57\151\x74\150\103\x6f\x6d\x6d\x65\x6e\x74\163";
    const EXC_C14N = "\x68\x74\164\x70\x3a\x2f\57\167\x77\x77\x2e\167\x33\56\157\x72\147\57\x32\60\x30\x31\x2f\61\x30\57\170\x6d\154\55\x65\x78\x63\55\143\x31\x34\156\43";
    const EXC_C14N_COMMENTS = "\x68\164\164\160\72\x2f\57\167\x77\167\56\x77\63\x2e\157\x72\147\x2f\x32\60\x30\x31\x2f\61\x30\x2f\170\x6d\154\x2d\145\170\x63\x2d\143\61\64\156\43\x57\x69\x74\150\103\x6f\x6d\x6d\145\x6e\164\163";
    const template = "\x3c\144\x73\72\123\151\x67\156\141\164\x75\x72\x65\x20\x78\155\154\156\163\x3a\144\x73\75\x22\150\x74\x74\x70\x3a\x2f\x2f\167\167\x77\x2e\167\63\56\157\x72\147\x2f\62\60\60\x30\x2f\60\x39\x2f\x78\x6d\154\144\163\151\x67\x23\x22\76\15\12\40\40\74\x64\163\x3a\x53\151\x67\156\145\x64\x49\x6e\146\157\76\15\xa\x20\x20\x20\40\74\144\x73\x3a\123\151\x67\156\x61\x74\x75\162\x65\x4d\145\164\x68\x6f\x64\40\x2f\x3e\15\12\x20\40\74\57\144\163\x3a\123\151\147\156\145\144\x49\156\146\x6f\76\15\xa\74\57\x64\x73\x3a\x53\x69\x67\156\141\164\x75\162\x65\76";
    const BASE_TEMPLATE = "\x3c\x53\x69\147\156\x61\164\165\162\145\40\x78\x6d\154\156\163\x3d\42\x68\x74\164\160\72\57\57\x77\167\167\56\167\x33\x2e\x6f\x72\147\x2f\x32\60\60\x30\57\x30\71\57\170\155\154\144\x73\151\147\x23\42\x3e\xd\12\40\40\74\123\151\x67\x6e\x65\x64\111\156\x66\x6f\x3e\15\xa\40\x20\40\40\x3c\123\151\147\x6e\141\164\x75\162\x65\115\145\x74\150\x6f\x64\x20\57\76\xd\xa\x20\40\74\57\123\151\x67\156\145\x64\x49\156\x66\157\76\xd\12\x3c\x2f\x53\x69\147\x6e\141\164\x75\x72\145\x3e";
    public $sigNode = null;
    public $idKeys = array();
    public $idNS = array();
    private $signedInfo = null;
    private $xPathCtx = null;
    private $canonicalMethod = null;
    private $prefix = '';
    private $searchpfx = "\x73\x65\143\x64\163\151\x67";
    private $validatedNodes = null;
    public function __construct($Pf = "\144\x73")
    {
        $To = self::BASE_TEMPLATE;
        if (empty($Pf)) {
            goto h3;
        }
        $this->prefix = $Pf . "\x3a";
        $nW = array("\x3c\x53", "\x3c\57\x53", "\x78\x6d\x6c\156\163\75");
        $A3 = array("\x3c{$Pf}\x3a\x53", "\74\57{$Pf}\x3a\x53", "\170\x6d\154\x6e\163\72{$Pf}\x3d");
        $To = str_replace($nW, $A3, $To);
        h3:
        $UD = new DOMDocument();
        $UD->loadXML($To);
        $this->sigNode = $UD->documentElement;
    }
    private function resetXPathObj()
    {
        $this->xPathCtx = null;
    }
    private function getXPathObj()
    {
        if (!(empty($this->xPathCtx) && !empty($this->sigNode))) {
            goto o7;
        }
        $wC = new DOMXPath($this->sigNode->ownerDocument);
        $wC->registerNamespace("\x73\145\143\144\163\x69\x67", self::XMLDSIGNS);
        $this->xPathCtx = $wC;
        o7:
        return $this->xPathCtx;
    }
    public static function generateGUID($Pf = "\x70\146\170")
    {
        $c3 = md5(uniqid(mt_rand(), true));
        $NH = $Pf . substr($c3, 0, 8) . "\55" . substr($c3, 8, 4) . "\55" . substr($c3, 12, 4) . "\55" . substr($c3, 16, 4) . "\55" . substr($c3, 20, 12);
        return $NH;
    }
    public static function generate_GUID($Pf = "\160\x66\x78")
    {
        return self::generateGUID($Pf);
    }
    public function locateSignature($hc, $ej = 0)
    {
        if ($hc instanceof DOMDocument) {
            goto nW;
        }
        $oy = $hc->ownerDocument;
        goto AE;
        nW:
        $oy = $hc;
        AE:
        if (!$oy) {
            goto Rm;
        }
        $wC = new DOMXPath($oy);
        $wC->registerNamespace("\x73\x65\x63\144\163\151\147", self::XMLDSIGNS);
        $lB = "\x2e\57\x2f\163\x65\x63\144\163\x69\147\x3a\123\x69\147\156\x61\164\165\162\145";
        $ET = $wC->query($lB, $hc);
        $this->sigNode = $ET->item($ej);
        $lB = "\x2e\57\x73\145\143\x64\163\x69\x67\x3a\x53\x69\x67\156\145\144\x49\156\146\157";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($ET->length > 1)) {
            goto Hp;
        }
        throw new Exception("\111\156\166\x61\x6c\x69\144\x20\163\x74\162\165\143\x74\x75\x72\145\40\x2d\40\x54\x6f\x6f\x20\155\141\x6e\x79\x20\123\x69\x67\x6e\x65\144\111\156\146\157\x20\145\154\x65\155\145\156\164\163\40\x66\x6f\x75\x6e\144");
        Hp:
        return $this->sigNode;
        Rm:
        return null;
    }
    public function createNewSignNode($Pk, $l7 = null)
    {
        $oy = $this->sigNode->ownerDocument;
        if (!is_null($l7)) {
            goto O1;
        }
        $D_ = $oy->createElementNS(self::XMLDSIGNS, $this->prefix . $Pk);
        goto bE;
        O1:
        $D_ = $oy->createElementNS(self::XMLDSIGNS, $this->prefix . $Pk, $l7);
        bE:
        return $D_;
    }
    public function setCanonicalMethod($qK)
    {
        switch ($qK) {
            case "\x68\164\x74\160\x3a\57\x2f\x77\167\167\x2e\x77\x33\56\157\162\147\57\124\122\57\62\x30\60\x31\57\122\105\x43\x2d\x78\x6d\x6c\55\143\x31\64\x6e\x2d\62\x30\60\x31\x30\x33\x31\x35":
            case "\x68\164\x74\x70\x3a\x2f\57\167\167\167\x2e\x77\63\x2e\x6f\162\147\57\x54\122\x2f\x32\x30\x30\61\57\122\105\103\x2d\x78\x6d\x6c\x2d\x63\61\64\x6e\x2d\x32\x30\60\61\60\x33\61\65\x23\127\x69\164\150\x43\x6f\155\155\x65\x6e\164\x73":
            case "\x68\164\164\x70\72\x2f\57\167\167\x77\56\167\x33\56\157\162\147\57\x32\60\x30\x31\57\x31\60\57\170\x6d\x6c\x2d\145\x78\x63\55\x63\61\x34\x6e\x23":
            case "\x68\x74\164\160\x3a\x2f\57\167\x77\167\x2e\x77\x33\x2e\157\162\147\57\62\x30\60\x31\57\61\60\x2f\x78\155\x6c\x2d\x65\170\143\55\x63\61\64\156\43\127\x69\x74\x68\x43\x6f\155\x6d\x65\x6e\164\163":
                $this->canonicalMethod = $qK;
                goto Io;
            default:
                throw new Exception("\111\x6e\x76\x61\154\x69\144\x20\103\x61\x6e\157\x6e\151\143\141\x6c\x20\115\145\x74\x68\157\144");
        }
        Ao:
        Io:
        if (!($wC = $this->getXPathObj())) {
            goto db;
        }
        $lB = "\x2e\57" . $this->searchpfx . "\72\x53\x69\x67\x6e\x65\x64\x49\156\x66\x6f";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($GS = $ET->item(0))) {
            goto VN;
        }
        $lB = "\56\57" . $this->searchpfx . "\103\x61\156\x6f\x6e\151\x63\141\154\151\172\141\x74\x69\x6f\156\x4d\x65\164\x68\157\x64";
        $ET = $wC->query($lB, $GS);
        if ($GZ = $ET->item(0)) {
            goto GU;
        }
        $GZ = $this->createNewSignNode("\103\x61\x6e\157\156\x69\x63\141\154\151\x7a\141\164\x69\157\156\x4d\x65\164\150\x6f\144");
        $GS->insertBefore($GZ, $GS->firstChild);
        GU:
        $GZ->setAttribute("\101\x6c\147\x6f\x72\151\x74\150\x6d", $this->canonicalMethod);
        VN:
        db:
    }
    private function canonicalizeData($D_, $g4, $a3 = null, $ru = null)
    {
        $qG = false;
        $gL = false;
        switch ($g4) {
            case "\150\164\x74\x70\72\x2f\x2f\167\x77\167\x2e\x77\63\x2e\157\162\x67\x2f\124\122\57\x32\x30\x30\x31\x2f\x52\105\x43\55\170\x6d\154\x2d\x63\61\x34\156\55\62\60\60\61\x30\x33\61\x35":
                $qG = false;
                $gL = false;
                goto i9;
            case "\x68\x74\164\160\x3a\57\x2f\167\x77\x77\x2e\167\x33\x2e\x6f\162\147\x2f\124\x52\57\62\60\x30\61\57\122\x45\x43\55\x78\155\x6c\x2d\143\x31\x34\156\55\x32\60\60\61\60\63\x31\65\x23\x57\x69\x74\150\103\157\x6d\x6d\145\156\164\x73":
                $gL = true;
                goto i9;
            case "\150\x74\x74\160\x3a\57\x2f\167\x77\x77\56\167\63\x2e\157\x72\x67\x2f\62\x30\x30\61\57\61\60\57\170\x6d\x6c\x2d\x65\x78\143\x2d\x63\x31\x34\x6e\x23":
                $qG = true;
                goto i9;
            case "\x68\x74\164\x70\72\x2f\57\x77\x77\x77\x2e\167\63\x2e\x6f\x72\x67\x2f\62\60\60\61\x2f\61\x30\x2f\170\155\154\x2d\x65\x78\143\x2d\143\x31\64\156\x23\x57\151\x74\150\103\157\x6d\155\x65\x6e\164\163":
                $qG = true;
                $gL = true;
                goto i9;
        }
        Op:
        i9:
        if (!(is_null($a3) && $D_ instanceof DOMNode && $D_->ownerDocument !== null && $D_->isSameNode($D_->ownerDocument->documentElement))) {
            goto cj;
        }
        $sU = $D_;
        SH:
        if (!($qI = $sU->previousSibling)) {
            goto aq;
        }
        if (!($qI->nodeType == XML_PI_NODE || $qI->nodeType == XML_COMMENT_NODE && $gL)) {
            goto ui;
        }
        goto aq;
        ui:
        $sU = $qI;
        goto SH;
        aq:
        if (!($qI == null)) {
            goto Lo;
        }
        $D_ = $D_->ownerDocument;
        Lo:
        cj:
        return $D_->C14N($qG, $gL, $a3, $ru);
    }
    public function canonicalizeSignedInfo()
    {
        $oy = $this->sigNode->ownerDocument;
        $g4 = null;
        if (!$oy) {
            goto YC;
        }
        $wC = $this->getXPathObj();
        $lB = "\56\57\163\x65\x63\144\x73\151\147\72\123\x69\x67\x6e\x65\x64\x49\156\146\157";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($ET->length > 1)) {
            goto bk;
        }
        throw new Exception("\x49\156\x76\141\154\151\x64\40\x73\164\162\x75\143\164\x75\162\145\40\55\40\x54\x6f\157\x20\155\141\x6e\x79\40\x53\x69\147\156\x65\x64\x49\156\146\x6f\x20\145\x6c\x65\x6d\145\156\x74\163\x20\146\x6f\x75\x6e\144");
        bk:
        if (!($Qs = $ET->item(0))) {
            goto J8;
        }
        $lB = "\56\x2f\x73\145\143\x64\163\151\x67\x3a\103\141\156\157\156\x69\x63\x61\154\x69\x7a\141\x74\x69\x6f\x6e\x4d\x65\x74\x68\x6f\144";
        $ET = $wC->query($lB, $Qs);
        $ru = null;
        if (!($GZ = $ET->item(0))) {
            goto Kz;
        }
        $g4 = $GZ->getAttribute("\101\x6c\x67\x6f\x72\151\164\x68\x6d");
        foreach ($GZ->childNodes as $D_) {
            if (!($D_->localName == "\111\x6e\x63\x6c\165\x73\x69\x76\x65\116\x61\x6d\x65\163\x70\141\x63\x65\x73")) {
                goto MT;
            }
            if (!($si = $D_->getAttribute("\120\162\x65\x66\x69\x78\114\x69\163\164"))) {
                goto fV;
            }
            $UF = array_filter(explode("\x20", $si));
            if (!(count($UF) > 0)) {
                goto Xd;
            }
            $ru = array_merge($ru ? $ru : array(), $UF);
            Xd:
            fV:
            MT:
            YW:
        }
        R0:
        Kz:
        $this->signedInfo = $this->canonicalizeData($Qs, $g4, null, $ru);
        return $this->signedInfo;
        J8:
        YC:
        return null;
    }
    public function calculateDigest($JD, $fL, $Al = true)
    {
        switch ($JD) {
            case self::SHA1:
                $t7 = "\x73\150\x61\x31";
                goto jO;
            case self::SHA256:
                $t7 = "\163\x68\x61\62\65\x36";
                goto jO;
            case self::SHA384:
                $t7 = "\x73\150\141\63\70\64";
                goto jO;
            case self::SHA512:
                $t7 = "\x73\150\141\x35\61\x32";
                goto jO;
            case self::RIPEMD160:
                $t7 = "\x72\x69\x70\x65\155\x64\61\x36\x30";
                goto jO;
            default:
                throw new Exception("\x43\141\x6e\156\157\x74\x20\x76\x61\154\151\x64\141\164\145\x20\x64\x69\147\x65\163\x74\72\40\125\156\x73\165\160\x70\157\162\164\x65\x64\40\101\x6c\x67\157\x72\151\164\x68\x6d\40\x3c{$JD}\76");
        }
        CK:
        jO:
        $ly = hash($t7, $fL, true);
        if (!$Al) {
            goto Gq;
        }
        $ly = base64_encode($ly);
        Gq:
        return $ly;
    }
    public function validateDigest($C5, $fL)
    {
        $wC = new DOMXPath($C5->ownerDocument);
        $wC->registerNamespace("\x73\x65\143\x64\163\151\x67", self::XMLDSIGNS);
        $lB = "\163\x74\x72\x69\156\x67\x28\56\x2f\x73\145\x63\x64\x73\151\147\x3a\104\x69\x67\x65\163\x74\115\145\x74\150\x6f\x64\x2f\100\x41\154\x67\157\x72\151\164\x68\155\51";
        $JD = $wC->evaluate($lB, $C5);
        $Bl = $this->calculateDigest($JD, $fL, false);
        $lB = "\163\x74\162\151\156\x67\x28\x2e\57\163\145\143\144\x73\151\x67\72\104\x69\147\x65\x73\164\x56\141\154\x75\x65\51";
        $PR = $wC->evaluate($lB, $C5);
        return $Bl === base64_decode($PR);
    }
    public function processTransforms($C5, $OT, $zT = true)
    {
        $fL = $OT;
        $wC = new DOMXPath($C5->ownerDocument);
        $wC->registerNamespace("\163\145\143\144\163\x69\147", self::XMLDSIGNS);
        $lB = "\x2e\57\163\145\x63\144\x73\x69\147\x3a\124\162\141\x6e\x73\146\x6f\162\x6d\163\57\x73\x65\143\x64\163\151\x67\72\124\x72\141\x6e\163\146\x6f\x72\x6d";
        $I6 = $wC->query($lB, $C5);
        $N4 = "\x68\164\164\x70\x3a\57\57\167\167\167\56\x77\63\x2e\x6f\x72\147\57\124\122\57\x32\60\x30\61\x2f\122\105\103\x2d\170\155\154\x2d\143\61\64\x6e\55\62\x30\x30\61\60\x33\x31\65";
        $a3 = null;
        $ru = null;
        foreach ($I6 as $M9) {
            $ib = $M9->getAttribute("\101\154\x67\x6f\162\x69\x74\x68\x6d");
            switch ($ib) {
                case "\x68\164\x74\x70\x3a\57\57\x77\167\x77\x2e\167\x33\x2e\x6f\162\x67\57\62\60\x30\61\57\x31\x30\x2f\x78\x6d\154\55\145\x78\143\55\143\61\x34\156\43":
                case "\x68\164\x74\x70\x3a\x2f\x2f\x77\x77\x77\56\x77\x33\x2e\x6f\x72\147\57\62\x30\60\61\57\61\x30\x2f\x78\155\x6c\x2d\x65\x78\143\x2d\143\61\x34\156\x23\127\151\x74\x68\x43\157\x6d\x6d\145\156\164\x73":
                    if (!$zT) {
                        goto It;
                    }
                    $N4 = $ib;
                    goto Sg;
                    It:
                    $N4 = "\150\164\164\x70\x3a\x2f\57\x77\x77\x77\x2e\x77\x33\56\157\x72\x67\57\x32\x30\x30\x31\57\61\x30\x2f\x78\x6d\x6c\x2d\x65\x78\x63\55\x63\x31\64\156\43";
                    Sg:
                    $D_ = $M9->firstChild;
                    Ta:
                    if (!$D_) {
                        goto gW;
                    }
                    if (!($D_->localName == "\x49\156\143\x6c\165\163\151\x76\x65\116\141\x6d\x65\x73\160\x61\143\145\163")) {
                        goto wp;
                    }
                    if (!($si = $D_->getAttribute("\x50\x72\x65\146\x69\x78\114\151\x73\x74"))) {
                        goto Sr;
                    }
                    $UF = array();
                    $QF = explode("\40", $si);
                    foreach ($QF as $si) {
                        $tL = trim($si);
                        if (empty($tL)) {
                            goto yV;
                        }
                        $UF[] = $tL;
                        yV:
                        Nn:
                    }
                    vv:
                    if (!(count($UF) > 0)) {
                        goto Kk;
                    }
                    $ru = $UF;
                    Kk:
                    Sr:
                    goto gW;
                    wp:
                    $D_ = $D_->nextSibling;
                    goto Ta;
                    gW:
                    goto Ul;
                case "\x68\164\164\160\72\x2f\x2f\167\x77\167\x2e\167\x33\x2e\157\x72\147\57\124\122\57\62\60\x30\x31\57\122\x45\x43\x2d\170\155\x6c\x2d\x63\61\x34\x6e\55\x32\60\60\61\60\x33\61\65":
                case "\x68\x74\x74\160\x3a\57\57\x77\x77\167\x2e\x77\63\56\157\162\147\57\124\122\57\x32\60\60\61\57\x52\x45\x43\55\x78\155\x6c\55\143\x31\x34\x6e\55\62\x30\60\x31\60\x33\61\65\43\x57\151\164\150\103\157\155\x6d\x65\156\164\163":
                    if (!$zT) {
                        goto mJ;
                    }
                    $N4 = $ib;
                    goto qO;
                    mJ:
                    $N4 = "\150\164\x74\160\72\57\x2f\167\167\167\x2e\167\x33\x2e\x6f\x72\x67\57\124\122\57\62\x30\x30\61\57\122\105\x43\x2d\170\x6d\x6c\x2d\143\x31\64\x6e\x2d\62\60\x30\61\x30\63\61\x35";
                    qO:
                    goto Ul;
                case "\x68\x74\164\160\x3a\57\57\167\x77\167\x2e\x77\x33\x2e\x6f\162\147\x2f\124\122\x2f\x31\x39\71\71\x2f\x52\x45\x43\55\170\x70\141\x74\150\55\x31\x39\x39\71\61\x31\61\x36":
                    $D_ = $M9->firstChild;
                    WH:
                    if (!$D_) {
                        goto k4;
                    }
                    if (!($D_->localName == "\130\120\x61\164\x68")) {
                        goto V2;
                    }
                    $a3 = array();
                    $a3["\161\x75\145\162\x79"] = "\50\56\x2f\x2f\56\40\x7c\40\56\x2f\57\100\52\40\174\40\56\57\x2f\x6e\x61\155\x65\163\160\141\143\145\72\72\52\x29\x5b" . $D_->nodeValue . "\x5d";
                    $a3["\x6e\141\x6d\x65\x73\160\x61\x63\x65\163"] = array();
                    $FP = $wC->query("\x2e\57\156\x61\x6d\x65\163\160\141\143\145\72\x3a\52", $D_);
                    foreach ($FP as $ZQ) {
                        if (!($ZQ->localName != "\x78\155\x6c")) {
                            goto d6;
                        }
                        $a3["\x6e\141\155\145\163\x70\141\x63\145\x73"][$ZQ->localName] = $ZQ->nodeValue;
                        d6:
                        MS:
                    }
                    wM:
                    goto k4;
                    V2:
                    $D_ = $D_->nextSibling;
                    goto WH;
                    k4:
                    goto Ul;
            }
            t3:
            Ul:
            N8:
        }
        Pl:
        if (!$fL instanceof DOMNode) {
            goto cP;
        }
        $fL = $this->canonicalizeData($OT, $N4, $a3, $ru);
        cP:
        return $fL;
    }
    public function processRefNode($C5)
    {
        $MY = null;
        $zT = true;
        if ($V7 = $C5->getAttribute("\x55\x52\111")) {
            goto rA;
        }
        $zT = false;
        $MY = $C5->ownerDocument;
        goto pB;
        rA:
        $UA = parse_url($V7);
        if (!empty($UA["\x70\141\164\x68"])) {
            goto YS;
        }
        if ($CQ = $UA["\146\x72\x61\x67\155\x65\156\164"]) {
            goto UA;
        }
        $MY = $C5->ownerDocument;
        goto k0;
        UA:
        $zT = false;
        $da = new DOMXPath($C5->ownerDocument);
        if (!($this->idNS && is_array($this->idNS))) {
            goto bW;
        }
        foreach ($this->idNS as $X4 => $kH) {
            $da->registerNamespace($X4, $kH);
            zF:
        }
        OF:
        bW:
        $N0 = "\x40\x49\144\75\42" . XPath::filterAttrValue($CQ, XPath::DOUBLE_QUOTE) . "\42";
        if (!is_array($this->idKeys)) {
            goto Px;
        }
        foreach ($this->idKeys as $kD) {
            $N0 .= "\x20\157\x72\x20\x40" . XPath::filterAttrName($kD) . "\x3d\42" . XPath::filterAttrValue($CQ, XPath::DOUBLE_QUOTE) . "\42";
            Pp:
        }
        gY:
        Px:
        $lB = "\x2f\x2f\52\133" . $N0 . "\x5d";
        $MY = $da->query($lB)->item(0);
        k0:
        YS:
        pB:
        $fL = $this->processTransforms($C5, $MY, $zT);
        if ($this->validateDigest($C5, $fL)) {
            goto KA;
        }
        return false;
        KA:
        if (!$MY instanceof DOMNode) {
            goto YI;
        }
        if (!empty($CQ)) {
            goto tO;
        }
        $this->validatedNodes[] = $MY;
        goto Vl;
        tO:
        $this->validatedNodes[$CQ] = $MY;
        Vl:
        YI:
        return true;
    }
    public function getRefNodeID($C5)
    {
        if (!($V7 = $C5->getAttribute("\125\122\x49"))) {
            goto AF;
        }
        $UA = parse_url($V7);
        if (!empty($UA["\160\141\x74\x68"])) {
            goto VB;
        }
        if (!($CQ = $UA["\x66\162\141\147\x6d\145\156\x74"])) {
            goto lS;
        }
        return $CQ;
        lS:
        VB:
        AF:
        return null;
    }
    public function getRefIDs()
    {
        $CW = array();
        $wC = $this->getXPathObj();
        $lB = "\x2e\x2f\x73\x65\143\x64\x73\151\x67\72\x53\x69\x67\156\145\x64\x49\x6e\x66\x6f\133\61\135\57\x73\x65\143\x64\x73\x69\x67\72\122\145\146\145\x72\x65\x6e\x63\x65";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($ET->length == 0)) {
            goto kQ;
        }
        throw new Exception("\x52\145\x66\x65\x72\x65\x6e\143\145\x20\x6e\x6f\x64\145\x73\40\x6e\157\x74\x20\146\x6f\165\156\x64");
        kQ:
        foreach ($ET as $C5) {
            $CW[] = $this->getRefNodeID($C5);
            Xk:
        }
        lN:
        return $CW;
    }
    public function validateReference()
    {
        $Sh = $this->sigNode->ownerDocument->documentElement;
        if ($Sh->isSameNode($this->sigNode)) {
            goto tw;
        }
        if (!($this->sigNode->parentNode != null)) {
            goto U2;
        }
        $this->sigNode->parentNode->removeChild($this->sigNode);
        U2:
        tw:
        $wC = $this->getXPathObj();
        $lB = "\56\57\x73\x65\143\144\163\151\147\72\123\151\x67\156\x65\x64\x49\156\146\157\133\61\x5d\x2f\163\145\x63\x64\163\151\x67\72\122\145\146\x65\x72\145\156\x63\145";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($ET->length == 0)) {
            goto QI;
        }
        throw new Exception("\x52\x65\146\x65\162\x65\x6e\143\x65\x20\x6e\x6f\144\x65\163\40\156\157\164\x20\x66\x6f\x75\x6e\144");
        QI:
        $this->validatedNodes = array();
        foreach ($ET as $C5) {
            if ($this->processRefNode($C5)) {
                goto m0;
            }
            $this->validatedNodes = null;
            throw new Exception("\122\x65\x66\x65\x72\145\156\x63\145\40\166\141\x6c\x69\x64\x61\x74\151\x6f\x6e\x20\146\x61\151\154\x65\144");
            m0:
            Yu:
        }
        BI:
        return true;
    }
    private function addRefInternal($a5, $D_, $ib, $Du = null, $m1 = null)
    {
        $Pf = null;
        $IJ = null;
        $zP = "\x49\144";
        $hP = true;
        $Tr = false;
        if (!is_array($m1)) {
            goto pp;
        }
        $Pf = empty($m1["\x70\x72\145\x66\151\170"]) ? null : $m1["\160\x72\x65\146\x69\170"];
        $IJ = empty($m1["\160\162\x65\x66\151\x78\x5f\x6e\163"]) ? null : $m1["\160\x72\145\x66\151\x78\137\156\163"];
        $zP = empty($m1["\151\144\x5f\x6e\x61\x6d\145"]) ? "\x49\144" : $m1["\151\x64\137\156\141\x6d\x65"];
        $hP = !isset($m1["\157\166\x65\162\x77\x72\x69\164\x65"]) ? true : (bool) $m1["\157\166\x65\x72\x77\x72\151\x74\145"];
        $Tr = !isset($m1["\x66\x6f\x72\143\x65\x5f\x75\162\x69"]) ? false : (bool) $m1["\x66\x6f\162\143\x65\x5f\x75\x72\151"];
        pp:
        $Pa = $zP;
        if (empty($Pf)) {
            goto Zn;
        }
        $Pa = $Pf . "\72" . $Pa;
        Zn:
        $C5 = $this->createNewSignNode("\122\145\x66\x65\162\x65\156\143\145");
        $a5->appendChild($C5);
        if (!$D_ instanceof DOMDocument) {
            goto n1;
        }
        if ($Tr) {
            goto Uu;
        }
        goto ix;
        n1:
        $V7 = null;
        if ($hP) {
            goto Bu;
        }
        $V7 = $IJ ? $D_->getAttributeNS($IJ, $zP) : $D_->getAttribute($zP);
        Bu:
        if (!empty($V7)) {
            goto UG;
        }
        $V7 = self::generateGUID();
        $D_->setAttributeNS($IJ, $Pa, $V7);
        UG:
        $C5->setAttribute("\125\122\111", "\43" . $V7);
        goto ix;
        Uu:
        $C5->setAttribute("\125\x52\x49", '');
        ix:
        $yf = $this->createNewSignNode("\124\x72\141\x6e\163\x66\x6f\x72\x6d\163");
        $C5->appendChild($yf);
        if (is_array($Du)) {
            goto Vt;
        }
        if (!empty($this->canonicalMethod)) {
            goto uO;
        }
        goto hp;
        Vt:
        foreach ($Du as $M9) {
            $r0 = $this->createNewSignNode("\124\162\141\156\x73\146\157\x72\155");
            $yf->appendChild($r0);
            if (is_array($M9) && !empty($M9["\x68\x74\164\160\x3a\57\57\x77\x77\167\56\x77\63\x2e\x6f\162\147\57\124\122\x2f\61\x39\71\71\57\122\x45\103\x2d\170\160\x61\164\150\55\x31\71\71\x39\61\x31\x31\66"]) && !empty($M9["\150\x74\164\x70\x3a\57\x2f\x77\167\x77\56\167\63\x2e\157\x72\x67\57\124\122\x2f\x31\71\71\71\x2f\x52\105\103\55\x78\x70\141\x74\150\x2d\x31\x39\x39\71\61\61\61\66"]["\x71\165\x65\162\x79"])) {
                goto BG;
            }
            $r0->setAttribute("\101\154\147\x6f\162\151\164\x68\x6d", $M9);
            goto gq;
            BG:
            $r0->setAttribute("\101\154\147\157\162\x69\x74\150\x6d", "\150\164\x74\160\72\57\57\167\167\167\x2e\167\x33\56\157\x72\x67\57\124\x52\57\61\x39\71\71\57\122\x45\103\x2d\170\x70\141\x74\150\55\61\71\71\x39\x31\61\x31\x36");
            $MK = $this->createNewSignNode("\130\120\x61\x74\x68", $M9["\150\164\164\160\72\x2f\57\x77\x77\167\56\167\x33\x2e\x6f\162\x67\x2f\124\x52\x2f\61\71\71\71\x2f\122\x45\103\55\170\x70\x61\x74\150\55\x31\x39\71\x39\x31\x31\61\x36"]["\x71\165\x65\162\171"]);
            $r0->appendChild($MK);
            if (empty($M9["\150\x74\x74\160\72\57\x2f\167\167\167\56\x77\x33\x2e\157\x72\x67\57\x54\x52\57\61\71\71\x39\x2f\122\x45\x43\x2d\x78\160\x61\x74\150\x2d\x31\71\71\71\61\x31\x31\66"]["\x6e\141\x6d\145\163\160\141\x63\145\x73"])) {
                goto AO;
            }
            foreach ($M9["\x68\x74\164\x70\72\x2f\x2f\167\x77\167\56\x77\x33\x2e\x6f\x72\147\x2f\124\x52\57\x31\71\x39\x39\x2f\x52\x45\103\55\x78\x70\141\164\150\x2d\61\x39\x39\71\x31\61\61\x36"]["\156\x61\155\145\163\160\141\143\145\163"] as $Pf => $q1) {
                $MK->setAttributeNS("\x68\164\164\x70\72\x2f\57\x77\x77\x77\56\x77\63\x2e\157\x72\x67\x2f\62\60\60\x30\x2f\170\x6d\154\156\163\57", "\x78\155\154\x6e\x73\x3a{$Pf}", $q1);
                V4:
            }
            eC:
            AO:
            gq:
            xC:
        }
        Ig:
        goto hp;
        uO:
        $r0 = $this->createNewSignNode("\x54\x72\x61\156\163\x66\x6f\162\155");
        $yf->appendChild($r0);
        $r0->setAttribute("\x41\x6c\x67\x6f\x72\151\164\x68\155", $this->canonicalMethod);
        hp:
        $ZO = $this->processTransforms($C5, $D_);
        $Bl = $this->calculateDigest($ib, $ZO);
        $j9 = $this->createNewSignNode("\104\151\147\x65\163\164\115\x65\164\150\157\144");
        $C5->appendChild($j9);
        $j9->setAttribute("\101\x6c\x67\157\162\x69\164\150\155", $ib);
        $PR = $this->createNewSignNode("\104\x69\x67\x65\x73\164\126\141\154\x75\145", $Bl);
        $C5->appendChild($PR);
    }
    public function addReference($D_, $ib, $Du = null, $m1 = null)
    {
        if (!($wC = $this->getXPathObj())) {
            goto GL;
        }
        $lB = "\56\57\163\x65\x63\x64\x73\x69\147\72\x53\x69\147\156\145\x64\111\x6e\146\157";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($hJ = $ET->item(0))) {
            goto nz;
        }
        $this->addRefInternal($hJ, $D_, $ib, $Du, $m1);
        nz:
        GL:
    }
    public function addReferenceList($Tx, $ib, $Du = null, $m1 = null)
    {
        if (!($wC = $this->getXPathObj())) {
            goto rI;
        }
        $lB = "\56\57\163\x65\143\x64\163\x69\147\72\123\151\x67\x6e\145\144\111\x6e\x66\x6f";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($hJ = $ET->item(0))) {
            goto Ks;
        }
        foreach ($Tx as $D_) {
            $this->addRefInternal($hJ, $D_, $ib, $Du, $m1);
            hE:
        }
        Tv:
        Ks:
        rI:
    }
    public function addObject($fL, $nf = null, $w0 = null)
    {
        $FU = $this->createNewSignNode("\x4f\142\x6a\145\143\164");
        $this->sigNode->appendChild($FU);
        if (empty($nf)) {
            goto Xv;
        }
        $FU->setAttribute("\115\x69\155\145\124\171\x70\145", $nf);
        Xv:
        if (empty($w0)) {
            goto zZ;
        }
        $FU->setAttribute("\x45\156\143\157\x64\151\x6e\x67", $w0);
        zZ:
        if ($fL instanceof DOMElement) {
            goto C1;
        }
        $l3 = $this->sigNode->ownerDocument->createTextNode($fL);
        goto bz;
        C1:
        $l3 = $this->sigNode->ownerDocument->importNode($fL, true);
        bz:
        $FU->appendChild($l3);
        return $FU;
    }
    public function locateKey($D_ = null)
    {
        if (!empty($D_)) {
            goto by;
        }
        $D_ = $this->sigNode;
        by:
        if ($D_ instanceof DOMNode) {
            goto oo;
        }
        return null;
        oo:
        if (!($oy = $D_->ownerDocument)) {
            goto t8;
        }
        $wC = new DOMXPath($oy);
        $wC->registerNamespace("\163\x65\x63\x64\163\x69\x67", self::XMLDSIGNS);
        $lB = "\x73\164\x72\x69\156\x67\x28\56\57\163\x65\x63\144\163\x69\147\72\123\x69\x67\156\x65\x64\x49\x6e\146\157\x2f\163\145\x63\x64\163\151\147\x3a\123\x69\x67\156\141\164\165\162\x65\x4d\145\x74\150\157\x64\x2f\100\101\x6c\147\x6f\162\x69\x74\150\x6d\x29";
        $ib = $wC->evaluate($lB, $D_);
        if (!$ib) {
            goto fp;
        }
        try {
            $td = new XMLSecurityKey($ib, array("\x74\x79\x70\x65" => "\x70\165\142\154\x69\x63"));
        } catch (Exception $S5) {
            return null;
        }
        return $td;
        fp:
        t8:
        return null;
    }
    public function verify($td)
    {
        $oy = $this->sigNode->ownerDocument;
        $wC = new DOMXPath($oy);
        $wC->registerNamespace("\x73\145\x63\144\163\x69\x67", self::XMLDSIGNS);
        $lB = "\x73\164\162\x69\x6e\x67\x28\56\x2f\163\x65\x63\x64\x73\151\147\x3a\x53\151\x67\x6e\x61\x74\x75\162\x65\x56\x61\154\x75\145\x29";
        $n2 = $wC->evaluate($lB, $this->sigNode);
        if (!empty($n2)) {
            goto Pv;
        }
        throw new Exception("\125\156\141\142\x6c\145\40\x74\x6f\40\154\157\x63\141\164\x65\40\x53\x69\147\156\141\164\165\162\145\126\x61\154\165\x65");
        Pv:
        return $td->verifySignature($this->signedInfo, base64_decode($n2));
    }
    public function signData($td, $fL)
    {
        return $td->signData($fL);
    }
    public function sign($td, $GD = null)
    {
        if (!($GD != null)) {
            goto ng;
        }
        $this->resetXPathObj();
        $this->appendSignature($GD);
        $this->sigNode = $GD->lastChild;
        ng:
        if (!($wC = $this->getXPathObj())) {
            goto Yv;
        }
        $lB = "\x2e\57\163\x65\143\144\163\x69\x67\72\x53\x69\147\156\x65\144\x49\x6e\146\157";
        $ET = $wC->query($lB, $this->sigNode);
        if (!($hJ = $ET->item(0))) {
            goto ax;
        }
        $lB = "\56\57\x73\145\143\x64\x73\151\147\72\x53\x69\147\156\x61\x74\165\x72\x65\x4d\x65\164\x68\x6f\x64";
        $ET = $wC->query($lB, $hJ);
        $wq = $ET->item(0);
        $wq->setAttribute("\101\x6c\147\x6f\x72\151\164\150\x6d", $td->type);
        $fL = $this->canonicalizeData($hJ, $this->canonicalMethod);
        $n2 = base64_encode($this->signData($td, $fL));
        $fj = $this->createNewSignNode("\123\x69\147\x6e\141\x74\x75\162\145\126\x61\154\165\x65", $n2);
        if ($uS = $hJ->nextSibling) {
            goto tJ;
        }
        $this->sigNode->appendChild($fj);
        goto zA;
        tJ:
        $uS->parentNode->insertBefore($fj, $uS);
        zA:
        ax:
        Yv:
    }
    public function appendCert()
    {
    }
    public function appendKey($td, $EB = null)
    {
        $td->serializeKey($EB);
    }
    public function insertSignature($D_, $sG = null)
    {
        $i2 = $D_->ownerDocument;
        $xB = $i2->importNode($this->sigNode, true);
        if ($sG == null) {
            goto rr;
        }
        return $D_->insertBefore($xB, $sG);
        goto dM;
        rr:
        return $D_->insertBefore($xB);
        dM:
    }
    public function appendSignature($hg, $De = false)
    {
        $sG = $De ? $hg->firstChild : null;
        return $this->insertSignature($hg, $sG);
    }
    public static function get509XCert($Ix, $im = true)
    {
        $OQ = self::staticGet509XCerts($Ix, $im);
        if (empty($OQ)) {
            goto xy;
        }
        return $OQ[0];
        xy:
        return '';
    }
    public static function staticGet509XCerts($OQ, $im = true)
    {
        if ($im) {
            goto z8;
        }
        return array($OQ);
        goto Bb;
        z8:
        $fL = '';
        $xC = array();
        $kF = explode("\xa", $OQ);
        $Rz = false;
        foreach ($kF as $sd) {
            if (!$Rz) {
                goto BC;
            }
            if (!(strncmp($sd, "\55\55\x2d\x2d\55\x45\x4e\x44\40\x43\x45\122\124\x49\106\x49\x43\101\x54\105", 20) == 0)) {
                goto wC;
            }
            $Rz = false;
            $xC[] = $fL;
            $fL = '';
            goto D4;
            wC:
            $fL .= trim($sd);
            goto YF;
            BC:
            if (!(strncmp($sd, "\x2d\55\55\x2d\55\102\x45\107\111\116\x20\x43\x45\x52\124\x49\x46\111\x43\101\124\x45", 22) == 0)) {
                goto G9;
            }
            $Rz = true;
            G9:
            YF:
            D4:
        }
        Ha:
        return $xC;
        Bb:
    }
    public static function staticAdd509Cert($OK, $Ix, $im = true, $oJ = false, $wC = null, $m1 = null)
    {
        if (!$oJ) {
            goto Df;
        }
        $Ix = file_get_contents($Ix);
        Df:
        if ($OK instanceof DOMElement) {
            goto wg;
        }
        throw new Exception("\111\x6e\x76\x61\x6c\151\144\40\160\x61\x72\x65\x6e\164\40\x4e\x6f\144\145\x20\x70\141\x72\x61\155\x65\x74\145\x72");
        wg:
        $sI = $OK->ownerDocument;
        if (!empty($wC)) {
            goto WZ;
        }
        $wC = new DOMXPath($OK->ownerDocument);
        $wC->registerNamespace("\163\x65\143\x64\x73\x69\x67", self::XMLDSIGNS);
        WZ:
        $lB = "\x2e\57\x73\x65\143\144\x73\x69\147\x3a\x4b\145\171\111\x6e\x66\157";
        $ET = $wC->query($lB, $OK);
        $Zr = $ET->item(0);
        $dM = '';
        if (!$Zr) {
            goto HJ;
        }
        $si = $Zr->lookupPrefix(self::XMLDSIGNS);
        if (empty($si)) {
            goto Uv;
        }
        $dM = $si . "\72";
        Uv:
        goto wb;
        HJ:
        $si = $OK->lookupPrefix(self::XMLDSIGNS);
        if (empty($si)) {
            goto cB;
        }
        $dM = $si . "\x3a";
        cB:
        $bz = false;
        $Zr = $sI->createElementNS(self::XMLDSIGNS, $dM . "\x4b\x65\171\x49\156\146\157");
        $lB = "\56\57\x73\145\143\x64\x73\151\147\72\x4f\x62\152\x65\143\164";
        $ET = $wC->query($lB, $OK);
        if (!($wr = $ET->item(0))) {
            goto eK;
        }
        $wr->parentNode->insertBefore($Zr, $wr);
        $bz = true;
        eK:
        if ($bz) {
            goto yR;
        }
        $OK->appendChild($Zr);
        yR:
        wb:
        $OQ = self::staticGet509XCerts($Ix, $im);
        $uG = $sI->createElementNS(self::XMLDSIGNS, $dM . "\130\65\x30\71\104\x61\x74\141");
        $Zr->appendChild($uG);
        $ki = false;
        $s8 = false;
        if (!is_array($m1)) {
            goto vl;
        }
        if (empty($m1["\x69\163\163\165\145\x72\123\145\x72\151\141\154"])) {
            goto BE;
        }
        $ki = true;
        BE:
        if (empty($m1["\163\165\x62\x6a\145\x63\x74\x4e\141\155\x65"])) {
            goto wv;
        }
        $s8 = true;
        wv:
        vl:
        foreach ($OQ as $Na) {
            if (!($ki || $s8)) {
                goto WK;
            }
            if (!($F7 = openssl_x509_parse("\55\55\x2d\55\x2d\102\x45\107\x49\116\x20\x43\x45\122\x54\111\x46\x49\103\101\x54\105\55\55\55\55\55\12" . chunk_split($Na, 64, "\xa") . "\x2d\x2d\55\x2d\x2d\105\116\x44\40\103\x45\x52\124\111\x46\x49\103\101\124\105\55\55\55\x2d\x2d\12"))) {
                goto Pm;
            }
            if (!($s8 && !empty($F7["\163\x75\142\152\145\x63\x74"]))) {
                goto Kr;
            }
            if (is_array($F7["\163\x75\142\152\145\143\164"])) {
                goto Bn;
            }
            $mx = $F7["\163\165\x62\152\x65\143\x74"];
            goto J0;
            Bn:
            $Qx = array();
            foreach ($F7["\x73\165\142\x6a\145\x63\x74"] as $xO => $l7) {
                if (is_array($l7)) {
                    goto X3;
                }
                array_unshift($Qx, "{$xO}\x3d{$l7}");
                goto t1;
                X3:
                foreach ($l7 as $aN) {
                    array_unshift($Qx, "{$xO}\75{$aN}");
                    vI:
                }
                XY:
                t1:
                R2:
            }
            xM:
            $mx = implode("\x2c", $Qx);
            J0:
            $fZ = $sI->createElementNS(self::XMLDSIGNS, $dM . "\130\65\60\71\123\x75\142\x6a\x65\143\x74\116\141\x6d\145", $mx);
            $uG->appendChild($fZ);
            Kr:
            if (!($ki && !empty($F7["\x69\163\x73\x75\x65\x72"]) && !empty($F7["\x73\145\162\x69\141\154\116\165\x6d\x62\x65\162"]))) {
                goto CC;
            }
            if (is_array($F7["\x69\x73\x73\x75\145\x72"])) {
                goto K7;
            }
            $oA = $F7["\151\x73\x73\x75\145\162"];
            goto bl;
            K7:
            $Qx = array();
            foreach ($F7["\151\163\x73\165\145\x72"] as $xO => $l7) {
                array_unshift($Qx, "{$xO}\75{$l7}");
                lT:
            }
            jR:
            $oA = implode("\x2c", $Qx);
            bl:
            $kO = $sI->createElementNS(self::XMLDSIGNS, $dM . "\x58\x35\60\x39\x49\x73\x73\x75\145\162\x53\x65\x72\151\141\154");
            $uG->appendChild($kO);
            $ge = $sI->createElementNS(self::XMLDSIGNS, $dM . "\x58\65\x30\71\111\x73\x73\x75\145\162\x4e\x61\155\145", $oA);
            $kO->appendChild($ge);
            $ge = $sI->createElementNS(self::XMLDSIGNS, $dM . "\130\x35\x30\71\123\145\x72\151\x61\x6c\116\x75\155\x62\x65\x72", $F7["\x73\145\x72\x69\141\154\x4e\165\155\x62\145\162"]);
            $kO->appendChild($ge);
            CC:
            Pm:
            WK:
            $rP = $sI->createElementNS(self::XMLDSIGNS, $dM . "\x58\x35\x30\71\x43\x65\x72\164\x69\146\151\x63\x61\164\x65", $Na);
            $uG->appendChild($rP);
            mN:
        }
        T6:
    }
    public function add509Cert($Ix, $im = true, $oJ = false, $m1 = null)
    {
        if (!($wC = $this->getXPathObj())) {
            goto vb;
        }
        self::staticAdd509Cert($this->sigNode, $Ix, $im, $oJ, $wC, $m1);
        vb:
    }
    public function appendToKeyInfo($D_)
    {
        $OK = $this->sigNode;
        $sI = $OK->ownerDocument;
        $wC = $this->getXPathObj();
        if (!empty($wC)) {
            goto n6;
        }
        $wC = new DOMXPath($OK->ownerDocument);
        $wC->registerNamespace("\x73\x65\143\144\x73\x69\x67", self::XMLDSIGNS);
        n6:
        $lB = "\56\57\x73\145\143\x64\163\151\147\x3a\113\145\x79\111\156\146\157";
        $ET = $wC->query($lB, $OK);
        $Zr = $ET->item(0);
        if ($Zr) {
            goto AN;
        }
        $dM = '';
        $si = $OK->lookupPrefix(self::XMLDSIGNS);
        if (empty($si)) {
            goto E_;
        }
        $dM = $si . "\x3a";
        E_:
        $bz = false;
        $Zr = $sI->createElementNS(self::XMLDSIGNS, $dM . "\113\x65\171\x49\x6e\146\x6f");
        $lB = "\56\57\x73\x65\x63\144\x73\151\x67\72\x4f\x62\152\x65\143\164";
        $ET = $wC->query($lB, $OK);
        if (!($wr = $ET->item(0))) {
            goto Lm;
        }
        $wr->parentNode->insertBefore($Zr, $wr);
        $bz = true;
        Lm:
        if ($bz) {
            goto Dp;
        }
        $OK->appendChild($Zr);
        Dp:
        AN:
        $Zr->appendChild($D_);
        return $Zr;
    }
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
