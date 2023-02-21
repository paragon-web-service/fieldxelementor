<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidNumberOfNameIDsException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\116\x56\x41\114\111\x44\x5f\116\117\137\x4f\x46\137\x4e\x41\115\105\111\x44\x53");
        $Oo = 124;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\x5d\72\x20{$this->message}\12";
    }
}
