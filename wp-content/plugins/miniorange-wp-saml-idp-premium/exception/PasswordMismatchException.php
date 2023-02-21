<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class PasswordMismatchException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\120\101\123\x53\137\x4d\x49\123\x4d\x41\124\x43\110");
        $Oo = 122;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\x5b{$this->code}\135\72\40{$this->message}\12";
    }
}
