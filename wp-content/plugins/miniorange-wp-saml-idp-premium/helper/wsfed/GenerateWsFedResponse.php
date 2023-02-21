<?php


namespace IDP\Helper\WSFED;

use IDP\Helper\Utilities\MoIDPUtility;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use IDP\Exception\InvalidSSOUserException;
use IDP\Helper\Factory\ResponseHandlerFactory;
class GenerateWsFedResponse implements ResponseHandlerFactory
{
    private $xml;
    private $issuer;
    private $wtrealm;
    private $wa;
    private $wctx;
    private $subject;
    private $mo_idp_nameid_attr;
    private $mo_idp_nameid_format;
    private $currentUser;
    function __construct($Nv, $QS, $E2, $QB, $di, $Ko, $ur)
    {
        $this->xml = new \DOMDocument("\61\56\60", "\x75\x74\x66\55\x38");
        $this->xml->preserveWhiteSpace = false;
        $this->xml->formatOutput = false;
        $this->wctx = $E2;
        $this->issuer = $QB;
        $this->wtrealm = $Nv;
        $this->sp_attr = $Ko;
        $this->wa = $QS;
        $this->mo_idp_nameid_format = $di->mo_idp_nameid_format;
        $this->mo_idp_nameid_attr = $di->mo_idp_nameid_attr;
        $this->current_user = is_null($ur) ? wp_get_current_user() : get_user_by("\x6c\x6f\x67\x69\x6e", $ur);
    }
    function generateResponse()
    {
        if (!MoIDPUtility::isBlank($this->current_user)) {
            goto KK;
        }
        throw new InvalidSSOUserException();
        KK:
        $et = $this->getResponseParams();
        $Fg = $this->createResponseElement($et);
        $this->xml->appendChild($Fg);
        $dl = MoIDPUtility::getPrivateKey();
        $this->signNode($dl, $Fg->firstChild->nextSibling->nextSibling->firstChild, NULL, $et);
        $gd = $this->xml->saveXML();
        return $gd;
    }
    function getResponseParams()
    {
        $et = array();
        $bL = time();
        $et["\x49\x73\163\x75\x65\x49\x6e\163\x74\141\x6e\x74"] = str_replace("\x2b\x30\x30\72\60\x30", "\132", gmdate("\143", $bL));
        $et["\x4e\157\164\x4f\156\x4f\x72\x41\x66\x74\145\x72"] = str_replace("\x2b\60\x30\x3a\60\x30", "\x5a", gmdate("\x63", $bL + 300));
        $et["\x4e\x6f\x74\x42\145\146\x6f\x72\145"] = str_replace("\53\60\60\x3a\60\x30", "\x5a", gmdate("\x63", $bL - 30));
        $et["\101\x75\x74\x68\x6e\111\x6e\x73\x74\x61\156\x74"] = str_replace("\x2b\x30\60\x3a\60\x30", "\132", gmdate("\x63", $bL - 120));
        $et["\x41\163\163\x65\162\x74\111\104"] = $this->generateUniqueID(40);
        $ai = MoIDPUtility::getPublicCert();
        $td = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\164\x79\160\145" => "\160\x75\142\154\151\143"));
        $td->loadKey($ai, FALSE, TRUE);
        $et["\170\65\60\71"] = $td->getX509Certificate();
        return $et;
    }
    function generateUniqueID($kd)
    {
        return MoIDPUtility::generateRandomAlphanumericValue($kd);
    }
    function createResponseElement($et)
    {
        $Fg = $this->xml->createElementNS("\150\x74\164\x70\x3a\57\x2f\163\x63\x68\145\x6d\141\x73\x2e\x78\x6d\x6c\163\x6f\x61\160\x2e\x6f\162\x67\57\167\163\x2f\62\60\60\x35\x2f\x30\62\57\164\162\165\163\164", "\164\72\x52\x65\161\165\x65\x73\x74\x53\145\143\x75\x72\x69\164\171\x54\x6f\153\x65\156\122\145\163\x70\157\x6e\163\x65");
        $Xa = $this->createResponseElementLifetime($et);
        $Fg->appendChild($Xa);
        $hn = $this->createResponseElementAppliesTo($et);
        $Fg->appendChild($hn);
        $Is = $this->create_RequestedSecurityToken($et);
        $Fg->appendChild($Is);
        $pQ = $this->create_TokenType();
        $Fg->appendChild($pQ);
        $bk = $this->create_RequestType();
        $Fg->appendChild($bk);
        $VO = $this->create_KeyType();
        $Fg->appendChild($VO);
        return $Fg;
    }
    function create_RequestType()
    {
        $Fg = $this->xml->createElement("\164\x3a\124\x6f\153\145\x6e\x54\x79\x70\145", "\165\x72\156\x3a\157\x61\x73\151\163\x3a\156\141\x6d\145\x73\x3a\x74\x63\72\123\x41\x4d\x4c\x3a\61\56\x30\x3a\141\x73\x73\145\x72\x74\x69\157\156");
        return $Fg;
    }
    function create_KeyType()
    {
        $Fg = $this->xml->createElement("\x74\72\x4b\x65\x79\x54\x79\160\x65", "\x68\x74\164\x70\72\x2f\x2f\x73\143\x68\x65\x6d\x61\x73\56\170\155\x6c\163\157\141\x70\x2e\x6f\162\x67\x2f\x77\x73\57\x32\x30\x30\65\57\x30\x35\57\x69\x64\145\156\164\151\x74\171\x2f\116\x6f\x50\x72\x6f\157\146\113\145\x79");
        return $Fg;
    }
    function create_TokenType()
    {
        $Fg = $this->xml->createElement("\164\72\x52\x65\161\165\145\163\x74\124\171\160\145", "\150\164\x74\x70\72\x2f\x2f\x73\x63\x68\x65\x6d\141\163\56\170\x6d\x6c\163\157\141\x70\x2e\x6f\x72\x67\57\167\163\x2f\62\60\60\65\x2f\x30\62\57\164\x72\x75\163\164\57\111\x73\163\165\145");
        return $Fg;
    }
    function create_RequestedSecurityToken($et)
    {
        $Fg = $this->xml->createElement("\164\72\122\145\161\x75\x65\x73\x74\x65\x64\x53\145\x63\165\162\151\164\x79\124\x6f\x6b\x65\x6e");
        $Xa = $this->create_Assertion($et);
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function create_Assertion($et)
    {
        $pD = $this->xml->createElementNS("\165\162\156\72\x6f\x61\x73\151\x73\72\x6e\x61\155\145\163\x3a\x74\x63\x3a\x53\101\115\114\72\61\56\60\x3a\141\163\163\x65\162\164\x69\157\156", "\163\x61\x6d\154\72\101\x73\163\x65\x72\x74\151\157\156");
        $pD->setAttribute("\115\x61\152\x6f\x72\126\145\162\163\x69\157\x6e", "\61");
        $pD->setAttribute("\x4d\151\156\157\162\x56\x65\162\163\x69\157\156", "\x31");
        $pD->setAttribute("\101\x73\163\145\x72\x74\151\157\156\x49\x44", $et["\101\163\x73\145\162\x74\111\104"]);
        $pD->setAttribute("\111\163\163\x75\145\x72", $this->issuer);
        $pD->setAttribute("\x49\163\x73\165\x65\111\x6e\x73\x74\141\x6e\164", $et["\x49\x73\x73\x75\145\x49\x6e\163\164\141\156\164"]);
        $K3 = $this->createSamlConditions($et);
        $pD->appendChild($K3);
        if (!(isset($this->sp_attr) && !empty($this->sp_attr))) {
            goto u9;
        }
        $gk = $this->createAttributeStatement($et);
        $pD->appendChild($gk);
        u9:
        $cL = $this->createAuthenticationStatement($et);
        $pD->appendChild($cL);
        return $pD;
    }
    function signNode($dl, $D_, $x4, $et)
    {
        $td = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array("\164\x79\160\x65" => "\x70\162\151\x76\141\164\x65"));
        $td->loadKey($dl, FALSE);
        $JV = new XMLSecurityDSig();
        $JV->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $JV->addReferenceList(array($D_), XMLSecurityDSig::SHA256, array("\x68\x74\164\160\72\57\57\167\167\167\56\x77\63\x2e\157\162\x67\57\62\x30\x30\60\x2f\x30\71\57\170\x6d\x6c\x64\163\x69\x67\x23\x65\x6e\x76\145\x6c\157\160\x65\144\55\163\151\147\156\x61\164\x75\x72\145", XMLSecurityDSig::EXC_C14N), array("\151\144\137\x6e\141\x6d\145" => "\x41\163\x73\145\162\x74\151\157\156\111\x44", "\x6f\166\x65\162\167\x72\151\164\x65" => false));
        $JV->sign($td);
        $JV->add509Cert($et["\x78\65\60\x39"]);
        $JV->insertSignature($D_, NULL);
    }
    function creatSignedInfo()
    {
        $Fg = $this->xml->createElement("\144\x73\72\x53\x69\147\156\145\144");
        $Xa = $this->createCanonicalizationMethod();
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function createCanonicalizationMethod()
    {
        $Fg = $this->xml->createElement("\x64\163\72\x43\141\x6e\x6f\156\151\x63\141\154\151\x7a\141\164\x69\x6f\156\115\145\x74\x68\x6f\x64");
        $Fg->setAttribute("\x41\154\147\157\162\x69\x74\x68\155", "\150\x74\164\x70\x3a\57\57\x77\x77\167\56\x77\63\56\x6f\162\x67\57\x32\60\x30\x31\x2f\61\x30\57\170\155\x6c\x2d\145\170\x63\x2d\143\61\64\156\43");
        return $Fg;
    }
    function createAuthenticationStatement($et)
    {
        $Fg = $this->xml->createElement("\163\x61\155\x6c\72\x41\165\164\x68\x65\x6e\x74\151\x63\x61\x74\151\x6f\156\x53\x74\x61\164\145\x6d\x65\156\x74");
        $Fg->setAttribute("\101\165\x74\150\145\156\164\151\143\141\164\151\x6f\x6e\x4d\x65\164\150\157\144", "\165\x72\x6e\72\x6f\x61\x73\151\x73\x3a\156\x61\155\x65\163\72\164\x63\72\x53\101\115\x4c\72\x32\56\60\x3a\141\143\x3a\143\154\141\x73\x73\x65\x73\x3a\120\x61\x73\x73\x77\157\162\x64\x50\x72\x6f\164\145\x63\164\145\144\124\162\x61\x6e\163\x70\x6f\162\164");
        $Fg->setAttribute("\101\165\x74\150\x65\x6e\x74\151\143\141\x74\x69\157\156\x49\x6e\x73\164\141\x6e\x74", $et["\x41\x75\164\x68\156\x49\x6e\x73\164\141\x6e\164"]);
        $Xa = $this->createSubject();
        $this->subject = $Xa;
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function createAttributeStatement($et)
    {
        $gk = $this->xml->createElement("\x73\x61\155\x6c\x3a\101\x74\x74\162\x69\142\x75\164\145\x53\x74\x61\x74\145\x6d\145\156\164");
        $x4 = $this->createSubject();
        $this->subject = $x4;
        $gk->appendChild($x4);
        foreach ($this->sp_attr as $Hw) {
            $YK = $this->buildAttribute($et, $Hw->mo_sp_attr_name, $Hw->mo_sp_attr_value, $Hw->mo_attr_type);
            if (is_null($YK)) {
                goto xe;
            }
            $gk->appendChild($YK);
            xe:
            jw:
        }
        uA:
        return $gk;
    }
    function buildAttribute($et, $wf, $wm, $ZV)
    {
        if ($wf === "\147\x72\x6f\x75\x70\115\141\160\x4e\x61\155\x65") {
            goto WP;
        }
        if ($ZV == 0) {
            goto bO;
        }
        if (!($ZV == 2)) {
            goto xQ;
        }
        $l7 = $wm;
        xQ:
        goto I1;
        bO:
        $l7 = $this->current_user->{$wm};
        I1:
        goto oL;
        WP:
        $wf = $wm;
        $l7 = $this->current_user->roles;
        oL:
        if (!empty($l7)) {
            goto mM;
        }
        $l7 = get_user_meta($this->current_user->ID, $wm, TRUE);
        mM:
        $l7 = apply_filters("\147\145\x6e\x65\x72\141\x74\x65\x5f\167\x73\x66\x65\144\137\x61\164\164\x72\x69\142\x75\x74\145\x5f\x76\141\x6c\x75\x65", $l7, $this->current_user, $wf);
        if (!empty($l7)) {
            goto E4;
        }
        return null;
        E4:
        return $this->createAttributeNode($l7, $wf);
    }
    function createAttributeNode($l7, $wf)
    {
        $YK = $this->xml->createElement("\x73\x61\x6d\154\72\x41\164\x74\x72\x69\142\165\x74\145");
        $YK->setAttribute("\x41\x74\x74\x72\x69\x62\x75\164\145\116\141\155\x65", $wf);
        $YK->setAttribute("\101\164\164\162\x69\x62\x75\164\x65\116\x61\x6d\145\x73\160\141\143\145", "\x68\x74\164\160\x3a\x2f\x2f\163\143\150\145\155\141\x73\56\x78\155\x6c\163\x6f\x61\x70\56\x6f\x72\x67\57\143\154\x61\151\x6d\x73");
        if (is_array($l7)) {
            goto Bx;
        }
        $l7 = apply_filters("\155\x6f\x64\x69\x66\171\137\167\163\x66\145\x64\x5f\x61\164\x74\x72\x5f\166\x61\154\x75\x65", $l7);
        $zV = $this->xml->createElement("\163\x61\155\154\72\101\x74\164\162\x69\142\165\164\145\126\x61\154\165\145", htmlspecialchars($l7));
        $YK->appendChild($zV);
        goto K9;
        Bx:
        foreach ($l7 as $xO => $tL) {
            $tL = apply_filters("\x6d\x6f\144\151\146\171\x5f\x77\163\146\x65\x64\137\x61\x74\164\162\137\166\141\x6c\165\145", $tL);
            $zV = $this->xml->createElement("\163\x61\x6d\x6c\72\x41\x74\164\x72\x69\142\x75\164\145\126\x61\x6c\x75\x65", htmlspecialchars($tL));
            $YK->appendChild($zV);
            gm:
        }
        ox:
        K9:
        return $YK;
    }
    function createSubjectConfirmation()
    {
        $Fg = $this->xml->createElement("\163\x61\155\154\72\123\165\142\x6a\145\143\164\x43\157\x6e\146\x69\x72\155\141\164\x69\x6f\156");
        $Xa = $this->createConfirmationMethod();
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function createConfirmationMethod()
    {
        $Fg = $this->xml->createElement("\163\x61\x6d\x6c\72\103\x6f\x6e\146\x69\x72\x6d\141\x74\151\x6f\x6e\x4d\145\x74\150\x6f\144", "\165\162\x6e\72\157\141\x73\151\163\x3a\x6e\x61\155\145\163\72\164\x63\72\123\x41\115\114\72\61\x2e\60\x3a\x63\x6d\x3a\x62\145\x61\162\x65\x72");
        return $Fg;
    }
    function createSubject()
    {
        $Fg = $this->xml->createElement("\163\x61\155\x6c\x3a\123\165\x62\x6a\145\143\164");
        $Xa = $this->createNameId();
        $Fg->appendChild($Xa);
        $hn = $this->createSubjectConfirmation();
        $Fg->appendChild($hn);
        return $Fg;
    }
    function createNameId()
    {
        $gt = !empty($this->mo_idp_nameid_attr) && $this->mo_idp_nameid_attr != "\x65\155\141\x69\154\x41\x64\x64\162\145\163\x73" ? $this->mo_idp_nameid_attr : "\165\163\x65\x72\x5f\145\155\141\151\154";
        $l7 = MoIDPUtility::isBlank($this->current_user->{$gt}) ? get_user_meta($this->current_user->ID, $gt, true) : $this->current_user->{$gt};
        $l7 = apply_filters("\x67\145\x6e\145\162\x61\x74\x65\137\x77\x73\146\145\144\x5f\141\164\164\x72\151\x62\x75\x74\x65\x5f\x76\x61\154\165\x65", $l7, $this->current_user, "\x4e\x61\155\145\x49\x44");
        $Fg = $this->xml->createElement("\x73\141\155\154\72\116\141\155\x65\x49\x64\145\156\164\x69\146\x69\x65\x72", htmlspecialchars($l7));
        $Fg->setAttribute("\x46\157\162\155\141\164", "\x75\162\156\x3a\157\x61\x73\151\x73\72\x6e\x61\155\x65\163\x3a\x74\143\x3a\123\x41\x4d\x4c\x3a" . $this->mo_idp_nameid_format);
        return $Fg;
    }
    function createSamlConditions($et)
    {
        $Fg = $this->xml->createElement("\163\141\x6d\x6c\x3a\103\x6f\x6e\144\x69\x74\x69\157\x6e\x73");
        $Fg->setAttribute("\x4e\x6f\x74\102\x65\x66\x6f\162\x65", $et["\x4e\157\x74\102\145\x66\x6f\162\145"]);
        $Fg->setAttribute("\116\x6f\164\117\156\117\x72\x41\x66\x74\145\x72", $et["\116\x6f\164\x4f\156\117\162\101\x66\x74\x65\x72"]);
        $Xa = $this->createSamlAudience();
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function createSamlAudience()
    {
        $Fg = $this->xml->createElement("\x73\x61\x6d\154\72\x41\x75\x64\151\x65\156\143\x65\122\x65\163\164\x72\x69\x63\x74\151\157\156\x43\x6f\156\144\151\x74\151\x6f\156");
        $Xa = $this->buildAudience();
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function buildAudience()
    {
        $Fg = $this->xml->createElement("\163\141\155\x6c\x3a\x41\x75\144\x69\x65\156\x63\145", $this->wtrealm);
        return $Fg;
    }
    function createResponseElementLifetime($et)
    {
        $Fg = $this->xml->createElement("\164\x3a\114\x69\x66\x65\x74\151\x6d\145");
        $Xa = $this->createLifetime($et);
        $hn = $this->expireLifetime($et);
        $Fg->appendChild($Xa);
        $Fg->appendChild($hn);
        return $Fg;
    }
    function createResponseElementAppliesTo($et)
    {
        $Fg = $this->xml->createElementNS("\x68\x74\164\160\x3a\x2f\57\163\x63\150\x65\155\x61\x73\x2e\170\155\x6c\x73\157\141\x70\x2e\x6f\x72\147\x2f\x77\x73\x2f\62\60\60\x34\x2f\60\x39\57\x70\x6f\154\x69\143\171", "\x77\x73\x70\x3a\x41\x70\x70\154\151\x65\163\124\157");
        $Xa = $this->buildAppliesTO($et);
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function buildAppliesTO($et)
    {
        $Fg = $this->xml->createElementNS("\150\164\x74\160\72\57\57\167\x77\167\x2e\167\x33\56\157\162\147\57\x32\x30\x30\x35\57\x30\70\x2f\x61\144\x64\162\x65\x73\163\x69\156\x67", "\167\x73\141\x3a\x45\156\144\160\x6f\151\156\x74\122\145\x66\145\162\x65\x6e\143\x65");
        $Xa = $this->createAddress();
        $Fg->appendChild($Xa);
        return $Fg;
    }
    function createAddress()
    {
        $Fg = $this->xml->createElement("\167\163\x61\72\101\144\144\x72\145\163\163", $this->wtrealm);
        return $Fg;
    }
    function createLifetime($et)
    {
        $P_ = $et["\111\x73\x73\165\x65\x49\156\x73\164\x61\156\x74"];
        $Fg = $this->xml->createElementNS("\x68\x74\x74\160\x3a\57\x2f\144\157\143\x73\x2e\157\x61\x73\x69\x73\55\157\x70\x65\x6e\56\157\x72\x67\x2f\x77\163\x73\57\62\60\60\x34\57\x30\x31\x2f\x6f\141\163\151\163\x2d\62\60\60\x34\60\x31\55\x77\x73\x73\x2d\x77\163\163\x65\143\x75\x72\x69\x74\x79\55\165\164\151\154\151\x74\x79\x2d\61\56\x30\56\x78\163\x64", "\x77\x73\x75\x3a\x43\x72\145\x61\164\145\x64", $P_);
        return $Fg;
    }
    function expireLifetime($et)
    {
        $aZ = $et["\x4e\157\164\117\156\x4f\x72\x41\146\x74\x65\x72"];
        $Fg = $this->xml->createElementNS("\x68\164\x74\160\x3a\57\57\144\x6f\x63\163\x2e\157\141\x73\151\x73\55\157\x70\145\x6e\56\x6f\162\x67\x2f\167\x73\163\x2f\62\x30\x30\x34\57\x30\61\57\x6f\x61\x73\x69\163\55\x32\x30\x30\x34\60\61\x2d\x77\x73\x73\55\167\x73\163\145\x63\x75\x72\151\164\171\55\165\164\151\154\x69\x74\x79\55\61\x2e\x30\56\170\163\x64", "\x77\x73\x75\72\105\x78\x70\151\x72\x65\163", $aZ);
        return $Fg;
    }
}
