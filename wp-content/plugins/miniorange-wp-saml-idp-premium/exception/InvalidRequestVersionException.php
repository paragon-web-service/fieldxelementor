<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidRequestVersionException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\111\x4e\126\x41\x4c\111\x44\137\x53\x41\115\x4c\137\x56\105\x52\123\x49\117\116");
        $Oo = 118;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\133{$this->code}\x5d\72\x20{$this->message}\xa";
    }
}
