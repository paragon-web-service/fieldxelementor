<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidOperationException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\116\x56\x41\x4c\x49\104\137\117\x50");
        $Oo = 105;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\x5b{$this->code}\x5d\x3a\40{$this->message}\12";
    }
}
