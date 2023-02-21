<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidRequestInstantException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\116\x56\x41\x4c\x49\x44\x5f\x52\x45\x51\125\105\x53\x54\137\x49\116\x53\124\101\x4e\124");
        $Oo = 117;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\133{$this->code}\x5d\x3a\x20{$this->message}\xa";
    }
}
