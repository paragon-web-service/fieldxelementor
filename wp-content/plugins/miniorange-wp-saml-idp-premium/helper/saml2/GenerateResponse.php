<?php


namespace IDP\Helper\SAML2;

use IDP\Exception\InvalidSSOUserException;
use IDP\Helper\Utilities\MoIDPUtility;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use RobRichards\XMLSecLibs\XMLSecEnc;
use IDP\Helper\Factory\ResponseHandlerFactory;
class GenerateResponse implements ResponseHandlerFactory
{
    private $xml;
    private $acsUrl;
    private $issuer;
    private $audience;
    private $sp_attr;
    private $requestID;
    private $subject;
    private $mo_idp_assertion_signed;
    private $mo_idp_encrypted_assertion;
    private $mo_idp_response_signed;
    private $mo_idp_nameid_attr;
    private $mo_idp_nameid_format;
    private $mo_idp_cert_encrypt;
    private $login;
    private $current_user;
    private $sessionIndex;
    function __construct($Oy, $QB, $vj, $A2, $Ko, $di, $ur, $FW)
    {
        $this->xml = new \DOMDocument("\x31\x2e\x30", "\165\164\x66\55\x38");
        $this->acsUrl = $Oy;
        $this->issuer = $QB;
        $this->audience = $vj;
        $this->requestID = $A2;
        $this->login = $ur;
        $this->sp_attr = $Ko;
        $this->mo_idp_nameid_format = $di->mo_idp_nameid_format;
        $this->mo_idp_assertion_signed = $di->mo_idp_assertion_signed;
        $this->mo_idp_encrypted_assertion = $di->mo_idp_encrypted_assertion;
        $this->mo_idp_response_signed = $di->mo_idp_response_signed;
        $this->mo_idp_nameid_attr = $di->mo_idp_nameid_attr;
        $this->mo_idp_cert_encrypt = $di->mo_idp_cert_encrypt;
        $this->current_user = is_null($this->login) ? wp_get_current_user() : get_user_by("\154\x6f\x67\151\x6e", $this->login);
        $this->sessionIndex = $FW;
    }
    function generateResponse()
    {
        if (!MoIDPUtility::isBlank($this->current_user)) {
            goto ib;
        }
        throw new InvalidSSOUserException();
        ib:
        $et = $this->getResponseParams();
        $Fg = $this->createResponseElement($et);
        $this->xml->appendChild($Fg);
        $QB = $this->buildIssuer();
        $Fg->appendChild($QB);
        $wv = $this->buildStatus();
        $Fg->appendChild($wv);
        $mb = $this->buildStatusCode();
        $wv->appendChild($mb);
        $pD = $this->buildAssertion($et);
        $Fg->appendChild($pD);
        if (!MSI_DEBUG) {
            goto QD;
        }
        MoIDPUtility::mo_debug("\125\156\x65\x63\x6e\143\162\x79\160\x74\145\144\x20\x61\156\x64\40\x75\x6e\163\x69\147\156\x65\144\40\123\x41\x4d\114\x20\x52\145\x73\x70\157\156\163\145\72\x20" . $this->xml->saveXML());
        QD:
        if (!$this->mo_idp_assertion_signed) {
            goto Yt;
        }
        $dl = MoIDPUtility::getPrivateKey();
        $this->signNode($dl, $pD, $this->subject, $et);
        Yt:
        if (!$this->mo_idp_encrypted_assertion) {
            goto f8;
        }
        $Q5 = $this->buildEncryptedAssertion($pD);
        $Fg->removeChild($pD);
        $Fg->appendChild($Q5);
        f8:
        if (!$this->mo_idp_response_signed) {
            goto Wk;
        }
        $dl = MoIDPUtility::getPrivateKey();
        $this->signNode($dl, $Fg, $wv, $et);
        Wk:
        $W5 = $this->xml->saveXML();
        return $W5;
    }
    function getResponseParams()
    {
        $et = array();
        $bL = time();
        $et["\111\x73\163\x75\145\x49\x6e\x73\164\141\x6e\164"] = str_replace("\x2b\60\60\x3a\60\x30", "\132", gmdate("\x63", $bL));
        $et["\x4e\157\164\x4f\156\117\162\x41\146\164\145\162"] = str_replace("\x2b\60\60\x3a\x30\60", "\132", gmdate("\143", $bL + 300));
        $et["\116\157\164\102\145\x66\x6f\162\x65"] = str_replace("\x2b\60\60\72\60\60", "\x5a", gmdate("\x63", $bL - 30));
        $et["\x41\x75\164\150\156\x49\156\x73\164\x61\156\164"] = str_replace("\53\60\x30\x3a\60\x30", "\132", gmdate("\x63", $bL - 120));
        $et["\123\145\x73\163\x69\x6f\156\116\x6f\164\117\x6e\x4f\x72\x41\146\164\x65\162"] = str_replace("\x2b\60\60\72\x30\60", "\x5a", gmdate("\x63", $bL + 3600 * 8));
        $et["\x49\104"] = $this->generateUniqueID(40);
        $et["\101\x73\x73\x65\162\164\111\104"] = $this->generateUniqueID(40);
        $et["\111\x73\163\x75\145\x72"] = $this->issuer;
        $ai = MoIDPUtility::getPublicCert();
        $td = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\x74\171\x70\x65" => "\x70\165\x62\154\x69\x63"));
        $td->loadKey($ai, FALSE, TRUE);
        $et["\170\x35\60\71"] = $td->getX509Certificate();
        return $et;
    }
    function createResponseElement($et)
    {
        $Fg = $this->xml->createElementNS("\165\x72\x6e\x3a\x6f\x61\163\x69\163\x3a\156\141\x6d\x65\x73\72\164\x63\x3a\123\x41\115\x4c\x3a\62\x2e\60\x3a\160\162\x6f\164\x6f\143\x6f\154", "\163\141\155\x6c\x70\x3a\122\x65\x73\x70\x6f\x6e\x73\x65");
        $Fg->setAttribute("\111\x44", $et["\111\104"]);
        $Fg->setAttribute("\126\145\162\x73\151\157\x6e", "\x32\56\60");
        $Fg->setAttribute("\111\163\163\x75\145\111\x6e\163\x74\x61\x6e\164", $et["\111\x73\163\x75\x65\111\x6e\x73\164\141\156\164"]);
        $Fg->setAttribute("\x44\x65\x73\x74\x69\x6e\141\x74\x69\x6f\156", $this->acsUrl);
        if (is_null($this->requestID)) {
            goto KW;
        }
        $Fg->setAttribute("\x49\156\x52\x65\163\x70\x6f\x6e\163\145\x54\157", $this->requestID);
        KW:
        return $Fg;
    }
    function buildIssuer()
    {
        $QB = $this->xml->createElementNS("\x75\x72\x6e\x3a\x6f\141\163\x69\x73\x3a\x6e\141\155\x65\163\72\x74\x63\x3a\123\x41\x4d\114\x3a\62\56\60\72\x61\x73\163\x65\x72\164\x69\x6f\x6e", "\163\141\155\154\x3a\x49\x73\x73\165\x65\162", $this->issuer);
        return $QB;
    }
    function buildStatus()
    {
        $wv = $this->xml->createElementNS("\165\162\156\72\x6f\141\x73\x69\x73\x3a\156\x61\155\145\163\x3a\164\x63\72\123\101\115\x4c\x3a\62\56\60\x3a\x70\162\x6f\x74\157\x63\x6f\x6c", "\163\x61\155\x6c\x70\72\x53\164\141\164\x75\x73");
        return $wv;
    }
    function buildStatusCode()
    {
        $mb = $this->xml->createElementNS("\165\x72\x6e\x3a\x6f\141\x73\151\163\72\x6e\141\x6d\145\x73\x3a\x74\143\72\x53\101\115\x4c\x3a\x32\56\x30\72\x70\x72\157\x74\157\x63\157\154", "\x73\x61\x6d\154\160\72\x53\x74\x61\164\165\163\103\x6f\x64\x65");
        $mb->setAttribute("\126\x61\154\165\x65", "\165\x72\x6e\x3a\157\x61\163\151\163\72\156\x61\155\x65\163\x3a\x74\x63\72\x53\x41\115\x4c\72\62\x2e\60\72\x73\164\141\164\165\x73\72\123\165\143\x63\145\163\163");
        return $mb;
    }
    function buildAssertion($et)
    {
        $pD = $this->xml->createElementNS("\x75\162\156\72\157\141\x73\x69\x73\x3a\156\x61\155\x65\x73\x3a\164\143\x3a\x53\101\115\114\x3a\x32\x2e\x30\x3a\141\x73\x73\145\162\164\x69\157\x6e", "\x73\141\155\154\72\x41\163\x73\145\162\164\x69\x6f\x6e");
        $pD->setAttribute("\111\104", $et["\x41\163\x73\x65\x72\x74\111\104"]);
        $pD->setAttribute("\111\163\x73\x75\x65\111\156\163\x74\x61\x6e\x74", $et["\111\x73\x73\165\x65\x49\156\x73\x74\141\156\x74"]);
        $pD->setAttribute("\x56\145\x72\x73\151\x6f\x6e", "\x32\x2e\x30");
        $QB = $this->buildIssuer();
        $pD->appendChild($QB);
        $x4 = $this->buildSubject($et);
        $this->subject = $x4;
        $pD->appendChild($x4);
        $gG = $this->buildCondition($et);
        $pD->appendChild($gG);
        $kG = $this->buildAuthnStatement($et);
        $pD->appendChild($kG);
        if (!(isset($this->sp_attr) && !empty($this->sp_attr))) {
            goto ed;
        }
        $gk = $this->buildAttrStatement();
        $pD->appendChild($gk);
        ed:
        return $pD;
    }
    function buildSubject($et)
    {
        $x4 = $this->xml->createElement("\x73\141\155\x6c\x3a\123\x75\x62\x6a\145\143\x74");
        $tn = $this->buildNameIdentifier();
        $x4->appendChild($tn);
        $xQ = $this->buildSubjectConfirmation($et);
        $x4->appendChild($xQ);
        return $x4;
    }
    function buildNameIdentifier()
    {
        $gt = !empty($this->mo_idp_nameid_attr) && $this->mo_idp_nameid_attr != "\145\x6d\141\x69\154\x41\144\144\162\145\163\x73" ? $this->mo_idp_nameid_attr : "\165\163\145\162\137\x65\155\141\151\x6c";
        $l7 = MoIDPUtility::isBlank($this->current_user->{$gt}) ? get_user_meta($this->current_user->ID, $gt, true) : $this->current_user->{$gt};
        $l7 = apply_filters("\x67\145\x6e\x65\x72\x61\164\145\x5f\x73\x61\x6d\x6c\x5f\x61\x74\x74\x72\151\142\x75\164\145\x5f\166\x61\154\x75\x65", $l7, $this->current_user, "\x4e\141\155\x65\x49\104", "\116\x61\155\x65\111\x44");
        $tn = $this->xml->createElement("\163\x61\155\x6c\72\116\x61\x6d\145\x49\104", htmlspecialchars($l7));
        $tn->setAttribute("\x46\x6f\162\x6d\141\164", "\x75\162\156\x3a\x6f\x61\x73\x69\163\72\156\x61\155\145\x73\72\x74\143\72\123\x41\x4d\114\x3a" . $this->mo_idp_nameid_format);
        return $tn;
    }
    function buildSubjectConfirmation($et)
    {
        $xQ = $this->xml->createElement("\x73\141\155\154\72\x53\x75\142\x6a\145\x63\x74\103\157\156\x66\x69\x72\x6d\141\x74\151\x6f\x6e");
        $xQ->setAttribute("\x4d\145\x74\x68\157\144", "\165\x72\156\x3a\x6f\141\163\151\x73\x3a\x6e\x61\x6d\145\x73\x3a\x74\143\72\123\101\115\x4c\72\x32\x2e\x30\72\143\155\72\x62\x65\141\162\x65\162");
        $ub = $this->getSubjectConfirmationData($et);
        $xQ->appendChild($ub);
        return $xQ;
    }
    function getSubjectConfirmationData($et)
    {
        $ub = $this->xml->createElement("\x73\141\155\x6c\72\x53\165\142\152\x65\143\164\x43\x6f\x6e\x66\151\x72\x6d\x61\164\x69\157\x6e\x44\141\164\141");
        $ub->setAttribute("\x4e\x6f\x74\x4f\x6e\117\x72\101\146\x74\x65\x72", $et["\116\x6f\164\117\156\x4f\x72\101\146\x74\145\162"]);
        $ub->setAttribute("\122\x65\143\x69\x70\151\145\156\164", $this->acsUrl);
        if (is_null($this->requestID)) {
            goto e4;
        }
        $ub->setAttribute("\x49\x6e\x52\x65\x73\x70\157\156\163\145\124\x6f", $this->requestID);
        e4:
        return $ub;
    }
    function buildCondition($et)
    {
        $gG = $this->xml->createElement("\x73\141\x6d\x6c\72\x43\x6f\156\x64\x69\x74\151\157\x6e\163");
        $gG->setAttribute("\x4e\x6f\164\102\x65\x66\x6f\x72\x65", $et["\x4e\157\164\102\145\x66\157\x72\x65"]);
        $gG->setAttribute("\x4e\157\164\117\x6e\117\162\x41\x66\164\x65\x72", $et["\116\x6f\164\x4f\156\117\x72\101\146\x74\x65\x72"]);
        $vj = $this->buildAudienceRestriction();
        $gG->appendChild($vj);
        return $gG;
    }
    function buildAudienceRestriction()
    {
        $m5 = $this->xml->createElement("\x73\141\x6d\x6c\x3a\x41\165\144\151\145\x6e\x63\145\x52\x65\x73\x74\162\x69\x63\164\x69\x6f\156");
        $vj = $this->xml->createElement("\163\x61\x6d\154\x3a\x41\x75\x64\151\145\156\x63\145", $this->audience);
        $m5->appendChild($vj);
        return $m5;
    }
    function buildAuthnStatement($et)
    {
        $kG = $this->xml->createElement("\163\141\155\x6c\72\x41\x75\x74\x68\156\x53\x74\141\164\145\155\x65\156\164");
        $kG->setAttribute("\x41\165\164\x68\x6e\111\156\x73\x74\141\x6e\164", $et["\101\165\x74\x68\156\111\156\163\164\141\156\164"]);
        $kG->setAttribute("\123\x65\x73\x73\151\157\156\111\x6e\x64\145\170", $this->sessionIndex);
        $kG->setAttribute("\123\x65\163\163\x69\157\156\116\157\164\x4f\156\117\x72\x41\146\x74\x65\x72", $et["\123\x65\x73\163\151\x6f\x6e\x4e\x6f\x74\x4f\x6e\117\162\101\146\x74\145\162"]);
        $I4 = $this->xml->createElement("\x73\141\x6d\154\72\101\x75\164\x68\156\103\x6f\156\x74\x65\170\x74");
        $dS = $this->xml->createElement("\x73\x61\155\x6c\x3a\101\165\x74\x68\x6e\103\157\x6e\164\x65\170\164\x43\x6c\141\x73\x73\x52\145\146", "\165\x72\x6e\72\x6f\141\x73\x69\x73\72\156\141\x6d\145\x73\x3a\164\143\x3a\123\x41\x4d\x4c\72\62\x2e\x30\x3a\141\x63\72\x63\154\x61\163\163\x65\163\72\x50\141\x73\163\x77\157\x72\144\x50\162\157\164\x65\143\164\145\144\x54\162\x61\156\x73\x70\157\162\164");
        $I4->appendChild($dS);
        $kG->appendChild($I4);
        return $kG;
    }
    function buildAttrStatement()
    {
        $gk = $this->xml->createElement("\x73\x61\155\x6c\72\x41\x74\x74\x72\x69\x62\x75\164\x65\123\x74\141\x74\x65\x6d\x65\x6e\x74");
        foreach ($this->sp_attr as $Hw) {
            $YK = $this->buildAttribute($Hw->mo_sp_attr_name, $Hw->mo_sp_attr_value, $Hw->mo_attr_type);
            if (is_null($YK)) {
                goto ME;
            }
            $gk->appendChild($YK);
            ME:
            aI:
        }
        ZG:
        return $gk;
    }
    function buildAttribute($wf, $wm, $ZV)
    {
        if ($wf === "\147\162\157\x75\x70\x4d\x61\160\116\141\155\145") {
            goto mr;
        }
        if ($ZV == 0) {
            goto ij;
        }
        if (!($ZV == 2)) {
            goto qW;
        }
        $l7 = $wm;
        qW:
        goto D2;
        ij:
        $l7 = $this->current_user->{$wm};
        if (!empty($l7)) {
            goto Nr;
        }
        $l7 = get_user_meta($this->current_user->ID, $wm, TRUE);
        Nr:
        D2:
        goto S6;
        mr:
        $wf = $wm;
        $l7 = $this->current_user->roles;
        S6:
        $l7 = apply_filters("\147\x65\156\x65\x72\141\x74\145\137\163\x61\155\154\x5f\141\164\x74\162\x69\x62\x75\164\x65\137\x76\x61\154\165\x65", $l7, $this->current_user, $wf, $wm);
        if (!empty($l7)) {
            goto eZ;
        }
        return null;
        eZ:
        return $this->createAttributeNode($l7, $wf);
    }
    function createAttributeNode($l7, $wf)
    {
        $YK = $this->xml->createElement("\x73\141\155\154\72\x41\164\x74\x72\x69\x62\x75\164\145");
        $YK->setAttribute("\x4e\141\155\145", $wf);
        $YK->setAttribute("\116\141\155\145\x46\x6f\162\x6d\x61\x74", "\x75\x72\156\72\x6f\141\163\151\x73\x3a\156\x61\x6d\x65\163\x3a\x74\143\72\x53\101\x4d\114\72\x32\56\60\x3a\x61\164\x74\162\156\x61\155\x65\x2d\x66\x6f\x72\x6d\x61\x74\x3a\x75\x6e\163\160\145\x63\x69\146\x69\x65\144");
        if (is_array($l7)) {
            goto zH;
        }
        $l7 = apply_filters("\155\157\x64\151\146\x79\x5f\x73\141\155\154\x5f\x61\x74\164\x72\x5f\166\141\x6c\165\x65", $l7);
        $zV = $this->xml->createElement("\x73\x61\155\154\x3a\x41\x74\164\162\x69\142\165\164\x65\126\141\154\x75\145", htmlspecialchars($l7));
        $YK->appendChild($zV);
        goto kN;
        zH:
        foreach ($l7 as $xO => $tL) {
            $tL = apply_filters("\155\157\x64\x69\x66\x79\x5f\x73\141\155\154\x5f\x61\x74\x74\162\137\166\x61\x6c\x75\x65", $tL);
            $zV = $this->xml->createElement("\163\141\x6d\x6c\72\x41\x74\164\x72\151\x62\165\164\145\x56\x61\x6c\x75\x65", htmlspecialchars($tL));
            $YK->appendChild($zV);
            WX:
        }
        vx:
        kN:
        return $YK;
    }
    function buildEncryptedAssertion($pD)
    {
        $Q5 = $this->xml->createElementNS("\165\162\156\x3a\x6f\141\x73\x69\x73\x3a\x6e\x61\x6d\145\163\x3a\x74\143\72\123\101\115\114\72\62\56\60\72\x61\x73\x73\145\x72\x74\151\x6f\x6e", "\x73\141\x6d\154\x70\x3a\105\x6e\x63\162\x79\160\164\145\144\101\x73\x73\x65\162\164\151\x6f\156");
        $ZU = $this->buildEncryptedData($pD);
        $Q5->appendChild($Q5->ownerDocument->importNode($ZU, TRUE));
        return $Q5;
    }
    function buildEncryptedData($pD)
    {
        $ZU = new XMLSecEnc();
        $ZU->setNode($pD);
        $ZU->type = "\x68\x74\x74\160\x3a\x2f\57\x77\x77\x77\56\x77\63\x2e\157\162\x67\x2f\62\x30\x30\x31\57\60\x34\57\x78\155\154\145\156\143\x23\105\x6c\x65\155\x65\156\x74";
        $EQ = $this->mo_idp_cert_encrypt;
        $sc = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\164\171\160\145" => "\160\165\x62\x6c\151\143"));
        $sc->loadKey($EQ, FALSE, TRUE);
        $cH = new XMLSecurityKey(XMLSecurityKey::AES256_CBC);
        $cH->generateSessionKey();
        $ZU->encryptKey($sc, $cH);
        $qv = $ZU->encryptNode($cH, FALSE);
        return $qv;
    }
    function signNode($dl, $D_, $x4, $et)
    {
        $td = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\164\x79\160\x65" => "\160\x72\151\x76\x61\x74\x65"));
        $td->loadKey($dl, FALSE);
        $JV = new XMLSecurityDSig();
        $JV->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $JV->addReferenceList(array($D_), XMLSecurityDSig::SHA256, array("\150\164\x74\x70\x3a\x2f\x2f\x77\167\x77\56\167\x33\56\157\x72\147\x2f\62\60\x30\60\57\60\71\57\170\155\x6c\x64\163\151\147\43\x65\x6e\x76\x65\154\157\x70\x65\x64\55\163\151\x67\156\x61\x74\x75\x72\x65", XMLSecurityDSig::EXC_C14N), array("\x69\144\x5f\x6e\x61\x6d\x65" => "\111\x44", "\157\x76\145\162\x77\162\x69\x74\x65" => false));
        $JV->sign($td);
        $JV->add509Cert($et["\x78\x35\x30\x39"]);
        $JV->insertSignature($D_, $x4);
    }
    function generateUniqueID($kd)
    {
        return MoIDPUtility::generateRandomAlphanumericValue($kd);
    }
}
