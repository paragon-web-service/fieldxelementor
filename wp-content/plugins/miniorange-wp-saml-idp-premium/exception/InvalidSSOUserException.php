<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidSSOUserException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\x4e\126\101\x4c\x49\104\x5f\125\x53\x45\122");
        $Oo = 121;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\x20\x5b{$this->code}\x5d\72\x20{$this->message}\xa";
    }
}
