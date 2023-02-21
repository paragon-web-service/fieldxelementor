<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class OTPRequiredException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x52\105\x51\125\111\122\x45\x44\137\117\124\x50");
        $Oo = 113;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\x5b{$this->code}\135\72\40{$this->message}\xa";
    }
}
