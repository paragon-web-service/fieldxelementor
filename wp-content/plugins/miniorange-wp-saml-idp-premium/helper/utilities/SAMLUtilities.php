<?php


namespace IDP\Helper\Utilities;

use RobRichards\XMLSecLibs\XMLSecurityDsig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use IDP\Helper\Factory\ResponseDecisionHandler;
use IDP\Helper\Factory\RequestDecisionHandler;
use IDP\Helper\Constants\MoIDPConstants;
class SAMLUtilities
{
    public static function generateID()
    {
        return "\x5f" . self::stringToHex(self::generateRandomBytes(21));
    }
    public static function stringToHex($H8)
    {
        $uX = '';
        $rM = 0;
        uh:
        if (!($rM < strlen($H8))) {
            goto VD;
        }
        $uX .= sprintf("\45\60\62\x78", ord($H8[$rM]));
        Fg:
        $rM++;
        goto uh;
        VD:
        return $uX;
    }
    public static function generateRandomBytes($kd, $UE = TRUE)
    {
        return openssl_random_pseudo_bytes($kd);
    }
    public static function createLogoutRequest($Pb, $FW, $QB, $Ru, $gx = "\x48\x74\164\x70\122\145\144\151\162\145\x63\164")
    {
        $y0 = RequestDecisionHandler::getRequestHandler(MoIDPConstants::LOGOUT_REQUEST, $_REQUEST, $_GET, array($Pb, $FW, $QB, $Ru));
        $lo = $y0->generateRequest();
        if (!MSI_DEBUG) {
            goto vL;
        }
        MoIDPUtility::mo_debug("\123\x41\x4d\x4c\40\x4c\157\147\x6f\x75\x74\x20\122\145\161\165\x65\x73\164\40\x67\145\156\145\x72\x61\x74\x65\x64\x3a\40" . $lo);
        vL:
        if (!(empty($gx) || $gx == "\x48\x74\164\160\x52\x65\144\x69\162\145\143\164")) {
            goto Tj;
        }
        $Wv = gzdeflate($lo);
        $MN = base64_encode($Wv);
        $R_ = urlencode($MN);
        $lo = $R_;
        Tj:
        return $lo;
    }
    public static function createLogoutResponse($HB, $QB, $Ru, $gx = "\110\x74\164\160\122\x65\144\x69\162\145\x63\x74")
    {
        $wG = ResponseDecisionHandler::getResponseHandler(MoIDPConstants::LOGOUT_RESPONSE, array($HB, $QB, $Ru));
        $IA = $wG->generateResponse();
        if (!MSI_DEBUG) {
            goto co;
        }
        MoIDPUtility::mo_debug("\x53\x41\115\114\x20\x4c\157\147\157\165\x74\x20\122\x65\163\160\x6f\x6e\163\145\40\x67\145\x6e\x65\x72\x61\x74\145\144\72\x20" . $IA);
        co:
        if (!(empty($gx) || $gx == "\110\164\164\x70\122\145\144\151\162\145\143\x74")) {
            goto SX;
        }
        $Wv = gzdeflate($IA);
        $MN = base64_encode($Wv);
        $R_ = urlencode($MN);
        $IA = $R_;
        SX:
        return $IA;
    }
    public static function generateTimestamp($Y2 = NULL)
    {
        if (!($Y2 === NULL)) {
            goto wO;
        }
        $Y2 = time();
        wO:
        return gmdate("\131\55\x6d\55\144\134\x54\x48\72\x69\72\163\134\x5a", $Y2);
    }
    public static function xpQuery(\DomNode $D_, $lB)
    {
        static $Nu = NULL;
        if ($D_ instanceof \DOMDocument) {
            goto pf;
        }
        $oy = $D_->ownerDocument;
        goto CN;
        pf:
        $oy = $D_;
        CN:
        if (!($Nu === NULL || !$Nu->document->isSameNode($oy))) {
            goto wa;
        }
        $Nu = new \DOMXPath($oy);
        $Nu->registerNamespace("\163\x6f\141\x70\x2d\x65\156\x76", "\150\x74\164\x70\x3a\57\x2f\163\x63\150\x65\155\x61\x73\x2e\170\155\x6c\x73\157\141\160\56\157\x72\147\57\x73\x6f\141\x70\57\x65\156\166\x65\x6c\x6f\x70\145\57");
        $Nu->registerNamespace("\x73\x61\x6d\x6c\x5f\x70\162\x6f\164\x6f\x63\x6f\154", "\165\162\x6e\x3a\x6f\141\163\151\x73\72\156\141\155\x65\x73\x3a\x74\143\x3a\123\101\115\114\x3a\x32\56\x30\72\x70\162\x6f\164\157\x63\x6f\154");
        $Nu->registerNamespace("\163\x61\155\x6c\137\x61\163\x73\145\162\x74\151\x6f\x6e", "\165\162\156\x3a\x6f\x61\x73\x69\163\72\x6e\141\x6d\x65\163\x3a\164\143\72\x53\x41\115\x4c\x3a\x32\x2e\60\x3a\141\x73\163\x65\162\x74\x69\157\x6e");
        $Nu->registerNamespace("\163\141\155\154\x5f\x6d\145\x74\141\x64\141\x74\x61", "\165\x72\156\72\157\x61\x73\x69\163\x3a\x6e\141\155\145\163\72\164\143\x3a\123\101\x4d\x4c\72\x32\x2e\60\x3a\155\x65\x74\x61\x64\x61\164\x61");
        $Nu->registerNamespace("\x64\163", "\150\164\x74\160\72\57\x2f\167\167\167\56\167\63\56\157\x72\147\57\x32\60\x30\x30\57\x30\x39\x2f\x78\155\x6c\x64\x73\151\147\x23");
        $Nu->registerNamespace("\x78\x65\156\x63", "\150\164\x74\x70\x3a\57\x2f\167\167\x77\x2e\x77\x33\56\157\x72\147\57\62\x30\60\x31\x2f\60\x34\x2f\170\x6d\154\x65\156\x63\x23");
        wa:
        $E7 = $Nu->query($lB, $D_);
        $uX = array();
        $rM = 0;
        rL:
        if (!($rM < $E7->length)) {
            goto cA;
        }
        $uX[$rM] = $E7->item($rM);
        qK:
        $rM++;
        goto rL;
        cA:
        return $uX;
    }
    public static function parseNameId(\DOMElement $eL)
    {
        $uX = array("\126\x61\154\x75\x65" => trim($eL->textContent));
        foreach (array("\x4e\x61\155\x65\121\x75\141\154\151\x66\x69\145\x72", "\123\x50\116\x61\155\145\121\x75\141\x6c\151\x66\151\x65\x72", "\x46\157\x72\155\x61\x74") as $Hw) {
            if (!$eL->hasAttribute($Hw)) {
                goto je;
            }
            $uX[$Hw] = $eL->getAttribute($Hw);
            je:
            bx:
        }
        RS:
        return $uX;
    }
    public static function xsDateTimeToTimestamp($bL)
    {
        $BT = array();
        $Z4 = "\x2f\x5e\50\x5c\144\x5c\x64\134\x64\x5c\x64\x29\55\50\x5c\144\x5c\x64\x29\55\x28\x5c\x64\134\144\x29\124\x28\134\x64\134\x64\51\72\50\x5c\x64\x5c\144\51\72\50\x5c\x64\x5c\144\51\x28\77\72\134\56\x5c\x64\53\x29\x3f\132\44\x2f\x44";
        if (!(preg_match($Z4, $bL, $BT) == 0)) {
            goto SZ;
        }
        echo sprintf("\x49\x6e\x76\x61\154\x69\144\x20\x53\101\115\x4c\x32\40\x74\151\x6d\145\x73\x74\141\x6d\x70\40\x70\141\x73\163\x65\144\40\164\x6f\40\170\x73\104\x61\x74\145\124\151\x6d\x65\x54\157\x54\x69\155\x65\x73\x74\x61\155\160\x3a\x20" . $bL);
        exit;
        SZ:
        $rX = intval($BT[1]);
        $om = intval($BT[2]);
        $WP = intval($BT[3]);
        $PV = intval($BT[4]);
        $zx = intval($BT[5]);
        $Lf = intval($BT[6]);
        $Pc = gmmktime($PV, $zx, $Lf, $om, $WP, $rX);
        return $Pc;
    }
    public static function extractStrings(\DOMElement $EB, $hA, $kr)
    {
        $uX = array();
        $D_ = $EB->firstChild;
        f0:
        if (!($D_ !== NULL)) {
            goto oO;
        }
        if (!($D_->namespaceURI !== $hA || $D_->localName !== $kr)) {
            goto jn;
        }
        goto Q4;
        jn:
        $uX[] = trim($D_->textContent);
        Q4:
        $D_ = $D_->nextSibling;
        goto f0;
        oO:
        return $uX;
    }
    public static function validateElement(\DOMElement $u2)
    {
        $JV = new XMLSecurityDSig();
        $JV->idKeys[] = "\111\104";
        $xB = self::xpQuery($u2, "\x2e\x2f\144\x73\x3a\123\151\147\x6e\141\x74\x75\x72\x65");
        if (count($xB) === 0) {
            goto ay;
        }
        if (count($xB) > 1) {
            goto l2;
        }
        goto hj;
        ay:
        return FALSE;
        goto hj;
        l2:
        echo sprintf("\130\115\x4c\x53\145\x63\72\x20\155\x6f\162\x65\40\x74\x68\141\156\x20\157\156\145\x20\x73\x69\x67\156\141\164\165\162\145\40\x65\x6c\x65\x6d\145\x6e\x74\x20\x69\156\x20\162\x6f\157\164\x2e");
        exit;
        hj:
        $xB = $xB[0];
        $JV->sigNode = $xB;
        $JV->canonicalizeSignedInfo();
        if ($JV->validateReference()) {
            goto RC;
        }
        echo sprintf("\x58\115\114\163\145\143\72\x20\x64\151\x67\145\163\x74\40\166\141\154\x69\x64\x61\164\151\x6f\156\40\146\x61\x69\154\145\144");
        exit;
        RC:
        $Az = FALSE;
        foreach ($JV->getValidatedNodes() as $WF) {
            if ($WF->isSameNode($u2)) {
                goto Qy;
            }
            if ($u2->parentNode instanceof \DOMDocument && $WF->isSameNode($u2->ownerDocument)) {
                goto B8;
            }
            goto aX;
            Qy:
            $Az = TRUE;
            goto dH;
            goto aX;
            B8:
            $Az = TRUE;
            goto dH;
            aX:
            LF:
        }
        dH:
        if ($Az) {
            goto Hs;
        }
        echo sprintf("\x58\x4d\x4c\123\x65\143\72\40\x54\150\145\40\162\157\157\164\40\x65\x6c\145\155\145\156\x74\x20\151\x73\40\156\157\164\x20\x73\x69\147\x6e\x65\144\56");
        exit;
        Hs:
        $rq = array();
        foreach (self::xpQuery($xB, "\56\x2f\x64\x73\x3a\x4b\145\171\111\156\x66\x6f\x2f\x64\x73\x3a\130\x35\x30\71\104\141\x74\141\x2f\x64\x73\x3a\130\x35\x30\x39\103\x65\x72\x74\151\x66\x69\x63\x61\x74\145") as $J6) {
            $F7 = trim($J6->textContent);
            $F7 = str_replace(array("\xd", "\12", "\11", "\40"), '', $F7);
            $rq[] = $F7;
            NK:
        }
        de:
        $uX = array("\x53\x69\147\156\x61\164\x75\162\x65" => $JV, "\x43\145\x72\164\151\146\151\143\141\164\x65\163" => $rq);
        return $uX;
    }
    public static function validateSignature(array $VM, XMLSecurityKey $xO)
    {
        $JV = $VM["\x53\x69\147\x6e\141\x74\x75\x72\x65"];
        $BI = self::xpQuery($JV->sigNode, "\56\x2f\x64\163\72\x53\x69\x67\156\145\144\111\x6e\x66\157\57\144\163\x3a\x53\151\x67\x6e\x61\164\x75\162\x65\115\145\x74\150\157\144");
        if (!empty($BI)) {
            goto lv;
        }
        echo sprintf("\x4d\151\163\x73\151\156\147\40\123\x69\x67\x6e\x61\x74\165\x72\145\115\145\x74\x68\x6f\144\40\145\x6c\145\155\145\156\164");
        exit;
        lv:
        $BI = $BI[0];
        if ($BI->hasAttribute("\x41\x6c\x67\157\x72\151\164\x68\x6d")) {
            goto iK;
        }
        echo sprintf("\x4d\x69\x73\x73\151\156\147\x20\x41\154\x67\157\x72\x69\164\x68\x6d\x2d\141\x74\164\162\x69\142\x75\x74\x65\40\157\156\x20\x53\x69\x67\156\141\164\x75\x72\145\115\x65\x74\150\157\144\x20\145\x6c\145\x6d\145\x6e\164\56");
        exit;
        iK:
        $B5 = $BI->getAttribute("\101\154\147\157\162\x69\x74\x68\x6d");
        if (!($xO->type === XMLSecurityKey::RSA_SHA256 && $B5 !== $xO->type)) {
            goto Yi;
        }
        $xO = self::castKey($xO, $B5);
        Yi:
        if ($JV->verify($xO)) {
            goto yL;
        }
        echo sprintf("\125\156\141\142\154\145\40\x74\x6f\40\166\141\x6c\x69\x64\141\x74\x65\40\123\x67\x6e\x61\164\165\162\145");
        exit;
        yL:
    }
    public static function castKey(XMLSecurityKey $xO, $ib, $ZV = "\x70\x75\x62\x6c\151\143")
    {
        if (!($xO->type === $ib)) {
            goto Ss;
        }
        return $xO;
        Ss:
        $Zr = openssl_pkey_get_details($xO->key);
        if (!($Zr === FALSE)) {
            goto O4;
        }
        echo sprintf("\125\x6e\141\x62\x6c\145\x20\164\157\x20\147\x65\164\x20\x6b\x65\171\40\x64\x65\x74\x61\151\x6c\x73\40\146\x72\x6f\155\x20\x58\115\114\123\x65\x63\165\162\151\164\171\x4b\x65\171\56");
        exit;
        O4:
        if (isset($Zr["\153\x65\171"])) {
            goto LB;
        }
        echo sprintf("\x4d\x69\163\163\x69\x6e\147\x20\x6b\145\171\x20\x69\x6e\40\x70\165\x62\x6c\x69\143\40\153\145\171\x20\144\145\x74\141\x69\x6c\x73\x2e");
        exit;
        LB:
        $kW = new XMLSecurityKey($ib, array("\x74\x79\x70\145" => $ZV));
        $kW->loadKey($Zr["\153\x65\171"]);
        return $kW;
    }
    public static function processRequest($Er, $eh)
    {
        $Qh = self::checkSign($Er, $eh);
        return $Qh;
    }
    public static function checkSign($Er, $eh)
    {
        $rq = $eh["\x43\x65\162\x74\151\146\x69\x63\x61\164\x65\163"];
        if (!(count($rq) === 0)) {
            goto CM;
        }
        return FALSE;
        CM:
        $tA = array();
        $tA[] = $Er;
        $YQ = self::findCertificate($tA, $rq);
        $dp = NULL;
        $xO = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\x74\171\160\x65" => "\160\165\142\154\x69\143"));
        $xO->loadKey($YQ);
        try {
            self::validateSignature($eh, $xO);
            return TRUE;
        } catch (Exception $S5) {
            $dp = $S5;
        }
        if ($dp !== NULL) {
            goto jx;
        }
        return FALSE;
        goto J5;
        jx:
        throw $dp;
        J5:
    }
    private static function findCertificate(array $QY, array $rq)
    {
        $TG = array();
        foreach ($rq as $Ix) {
            $J9 = strtolower(sha1(base64_decode($Ix)));
            if (in_array($J9, $QY, TRUE)) {
                goto EG;
            }
            $TG[] = $J9;
            goto TU;
            EG:
            $yt = "\x2d\x2d\x2d\55\x2d\102\x45\107\111\x4e\40\x43\105\122\124\111\x46\x49\x43\101\124\105\55\55\x2d\55\x2d\xa" . chunk_split($Ix, 64) . "\x2d\x2d\x2d\x2d\55\x45\116\104\40\x43\x45\x52\124\111\x46\x49\x43\x41\x54\105\x2d\x2d\55\x2d\x2d\xa";
            return $yt;
            TU:
        }
        CS:
        echo sprintf("\x55\156\x61\142\154\145\x20\x74\157\x20\146\x69\156\144\40\x61\40\x63\145\162\x74\151\x66\151\x63\x61\x74\x65\x20\155\141\164\143\150\151\156\147\40\164\150\145\40\x63\x6f\156\146\151\x67\165\162\145\x64\40\146\x69\156\x67\x65\x72\160\162\x69\156\164\x2e");
        exit;
    }
    public static function parseBoolean(\DOMElement $D_, $K8, $nY = null)
    {
        if ($D_->hasAttribute($K8)) {
            goto w0;
        }
        return $nY;
        w0:
        $l7 = $D_->getAttribute($K8);
        switch (strtolower($l7)) {
            case "\60":
            case "\x66\141\154\x73\145":
                return false;
            case "\x31":
            case "\164\162\x75\145":
                return true;
            default:
                throw new \Exception("\111\x6e\x76\141\x6c\x69\144\x20\166\x61\154\165\x65\x20\157\x66\x20\x62\157\x6f\154\145\141\x6e\40\141\164\164\162\151\x62\x75\164\145\40" . var_export($K8, true) . "\72\x20" . var_export($l7, true));
        }
        yh:
        Uw:
    }
    public static function insertSignature(XMLSecurityKey $xO, array $rq, \DOMElement $u2, \DomNode $De = NULL)
    {
        $JV = new XMLSecurityDSig();
        $JV->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        switch ($xO->type) {
            case XMLSecurityKey::RSA_SHA256:
                $ZV = XMLSecurityDSig::SHA256;
                goto N0;
            case XMLSecurityKey::RSA_SHA384:
                $ZV = XMLSecurityDSig::SHA384;
                goto N0;
            case XMLSecurityKey::RSA_SHA512:
                $ZV = XMLSecurityDSig::SHA512;
                goto N0;
            default:
                $ZV = XMLSecurityDSig::SHA1;
        }
        ah:
        N0:
        $JV->addReferenceList(array($u2), $ZV, array("\x68\x74\x74\x70\72\x2f\57\167\x77\167\56\x77\x33\56\157\x72\x67\x2f\x32\x30\x30\x30\57\x30\x39\x2f\170\155\x6c\144\x73\151\147\x23\145\x6e\x76\x65\154\157\160\145\x64\x2d\163\151\x67\x6e\141\x74\x75\162\x65", XMLSecurityDSig::EXC_C14N), array("\x69\x64\x5f\156\x61\x6d\x65" => "\x49\x44", "\157\166\x65\x72\167\162\x69\x74\x65" => FALSE));
        $JV->sign($xO);
        foreach ($rq as $Ln) {
            $JV->add509Cert($Ln, TRUE);
            Un:
        }
        fm:
        $JV->insertSignature($u2, $De);
    }
    public static function signXML($eL, $NT, $KL, $z5 = '')
    {
        $Bj = array("\x74\x79\x70\145" => "\x70\x72\x69\x76\x61\x74\145");
        $xO = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $Bj);
        $xO->loadKey($KL, TRUE);
        $WW = file_get_contents($NT);
        $i2 = new \DOMDocument();
        $i2->loadXML($eL);
        $sU = $i2->firstChild;
        if (!empty($z5)) {
            goto HK;
        }
        self::insertSignature($xO, array($WW), $sU);
        goto SR;
        HK:
        $h3 = $i2->getElementsByTagName($z5)->item(0);
        self::insertSignature($xO, array($WW), $sU, $h3);
        SR:
        $dJ = $sU->ownerDocument->saveXML($sU);
        if (!MSI_DEBUG) {
            goto zU;
        }
        MoIDPUtility::mo_debug("\114\x6f\147\x6f\165\x74\40\122\x65\x73\160\x6f\156\163\x65\x20\107\x65\x6e\145\x72\141\164\145\144\72" . $dJ);
        zU:
        return $dJ;
    }
    public static function getEncryptionAlgorithm($qK)
    {
        switch ($qK) {
            case "\150\164\164\x70\x3a\x2f\x2f\167\167\x77\x2e\x77\63\x2e\x6f\162\147\x2f\x32\60\x30\x31\57\x30\x34\x2f\170\155\154\145\156\x63\43\164\162\151\x70\154\145\x64\x65\x73\x2d\x63\x62\143":
                return XMLSecurityKey::TRIPLEDES_CBC;
                goto iJ;
            case "\x68\x74\x74\160\72\57\57\x77\x77\167\x2e\x77\63\x2e\x6f\x72\x67\x2f\62\60\60\61\x2f\60\64\57\x78\x6d\x6c\145\x6e\143\x23\141\145\163\x31\62\x38\x2d\143\x62\x63":
                return XMLSecurityKey::AES128_CBC;
            case "\150\x74\x74\x70\72\57\57\x77\167\x77\56\167\x33\x2e\157\162\147\57\x32\x30\x30\61\x2f\60\x34\57\x78\155\x6c\x65\156\143\x23\x61\145\163\61\x39\62\x2d\143\x62\x63":
                return XMLSecurityKey::AES192_CBC;
                goto iJ;
            case "\150\x74\164\160\72\57\x2f\x77\x77\x77\56\x77\x33\x2e\x6f\x72\147\57\x32\60\60\61\57\60\x34\57\170\155\x6c\145\x6e\x63\x23\x61\145\x73\x32\x35\66\55\x63\x62\143":
                return XMLSecurityKey::AES256_CBC;
                goto iJ;
            case "\x68\x74\x74\x70\x3a\x2f\x2f\167\167\x77\x2e\x77\x33\x2e\157\162\x67\57\62\x30\x30\61\57\x30\64\57\x78\155\x6c\x65\156\x63\43\x72\163\x61\55\61\137\65":
                return XMLSecurityKey::RSA_1_5;
                goto iJ;
            case "\x68\164\164\160\x3a\x2f\x2f\167\167\x77\x2e\167\63\56\x6f\x72\x67\57\x32\x30\x30\61\x2f\60\x34\57\170\x6d\x6c\x65\x6e\x63\x23\162\x73\x61\x2d\157\x61\145\x70\x2d\x6d\147\146\x31\x70":
                return XMLSecurityKey::RSA_OAEP_MGF1P;
                goto iJ;
            case "\150\x74\164\160\72\x2f\x2f\167\x77\x77\56\x77\x33\56\x6f\x72\147\x2f\x32\x30\x30\x30\57\x30\x39\x2f\170\155\x6c\x64\x73\x69\x67\43\x64\163\141\x2d\163\x68\141\x31":
                return XMLSecurityKey::DSA_SHA1;
                goto iJ;
            case "\150\164\x74\160\x3a\57\57\167\x77\167\x2e\167\63\x2e\157\162\x67\57\62\x30\60\x30\57\x30\x39\x2f\x78\155\154\144\163\x69\147\43\x72\x73\141\55\163\x68\x61\61":
                return XMLSecurityKey::RSA_SHA1;
                goto iJ;
            case "\150\x74\x74\160\72\x2f\57\x77\x77\167\56\167\63\56\x6f\x72\147\57\62\60\x30\61\x2f\x30\64\57\x78\x6d\x6c\144\x73\151\x67\x2d\x6d\x6f\x72\x65\x23\162\163\x61\x2d\163\150\x61\62\x35\x36":
                return XMLSecurityKey::RSA_SHA256;
                goto iJ;
            case "\x68\x74\x74\x70\x3a\x2f\57\x77\x77\x77\56\x77\x33\56\x6f\162\147\57\x32\60\60\61\x2f\x30\64\x2f\170\x6d\154\144\x73\x69\147\x2d\x6d\157\162\145\43\162\x73\x61\x2d\163\x68\141\63\70\x34":
                return XMLSecurityKey::RSA_SHA384;
                goto iJ;
            case "\150\x74\164\x70\72\x2f\57\167\x77\x77\x2e\x77\63\x2e\x6f\x72\147\x2f\62\x30\x30\x31\57\x30\64\x2f\170\155\154\x64\163\x69\x67\x2d\155\157\162\145\x23\x72\163\x61\x2d\163\x68\x61\65\61\62":
                return XMLSecurityKey::RSA_SHA512;
                goto iJ;
            default:
                echo sprintf("\111\x6e\166\x61\x6c\151\144\x20\105\x6e\x63\162\x79\160\x74\151\157\x6e\x20\x4d\x65\164\150\157\x64\72\x20" . $qK);
                exit;
                goto iJ;
        }
        lY:
        iJ:
    }
    public static function sanitize_certificate($Ln)
    {
        $Ln = preg_replace("\57\133\15\xa\135\x2b\57", '', $Ln);
        $Ln = str_replace("\55", '', $Ln);
        $Ln = str_replace("\x42\105\107\111\116\40\103\x45\122\x54\111\x46\111\103\x41\124\105", '', $Ln);
        $Ln = str_replace("\x45\x4e\x44\40\x43\105\x52\124\x49\106\x49\103\x41\x54\x45", '', $Ln);
        $Ln = str_replace("\40", '', $Ln);
        $Ln = chunk_split($Ln, 64, "\xd\xa");
        $Ln = "\x2d\x2d\x2d\55\55\102\105\x47\x49\x4e\40\x43\x45\122\124\x49\106\x49\103\x41\x54\x45\55\x2d\x2d\x2d\x2d\xd\xa" . $Ln . "\55\x2d\x2d\55\55\105\x4e\x44\40\x43\x45\x52\124\111\106\x49\103\101\x54\105\x2d\x2d\x2d\55\x2d";
        return $Ln;
    }
    public static function desanitize_certificate($Ln)
    {
        $Ln = preg_replace("\57\133\15\xa\135\x2b\x2f", '', $Ln);
        $Ln = str_replace("\x2d\55\55\55\x2d\102\105\x47\x49\116\40\x43\105\122\x54\x49\106\x49\x43\101\124\x45\x2d\55\55\55\x2d", '', $Ln);
        $Ln = str_replace("\x2d\x2d\x2d\x2d\55\x45\116\104\40\103\x45\x52\x54\111\106\x49\x43\101\124\105\55\x2d\x2d\x2d\x2d", '', $Ln);
        $Ln = str_replace("\x20", '', $Ln);
        return $Ln;
    }
}
