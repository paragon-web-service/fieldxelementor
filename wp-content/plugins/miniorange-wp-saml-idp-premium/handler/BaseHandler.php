<?php


namespace IDP\Handler;

use IDP\Exception\JSErrorException;
use IDP\Exception\NotRegisteredException;
use IDP\Exception\RequiredFieldsException;
use IDP\Exception\SupportQueryRequiredFieldsException;
use IDP\Exception\LicenseExpiredException;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Utilities\MoIDPUtility;
class BaseHandler
{
    public $_nonce;
    public function isValidRequest()
    {
        if (!(!current_user_can("\155\141\156\x61\x67\145\137\157\x70\164\151\157\x6e\163") || !check_admin_referer($this->_nonce))) {
            goto ji;
        }
        wp_die(MoIDPMessages::showMessage("\111\x4e\126\x41\114\x49\x44\x5f\x4f\120"));
        ji:
        return TRUE;
    }
    public function checkIfJSErrorMessage($bn, $xO = "\x65\162\162\x6f\162\137\155\145\x73\163\141\147\145")
    {
        if (!(array_key_exists($xO, $bn) && $bn[$xO])) {
            goto XC;
        }
        throw new JSErrorException($bn[$xO]);
        XC:
    }
    public function checkIfRequiredFieldsEmpty($bn)
    {
        foreach ($bn as $xO => $l7) {
            if (!(is_array($l7) && (!array_key_exists($xO, $l7) || MoIDPUtility::isBlank($l7[$xO])) || MoIDPUtility::isBlank($l7))) {
                goto OH;
            }
            throw new RequiredFieldsException();
            OH:
            Kv:
        }
        qR:
    }
    public function checkIfSupportQueryFieldsEmpty($bn)
    {
        try {
            $this->checkIfRequiredFieldsEmpty($bn);
        } catch (RequiredFieldsException $S5) {
            throw new SupportQueryRequiredFieldsException();
        }
    }
    public function checkIfValidPlugin()
    {
        if (MoIDPUtility::iclv()) {
            goto NW;
        }
        throw new NotRegisteredException();
        NW:
    }
    public function checkIfValidLicense()
    {
        if (!MoIDPUtility::cled()) {
            goto vG;
        }
        throw new LicenseExpiredException();
        vG:
    }
    public function checkValidDomain()
    {
        if (!MoIDPUtility::cvd()) {
            goto bM;
        }
        do_action("\163\164\x61\162\164\144\x70\162\x6f\143\145\163\163");
        bM:
    }
}
