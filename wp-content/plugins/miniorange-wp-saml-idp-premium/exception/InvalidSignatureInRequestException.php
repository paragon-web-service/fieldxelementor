<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidSignatureInRequestException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\116\126\101\114\111\104\x5f\x52\105\x51\125\x45\x53\x54\137\123\x49\107\x4e\101\124\125\122\105");
        $Oo = 120;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\x5b{$this->code}\x5d\x3a\x20{$this->message}\12";
    }
}
