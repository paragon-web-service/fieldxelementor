<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class PasswordStrengthException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\116\126\x41\114\111\104\137\120\101\x53\x53\x5f\x53\x54\122\105\116\107\124\110");
        $Oo = 110;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\x20\x5b{$this->code}\x5d\72\40{$this->message}\12";
    }
}
