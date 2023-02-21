<?php


namespace IDP\Helper\JWT;

use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Exception\InvalidSSOUserException;
use IDP\Helper\Factory\ResponseHandlerFactory;
class GenerateJwtToken implements ResponseHandlerFactory
{
    private $algo;
    private $sharedSecret;
    private $sp;
    private $sp_attr;
    function __construct($B5, $vs, $di, $Ko, $ur)
    {
        $this->algo = $B5;
        $this->sharedSecret = $vs;
        $this->sp = $di;
        $this->sp_attr = $Ko;
        $this->current_user = is_null($ur) ? wp_get_current_user() : get_user_by("\154\157\x67\151\156", $ur);
    }
    function generateResponse()
    {
        if (!MoIDPUtility::isBlank($this->current_user)) {
            goto Sx;
        }
        throw new InvalidSSOUserException();
        Sx:
        $et = $this->getResponseParams();
        $Fg = $this->createResponseElement($et);
        $z2 = str_replace(["\53", "\x2f", "\75"], ["\55", "\137", ''], base64_encode(hash_hmac($this->algo, $Fg, $this->sharedSecret, 1)));
        return $Fg . "\56" . $z2;
    }
    function getResponseParams()
    {
        $et = array();
        $bL = time();
        $et["\x63\x75\x72\162\x65\x6e\164\x5f\x74\151\155\x65"] = str_replace("\x2b\60\x30\x3a\x30\60", "\x5a", gmdate("\143", $bL - 120));
        $et["\x69\x61\164"] = $bL;
        $et["\x65\170\x70"] = $bL + 300;
        $et["\152\164\151"] = $et["\151\141\164"] . $this->generateUniqueID(40);
        return $et;
    }
    function generateUniqueID($kd)
    {
        return MoIDPUtility::generateRandomAlphanumericValue($kd);
    }
    function createResponseElement($et)
    {
        $pg = str_replace(["\x2b", "\57", "\75"], ["\x2d", "\137", ''], base64_encode($this->createHeader($et)));
        $Ep = str_replace(["\53", "\57", "\x3d"], ["\x2d", "\137", ''], base64_encode($this->createPayload($et)));
        return $pg . "\56" . $Ep;
    }
    function createHeader()
    {
        $pg = ["\164\x79\160" => "\x4a\127\124", "\141\154\x67" => "\110\123\62\x35\x36"];
        if (!MSI_DEBUG) {
            goto oW;
        }
        MoIDPUtility::mo_debug("\110\145\x61\x64\145\162\40\107\145\x6e\x65\x72\141\164\145\144\40\x3a\x20" . print_r($pg, true));
        oW:
        return \json_encode($pg);
    }
    function createPayload($et)
    {
        $Ep = ["\x69\x61\x74" => $et["\x69\x61\164"], "\152\x74\x69" => $et["\x6a\x74\x69"], "\145\x78\160" => $et["\145\x78\x70"]];
        $YK = [];
        foreach ($this->sp_attr as $Hw) {
            $YK = array_merge($YK, $this->buildAttribute($et, $Hw->mo_sp_attr_name, $Hw->mo_sp_attr_value, $Hw->mo_attr_type));
            rp:
        }
        RM:
        $Ep = array_merge($Ep, $YK);
        if (!MSI_DEBUG) {
            goto pS;
        }
        MoIDPUtility::mo_debug("\120\x61\171\154\157\141\x64\40\107\145\x6e\x65\x72\x61\164\145\144\40\72\40" . print_r($Ep, true));
        pS:
        return \json_encode($Ep);
    }
    function buildAttribute($et, $wf, $wm, $ZV)
    {
        if ($wf === "\147\x72\157\x75\x70\x4d\141\160\x4e\141\x6d\x65") {
            goto uF;
        }
        if ($ZV == 0) {
            goto o8;
        }
        if (!($ZV == 2)) {
            goto Gg;
        }
        $l7 = $wm;
        Gg:
        goto IE;
        o8:
        $l7 = $this->current_user->{$wm};
        IE:
        goto oY;
        uF:
        $wf = $wm;
        $l7 = $this->current_user->roles;
        oY:
        if (!empty($l7)) {
            goto zY;
        }
        $l7 = get_user_meta($this->current_user->ID, $wm, TRUE);
        zY:
        $l7 = apply_filters("\147\145\156\145\162\x61\164\x65\137\x6a\x77\x74\x5f\x61\x74\x74\162\x69\142\x75\x74\145\x5f\x76\x61\x6c\x75\x65", $l7, $this->current_user, $wf, $wm);
        $ft = [$wf => apply_filters("\x6d\157\x64\x69\x66\x79\137\x73\141\x6d\154\x5f\141\x74\x74\x72\137\166\x61\x6c\165\x65", $l7)];
        return $ft;
    }
}
