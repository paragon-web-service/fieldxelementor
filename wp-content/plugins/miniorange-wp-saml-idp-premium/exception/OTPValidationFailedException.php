<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class OTPValidationFailedException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\111\116\126\101\114\x49\x44\x5f\117\124\x50");
        $Oo = 114;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\133{$this->code}\135\x3a\40{$this->message}\12";
    }
}
