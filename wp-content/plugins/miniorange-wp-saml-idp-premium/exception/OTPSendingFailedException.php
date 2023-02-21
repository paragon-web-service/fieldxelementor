<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class OTPSendingFailedException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x45\122\122\x4f\x52\x5f\123\105\x4e\x44\111\116\x47\x5f\117\124\x50");
        $Oo = 115;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\x5b{$this->code}\135\72\40{$this->message}\12";
    }
}
