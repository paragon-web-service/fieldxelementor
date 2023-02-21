<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class LicenseExpiredException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x4c\111\103\x45\x4e\123\105\x5f\x45\x58\x50\111\122\105\104");
        $Oo = 132;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\x20\x5b{$this->code}\x5d\x3a\x20{$this->message}\xa";
    }
}
