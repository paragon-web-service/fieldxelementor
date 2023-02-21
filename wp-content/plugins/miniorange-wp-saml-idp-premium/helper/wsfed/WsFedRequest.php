<?php


namespace IDP\Helper\WSFED;

use IDP\Exception\MissingWaAttributeException;
use IDP\Exception\MissingWtRealmAttributeException;
use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Factory\RequestHandlerFactory;
class WsFedRequest implements RequestHandlerFactory
{
    private $clientRequestId;
    private $username;
    private $wreply;
    private $wres;
    private $wctx;
    private $wp;
    private $wct;
    private $wfed;
    private $wencoding;
    private $wfresh;
    private $wauth;
    private $wreq;
    private $whr;
    private $wreqptr;
    private $wa;
    private $wtrealm;
    private $requestType = MoIDPConstants::WS_FED;
    public function __construct($oF)
    {
        $this->clientRequestId = array_key_exists("\x63\x6c\151\x65\156\x74\55\x72\145\161\x75\x65\x73\x74\55\151\144", $oF) ? $oF["\x63\x6c\151\145\x6e\x74\x2d\162\x65\161\x75\x65\163\164\x2d\151\144"] : NULL;
        $this->username = array_key_exists("\165\163\x65\162\156\x61\x6d\145", $oF) ? $oF["\165\163\x65\x72\x6e\141\155\145"] : NULL;
        $this->wa = array_key_exists("\167\141", $oF) ? $oF["\167\x61"] : NULL;
        $this->wtrealm = array_key_exists("\x77\x74\162\145\x61\154\155", $oF) ? $oF["\167\164\162\145\141\x6c\155"] : NULL;
        $this->wctx = array_key_exists("\167\x63\x74\170", $oF) ? $oF["\167\143\x74\x78"] : NULL;
        $this->wct = array_key_exists("\167\143\x74\x78", $oF) ? $oF["\x77\x63\164\x78"] : NULL;
        if (!MoIDPUtility::isBlank($this->wa)) {
            goto rS;
        }
        throw new MissingWaAttributeException();
        rS:
        if (!MoIDPUtility::isBlank($this->wtrealm)) {
            goto ge;
        }
        throw new MissingWtRealmAttributeException();
        ge:
    }
    public function generateRequest()
    {
        return;
    }
    public function __toString()
    {
        $Kn = "\x57\x53\55\106\105\104\x20\x52\x45\x51\x55\x45\123\124\x20\120\101\x52\101\x4d\x53\x20\133";
        $Kn .= "\40\167\x61\40\x3d\40" . $this->wa;
        $Kn .= "\x2c\40\x77\164\x72\x65\141\154\x6d\x20\75\x20\40" . $this->wtrealm;
        $Kn .= "\x2c\x20\143\x6c\x69\145\x6e\x74\x52\145\161\x75\145\163\164\111\x64\40\75\40" . $this->clientRequestId;
        $Kn .= "\54\40\x75\163\x65\x72\x6e\141\155\145\x20\x3d\x20" . $this->username;
        $Kn .= "\54\40\x77\162\x65\x70\154\171\40\x3d\40" . $this->wreply;
        $Kn .= "\x2c\x20\167\162\x65\163\x20\x3d\x20" . $this->wres;
        $Kn .= "\54\x20\167\143\x74\170\x20\x3d\40" . $this->wctx;
        $Kn .= "\x2c\x20\x77\160\x20\x3d\x20" . $this->wp;
        $Kn .= "\x2c\40\x77\143\164\40\75\40" . $this->wct;
        $Kn .= "\54\40\x77\146\145\x64\x20\x3d\x20" . $this->wfed;
        $Kn .= "\54\40\167\x65\x6e\x63\x6f\144\x69\x6e\147\x20\x3d\x20" . $this->wencoding;
        $Kn .= "\x2c\x20\x77\146\162\145\x73\x68\x20\75\x20" . $this->wfresh;
        $Kn .= "\54\40\167\141\165\164\150\40\x3d\x20" . $this->wauth;
        $Kn .= "\x2c\40\167\x72\x65\161\40\75\x20" . $this->wreq;
        $Kn .= "\54\40\x77\x68\x72\40\75\x20" . $this->whr;
        $Kn .= "\54\40\167\x72\x65\x71\x70\164\x72\40\75\x20" . $this->wreqptr;
        $Kn .= "\x5d";
        return $Kn;
    }
    public function getClientRequestId()
    {
        return $this->clientRequestId;
    }
    public function setClientRequestId($uM)
    {
        $this->clientRequestId = $uM;
        return $this;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function setUsername($Cy)
    {
        $this->username = $Cy;
        return $this;
    }
    public function getWreply()
    {
        return $this->wreply;
    }
    public function setWreply($IX)
    {
        $this->wreply = $IX;
        return $this;
    }
    public function getWres()
    {
        return $this->wres;
    }
    public function setWres($KM)
    {
        $this->wres = $KM;
        return $this;
    }
    public function getWctx()
    {
        return $this->wctx;
    }
    public function setWctx($E2)
    {
        $this->wctx = $E2;
        return $this;
    }
    public function getWp()
    {
        return $this->wp;
    }
    public function setWp($w9)
    {
        $this->wp = $w9;
        return $this;
    }
    public function getWct()
    {
        return $this->wct;
    }
    public function setWct($AO)
    {
        $this->wct = $AO;
        return $this;
    }
    public function getWfed()
    {
        return $this->wfed;
    }
    public function setWfed($Oe)
    {
        $this->wfed = $Oe;
        return $this;
    }
    public function getWencoding()
    {
        return $this->wencoding;
    }
    public function setWencoding($Si)
    {
        $this->wencoding = $Si;
        return $this;
    }
    public function getWfresh()
    {
        return $this->wfresh;
    }
    public function setWfresh($nh)
    {
        $this->wfresh = $nh;
        return $this;
    }
    public function getWauth()
    {
        return $this->wauth;
    }
    public function setWauth($Zx)
    {
        $this->wauth = $Zx;
        return $this;
    }
    public function getWreq()
    {
        return $this->wreq;
    }
    public function setWreq($ZA)
    {
        $this->wreq = $ZA;
        return $this;
    }
    public function getWhr()
    {
        return $this->whr;
    }
    public function setWhr($CZ)
    {
        $this->whr = $CZ;
        return $this;
    }
    public function getWreqptr()
    {
        return $this->wreqptr;
    }
    public function setWreqptr($pl)
    {
        $this->wreqptr = $pl;
        return $this;
    }
    public function getWa()
    {
        return $this->wa;
    }
    public function setWa($QS)
    {
        $this->wa = $QS;
        return $this;
    }
    public function getWtrealm()
    {
        return $this->wtrealm;
    }
    public function setWtrealm($Nv)
    {
        $this->wtrealm = $Nv;
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
